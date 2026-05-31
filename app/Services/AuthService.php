<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\RateLimiter;

class AuthService
{
    public function login($credentials, $request)
    {
        // 1. Vérifier le rate limiting (max 5 tentatives / minute par IP)
        $this->checkRateLimit($request);

        // 2. Trouver l'utilisateur
        $user = User::where('email', $credentials['email'])->first();

        if (!$user) {
            $this->incrementLoginAttempts($request);
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

        // 5. Déterminer le guard selon le rôle
        $guard = match ($user->role) {
            'admin'  => 'admin',
            'driver' => 'driver',
            default  => 'client',
        };

        // 6. Tenter l'authentification
        if (Auth::guard($guard)->attempt($credentials)) {

            // Succès : réinitialiser les tentatives
            $this->resetLoginAttempts($request, $user);

            $request->session()->regenerate();

            // Enregistrer la connexion (IP, date, user agent)
            $this->logSuccessfulLogin($user, $request);

            return match ($guard) {
                'admin'  => redirect()->route('admin.dashboard')
                    ->with('success', 'Connexion réussie en tant qu\'administrateur.'),
                'driver' => redirect()->route('driver.dashboard')
                    ->with('success', 'Connexion réussie en tant que conducteur.'),
                default  => redirect()->route('client.dashboard')
                    ->with('success', 'Connexion réussie.'),
            };
        }

        // Échec : incrémenter les tentatives
        $this->incrementLoginAttempts($request, $user);

        return false;
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
            'role'       => $user->role,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'at'         => now()->toDateTimeString(),
        ]);

        // Optionnel : Mettre à jour last_login_at dans la base
        $user->updateQuietly(['last_login_at' => now(), 'last_login_ip' => $request->ip()]);
    }

    public function logout(Request $request)
    {
        // 1. Identifier le guard actif et l'utilisateur connecté
        $guard = $this->getActiveGuard();
        $user  = $guard ? Auth::guard($guard)->user() : null;

        // 2. Logger la déconnexion avant de perdre la session
        if ($user) {
            $this->logLogout($user, $request);
        }

        // 3. Déconnecter tous les guards actifs
        $this->logoutAllGuards();

        // 4. Détruire complètement la session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'Vous avez été déconnecté avec succès.');
    }

    /**
     * Retourne le guard actuellement actif.
     */
    private function getActiveGuard(): ?string
    {
        foreach (['admin', 'driver', 'client'] as $guard) {
            if (Auth::guard($guard)->check()) {
                return $guard;
            }
        }
        return null;
    }

    /**
     * Déconnecte tous les guards actifs.
     */
    private function logoutAllGuards(): void
    {
        foreach (['admin', 'driver', 'client'] as $guard) {
            if (Auth::guard($guard)->check()) {
                Auth::guard($guard)->logout();
            }
        }
    }

    /**
     * Enregistre la déconnexion dans les logs.
     */
    private function logLogout(User $user, Request $request): void
    {
        Log::info('Déconnexion', [
            'user_id'    => $user->id,
            'email'      => $user->email,
            'role'       => $user->role,
            'ip'         => $request->ip(),
            'user_agent' => $request->userAgent(),
            'at'         => now()->toDateTimeString(),
        ]);
    }
}
