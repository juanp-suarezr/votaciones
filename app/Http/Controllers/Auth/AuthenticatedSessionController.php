<?php

namespace App\Http\Controllers\Auth;
use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\Registro;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {

        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),

        ]);
    }

    public function loginPresupuesto(): Response
    {

        return Inertia::render('Auth/LoginPresupuesto', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),

        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request)
    {

        

        $request->authenticate();

        $request->session()->regenerate();



            return redirect()->intended(RouteServiceProvider::HOME);

    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/welcome');
    }

    /**
     * Handle an emergency login request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function emergencyLogin(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'code' => 'required|string',
    ]);

    // Verificar código
    if ($request->code !== config('security.emergency_login_code')) {
        return response()->json(['message' => 'Código incorrecto.'], 401);
    }

    $user = User::where('email', $request->email)->first();

    if (!$user) {
        return response()->json(['message' => 'Usuario no encontrado.'], 404);
    }

    Auth::login($user); // Autenticación directa sin clave

    return response()->json(['message' => 'Ingreso exitoso por clave maestra.']);
}
}
