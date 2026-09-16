<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Sanctum\PersonalAccessToken;

class AuthService
{
    public function login($credentials, $request)
    {
        // 1. Rate limiting global par IP
        $this->checkRateLimit($request);

        // 2. Trouver l'utilisateur
        $user = User::where('email', $credentials['email'])
            ->where('profil', $credentials['profil'])
            ->first();

        // 3. Vérifier le verrou AVANT tout
        if ($user && $this->isAccountLocked($user)) {
            $remaining = $this->getLockRemainingTime($user);
            $minutes   = ceil($remaining / 60);
            $seconds   = $remaining % 60;

            $timeMsg = $minutes > 0
                ? "{$minutes} minute(s) et {$seconds} seconde(s)"
                : "{$seconds} seconde(s)";

            throw new \Exception(
                "Compte temporairement verrouillé. Réessayez dans {$timeMsg}."
            );
        }

        // 4. Credentials incorrects
        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            $this->incrementLoginAttempts($request, $user ?? null);

            if ($user) {
                $user->refresh();

                if ($this->isAccountLocked($user)) {
                    throw new \Exception(
                        "Trop de tentatives échouées. Compte verrouillé pour 5 minutes."
                    );
                }

                $remaining = max(0, 5 - $user->failed_login_attempts);
                throw new \Exception(
                    "Identifiants incorrects. Il vous reste {$remaining} tentative(s) avant le verrouillage."
                );
            }

            return false;
        }

        // 5. Vérifier si le compte est actif
        if (!$user->is_active) {
            throw new \Exception('Votre compte a été désactivé. Contactez l\'administrateur.');
        }

        // 6. Révoquer l'ancien token
        $user->tokens()->where('name', $user->profil)->delete();

        // 7. Créer le token Sanctum
        $token = $user->createToken($user->profil, [$user->profil])->plainTextToken;

        // 8. Connecter en session
        Auth::login($user);

        // 9. Réinitialiser les tentatives et logger
        $this->resetLoginAttempts($request, $user);
        $this->logSuccessfulLogin($user, $request);

        // 10. Cookie httpOnly
        $cookie = Cookie::make(
            name: 'ctt_' . $user->profil . '_token',
            value: $token,
            minutes: 60 * 24 * 30,
            path: '/',
            secure: config('app.env') === 'production',
            httpOnly: true,
            sameSite: 'Lax',
        );

        // 11. Redirection
        return match ($user->profil) {
            'admin'  => redirect()->route('admin.dashboard')
                ->with('success', 'Connexion réussie en tant qu\'administrateur.')
                ->cookie($cookie),
            'driver' => redirect()->route('driver.dashboard')
                ->with('success', 'Connexion réussie en tant que conducteur.')
                ->cookie($cookie),
            'owner'  => redirect()->route('owner.dashboard')
                ->with('success', 'Connexion réussie.')
                ->cookie($cookie),
            default  => redirect()->route('client.dashboard')
                ->with('success', 'Connexion réussie.')
                ->cookie($cookie),
        };
    }

    private function checkRateLimit(Request $request): void
    {
        $key = 'login_attempts_ip_' . $request->ip();

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);
            throw new \Exception(
                "Trop de tentatives depuis votre IP. Réessayez dans {$seconds} secondes."
            );
        }

        RateLimiter::hit($key, 60);
    }

    private function isAccountLocked(User $user): bool
    {
        return $user->locked_until && $user->locked_until->isFuture();
    }

    private function getLockRemainingTime(User $user): int
    {
        if (!$user->locked_until) return 0;
        return max(0, (int) now()->diffInSeconds($user->locked_until, false));
    }

    private function incrementLoginAttempts(Request $request, ?User $user = null): void
    {
        // Par IP
        RateLimiter::hit('login_attempts_ip_' . $request->ip(), 60);

        if (!$user) return;

        $user->increment('failed_login_attempts');
        $user->update(['last_failed_login' => now()]);

        // Verrouillage après 5 échecs
        if ($user->fresh()->failed_login_attempts >= 5) {
            $user->update(['locked_until' => now()->addMinutes(5)]);

            Log::warning('Compte verrouillé', [
                'user_id' => $user->id,
                'email'   => $user->email,
                'ip'      => $request->ip(),
            ]);
        }
    }

    private function resetLoginAttempts(Request $request, User $user): void
    {
        RateLimiter::clear('login_attempts_ip_' . $request->ip());

        $user->update([
            'failed_login_attempts' => 0,
            'locked_until'          => null,
            'last_failed_login'     => null,
        ]);
    }

    private function logSuccessfulLogin(User $user, Request $request): void
    {
        Log::info('Connexion réussie', [
            'user_id'    => $user->id,
            'email'      => $user->email,
            'profil'     => $user->profil,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'at'         => now()->toDateTimeString(),
        ]);

        // Optionnel : Mettre à jour last_login_at dans la base
        $user->updateQuietly(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);
    }

    public function logout(Request $request)
    {
        // Cherche le rôle actif parmi les cookies présents
        foreach (['admin', 'driver', 'client', 'owner'] as $profil) {
            $cookieName = 'ctt_' . $profil . '_token';
            $rawToken   = $request->cookie($cookieName);

            if (!$rawToken) continue;

            $pat = PersonalAccessToken::findToken($rawToken);

            if ($pat) {
                $this->logLogout($pat->tokenable, $request);
                $pat->delete();
            }

            Cookie::queue(Cookie::forget($cookieName));

            // Déconnecter aussi la session web
            Auth::guard('web')->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            if ($profil === 'owner') {
                return redirect('/owner/login')->with('success', 'Déconnecté avec succès.');
            } else {
                return redirect('/login')->with('success', 'Déconnecté avec succès.');
            }
        }

        // Sécurité : déconnecter la session même si aucun cookie trouvé
        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function logoutAll(Request $request): \Illuminate\Http\RedirectResponse
    {
        foreach (['admin', 'driver', 'client'] as $profil) {
            $rawToken = $request->cookie('ctt_' . $profil . '_token');

            if (!$rawToken) continue;

            $pat = PersonalAccessToken::findToken($rawToken);
            $pat?->delete();

            Cookie::queue(Cookie::forget('ctt_' . $profil . '_token'));
        }

        return redirect('/login')->with('success', 'Tous les profils ont été déconnectés.');
    }

    /**
     * Enregistre la déconnexion dans les logs.
     */
    private function logLogout(User $user, Request $request): void
    {
        Log::info('Déconnexion', [
            'user_id'    => $user->id,
            'email'      => $user->email,
            'profil'     => $user->profil,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'at'         => now()->toDateTimeString(),
        ]);
    }
}
