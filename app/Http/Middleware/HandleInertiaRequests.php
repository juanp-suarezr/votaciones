<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     *
     * @param \Illuminate\Http\Request $request
     * @return string|null
     */
    public function version(Request $request)
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @param \Illuminate\Http\Request $request
     * @return array
     */
    public function share(Request $request)
    {
        return array_merge(parent::share($request), [
            'auth' => [
                'user' => $request->user(),
            ],
            'flash' => function () use ($request) {
                return [
                    'success' => $request->session()->get('success'),
                ];
            },
            'user.roles' => $request->user() ? $request->user()->roles->pluck('name') : [],
            'user.info' => $request->user() ? $request->user()->votantes : [],
            'user.permissions' => $request->user() ? $request->user()->getPermissionsViaRoles()->pluck('name') : [],

            'user.jurado.evento' => $request->user() && $request->user()->jurado
                ? $request->user()->jurado->evento
                : null,
            'showingMobileMenu' => false,
            // Agregar la clave del sitio de reCAPTCHA
            'recaptcha' => [
                'site_key' => config('app.recaptcha_site_key'), // Obtiene la clave del archivo de configuración
            ],
        ]);
    }
}
