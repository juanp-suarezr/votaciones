<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Informacion_votantes;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Request as RequestFacade;

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
        'email'   => 'required|string', // 👈 ojo, no siempre será email válido si es PPT
        'isPPT'   => 'required|in:0,1',
    ]);

    $inputEmail = $request->input('email');
    $isPPT = $request->input('isPPT');

    if ($isPPT === '1') {
        // Caso: usuario PPT → el correo está en votantes
        $votante = Informacion_votantes::where('email', $inputEmail)->first();

        if (!$votante) {
            throw ValidationException::withMessages([
                'email' => ['No encontramos un votante con este correo.'],
            ]);
        }

        $user = $votante->user;
        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['El votante no tiene usuario asociado.'],
            ]);
        }

        $finalEmail = $user->email; // 👈 email válido del usuario
    } else {
        // Caso: usuario normal → buscar en users
        $user = \App\Models\User::where('email', $inputEmail)->first();

        if (!$user) {
            throw ValidationException::withMessages([
                'email' => ['No encontramos un usuario con este correo.'],
            ]);
        }

        $finalEmail = $user->email;
    }

    // Ahora sí, enviamos el link
    $status = Password::sendResetLink([
        'email' => $finalEmail,
    ]);

    if ($status == Password::RESET_LINK_SENT) {
        return back()->with('status', __($status));
    }

    throw ValidationException::withMessages([
        'email' => [trans($status)],
    ]);
}

}
