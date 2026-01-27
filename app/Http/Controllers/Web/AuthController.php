<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\AuthService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    protected $authService;

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
                'email' => 'required|email',
                'password' => 'required|string|min:6',
            ]);

            $loginResponse = $this->authService->login($credentials, $request);

            if ($loginResponse === false) {
                return back()->withErrors([
                    'email' => 'Les informations d\'identification fournies ne correspondent pas à nos enregistrements.',
                ])->onlyInput('email');
            }

            return $loginResponse;
        } catch (\Exception $e) {
            return back()->with('error', 'Une erreur est survenue lors de la tentative de connexion. Veuillez réessayer.' . $e->getMessage());
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
