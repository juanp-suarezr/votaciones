<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Informacion_votantes;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {

        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
            'isPPT' => RequestFacade::input('isPPT')
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        if ($request->isPPT == '1') {

            // 1. Buscar el correo en votantes
            $votante = Informacion_votantes::select('email')->where('email', $request->email)->first();

            if (!$votante) {
                return back()->withErrors(['email' => 'El correo no está registrado.']);
            }

            // 2. Generar token
            $token = Str::random(64);

            // 3. Guardar en password_resets
            DB::table('password_resets_tokens')->updateOrInsert(
                ['email' => $request->email],
                [
                    'email' => $request->email,
                    'token' => Hash::make($token),
                    'created_at' => Carbon::now(),
                ]
            );

            // 4. Disparar la notificación nativa (forzando el email correcto)
            $user = new \App\Models\User([
                'email' => $request->email, // falso user temporal solo para notificar
            ]);

            Notification::route('mail', $request->email)
                ->notify(new ResetPassword($token));

            return back()->with('status', __('Hemos enviado un enlace para restablecer su contraseña.'));
        }

        // --- flujo normal (Laravel se encarga solo) ---
        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status == Password::RESET_LINK_SENT) {
            return back()->with('status', __($status));
        }

        throw ValidationException::withMessages([
            'email' => [trans($status)],
        ]);
    }
}
