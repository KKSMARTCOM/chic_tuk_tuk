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

    public function showOwnerLoginForm()
    {
        return view('pages.auth.owner-login');
    }

    public function loginStore(Request $request)
    {
        try {
            $credentials = $request->validate([
                'profil'    => ['required', 'string', 'in:admin,client,driver,owner'],
                'email'    => ['required', 'email'],
                'password' => [
                    'required',
                    'string',
                    'min:8',
                    'regex:/[A-Z]/',
                    'regex:/[0-9]/',
                    'regex:/[@$!%*#?&]/',
                ],
            ], [
                'profil.required' => 'Le profil est obligatoire.',
                'profil.in'       => 'Le profil sélectionné est invalide.',
                'email.required'    => 'L\'adresse email est obligatoire.',
                'email.email'       => 'L\'adresse email n\'est pas valide.',
                'password.required' => 'Le mot de passe est obligatoire.',
                'password.min'      => 'Le mot de passe doit contenir au moins 8 caractères.',
                'password.regex'    => 'Le mot de passe doit contenir au moins une majuscule, un chiffre et un caractère spécial (@$!%*#?&).',
            ]);

            $response = $this->authService->login($credentials, $request);

            if ($response === false) {
                sleep(1); // Anti brute-force
                return back()->withErrors(['email' => 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.',])->onlyInput('email');
            }

            return $response;
        } catch (ValidationException $e) {
            return back()->withErrors(['email' => $e->getMessage()])->withInput($request->only('email'));
        } catch (\Exception $e) {
            Log::error('Erreur de connexion : ' . $e->getMessage());
            return back()->withErrors(['email' => $e->getMessage()])->withInput($request->only('email'));
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
