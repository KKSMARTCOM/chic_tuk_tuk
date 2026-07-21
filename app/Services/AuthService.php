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

        // 1. Vérifier le rate limiting (max 5 tentatives / minute par IP)
        $this->checkRateLimit($request);

        // 2. Trouver l'utilisateur
        $user = User::where('email', $credentials['email'])->where('profil', $credentials['profil'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            $this->incrementLoginAttempts($request, $user ?? null);
            return false;
        }

        // 3. Vérifier si le compte est actif
        if (!$user->is_active) {
            throw new \Exception('Votre compte a été désactivé. Contactez l\'administrateur.');
        }

        // 4. Vérifier si le compte est verrouillé (après trop de tentatives)
        if ($this->isAccountLocked($user)) {
            $remainingSeconds = $this->getLockRemainingTime($user);
            throw new \Exception("Compte temporairement verrouillé. Réessayez dans {$remainingSeconds} secondes.");
        }

        // 5. Révoquer l'ancien token du même profil s'il existe
        $user->tokens()->where('name', $user->profil)->delete();

        // 6. Créer un nouveau token Sanctum pour ce profil
        $token = $user->createToken($user->profil, [$user->profil])->plainTextToken;

        // 6b. Connecter en session
        Auth::login($user);

        // 7. Réinitialiser les tentatives et logger
        $this->resetLoginAttempts($request, $user);
        $this->logSuccessfulLogin($user, $request);

        // 8. Créer le cookie httpOnly pour ce profil
        $cookie = Cookie::make(
            name: 'ctt_' . $user->profil . '_token',
            value: $token,
            minutes: 60 * 24 * 30, // 30 jours
            path: '/',
            secure: config('app.env') === 'production',
            httpOnly: true,
            sameSite: 'Lax',
        );

        // 9. Rediriger selon le profil
        return match ($user->profil) {
            'admin'  => redirect()->route('admin.dashboard')
                ->with('success', 'Connexion réussie en tant qu\'administrateur.')
                ->cookie($cookie),
            'driver' => redirect()->route('driver.dashboard')
                ->with('success', 'Connexion réussie en tant que conducteur.')
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
            throw new \Exception("Trop de tentatives depuis votre IP. Réessayez dans {$seconds} secondes.");
        }

        RateLimiter::hit($key, 60); // Fenêtre de 60 secondes
    }

    private function isAccountLocked(User $user): bool
    {
        return Cache::has("user_locked_{$user->id}");
    }

    private function getLockRemainingTime(User $user): int
    {
        return (int) Cache::get("user_lock_ttl_{$user->id}", 0);
    }

    private function incrementLoginAttempts(Request $request, ?User $user = null): void
    {
        // Par IP
        $ipKey = 'login_attempts_ip_' . $request->ip();
        RateLimiter::hit($ipKey, 60);

        if ($user) {
            // Par utilisateur
            $userKey = "login_attempts_user_{$user->id}";
            $attempts = (int) Cache::increment($userKey);
            Cache::put($userKey, $attempts, now()->addMinutes(15));

            // Verrouillage après 5 échecs consécutifs
            if ($attempts >= 5) {
                $lockDuration = 15 * 60; // 15 minutes en secondes
                Cache::put("user_locked_{$user->id}", true, now()->addMinutes(15));
                Cache::put("user_lock_ttl_{$user->id}", $lockDuration, now()->addMinutes(15));

                Log::warning("Compte verrouillé", [
                    'user_id' => $user->id,
                    'email'   => $user->email,
                    'ip'      => $request->ip(),
                ]);
            }
        }
    }

    private function resetLoginAttempts(Request $request, User $user): void
    {
        RateLimiter::clear('login_attempts_ip_' . $request->ip());
        Cache::forget("login_attempts_user_{$user->id}");
        Cache::forget("user_locked_{$user->id}");
        Cache::forget("user_lock_ttl_{$user->id}");
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
        foreach (['admin', 'driver', 'client'] as $profil) {
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

            return redirect('/login')->with('success', 'Déconnecté avec succès.');
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
