<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function showLoginForm()
    {
        return view('pages.auth.login');
    }

    public function loginStore(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email'    => ['required', 'email'],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[A-Z]/',      // Au moins 1 majuscule
                    'regex:/[0-9]/',      // Au moins 1 chiffre
                    'regex:/[@$!%*#?&]/', // Au moins 1 caractère spécial
                ],
            ], [
                'email.required'    => 'L\'adresse email est obligatoire.',
                'email.email'       => 'L\'adresse email n\'est pas valide.',
                'password.required' => 'Le mot de passe est obligatoire.',
                'password.min'      => 'Le mot de passe doit contenir au moins 8 caractères.',
                'password.regex'    => 'Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial (@$!%*#?&).',
            ]);

            $loginResponse = $this->authService->login($credentials, $request);

            if ($loginResponse === false) {
                // Délai artificiel pour contrer le brute force
                sleep(1);

                return back()->withErrors([
                    'email' => 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.',
                ])->onlyInput('email');
            }

            return $loginResponse;
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput($request->only('email'));
        } catch (\Exception $e) {
            Log::error('Erreur de connexion : ' . $e->getMessage());
            return back()->with('error', 'Une erreur est survenue lors de la connexion. Veuillez réessayer.');
        }
    }

    public function logout(Request $request)
    {
        try {
            $logoutResponse = $this->authService->logout($request);
            return $logoutResponse;
        } catch (\Exception $e) {
            Log::error('Erreur lors de la déconnexion : ' . $e->getMessage());
            return redirect('/login')->with('error', 'Une erreur est survenue lors de la déconnexion.');
        }
    }
}
