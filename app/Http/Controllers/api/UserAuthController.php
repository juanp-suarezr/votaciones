<?php

namespace App\Http\Controllers\api;

use App\Events\VotoCreado;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Votos;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserAuthController extends Controller
{
    public function login(Request $request)
    {
        $loginUserData = $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|min:6'
        ]);

        Log::info('Intento de login', [
            'email' => $this->input('email'),
            'password' => $this->input('password'),
        ]);

        $user = User::where('email', $loginUserData['email'])->first();
        if (!$user || !Hash::check($loginUserData['password'], $user->password)) {
            return response()->json([
                'mensaje' => 'Usuario o contraseña incorrecta'
            ], 401);
        }
        if ($user->estado == 'Bloqueado') {
            return response()->json([
                'mensaje' => 'Usuario Bloqueado'
            ], 203);
        }
        $abilities = $user->getAllPermissions()->pluck('name')->toArray();
        // Remover el atributo de permisos y roles del objeto user
        $user->unsetRelation('permissions');
        $user->unsetRelation('roles');
        $data['user'] = $user;
        $data['token'] = $user->createToken($user->name . '-AuthToken', $abilities)->plainTextToken;

        $response = [
            'estado' => 'success',
            'mensaje' => 'Inicio de sesión exitoso.',
            'data' => $data,
        ];

        return response()->json($response, 200);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();

        return response()->json([
            'estado' => 'success',
            'mensaje' => 'logged out'
        ], 200);
    }

    // ...existing code...
    public function votar(Request $request)
    {
        // Verificar autenticación API
        $user = Auth::user();
        if (!$user) {
            return response()->json([
                'estado' => 'error',
                'mensaje' => 'No autenticado'
            ], 401);
        }


        try {
            DB::beginTransaction();

            $voto = Votos::create([
                    'id_votante'    => $request->id_votante,
                    'id_candidato'  => 0,
                    'id_proyecto'   => $request->proyecto_id,
                    'id_eventos'    => $request->evento_id,
                    'tipo'          => 'Votante',
                    'subtipo'       => 31,
                    'isVirtual'     => 1,
                ]);

            // Disparar evento para procesos posteriores
            event(new VotoCreado($voto));

            DB::commit();

            // Respuesta API exitosa
            return response()->json([
                'estado'  => 'success',
                'mensaje' => 'Voto registrado correctamente',
                'data'    => $voto
            ], 201);

        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error guardando voto: ' . $e->getMessage(), [
                'payload' => $request->all()
            ]);

            return response()->json([
                'estado'  => 'error',
                'mensaje' => 'Error al procesar el voto',
                'detalle' => config('app.debug') ? $e->getMessage() : null
            ], 500);
        }
    }
    // ...existing code...
}
