<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\FaceController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use App\Http\Controllers\CedulaController;
use App\Http\Controllers\ValidationController;
use Illuminate\Support\Facades\Route;

Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
    ->name('password.confirm');

Route::middleware('guest')->group(function () {

    // Aplicar 'verificar.estado' solo a la ruta register
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->middleware('verificar.estado')
        ->name('register');

    //Validator cedula
    Route::post('/validate-doc', [CedulaController::class, 'validateCedula'])->name('validate-doc');
    //validar face ia
    Route::post('/face-validate', [FaceController::class, 'validateFace'])->name('face-validate');
    //validar identificacion
    Route::post('/validar-identificacion', [ValidationController::class, 'verificar']);

    //enviar cod de verificacion
    Route::post('/validationUser', [ValidationController::class, 'register'])->name('validate-user');
    //recibir y crear usuario
    Route::post('/CreateUser', [ValidationController::class, 'verify'])->name('create-user');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::get('login-pp', [AuthenticatedSessionController::class, 'loginPresupuesto'])
        ->name('login-pp');

    Route::post('/emergency-login', [AuthenticatedSessionController::class, 'emergencyLogin'])
        ->middleware('throttle:5,1'); // Máximo 5 intentos por minuto

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');



    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');
    Route::put('password/{id_user}', [PasswordController::class, 'updatePassUser'])->name('password.updateuser');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
