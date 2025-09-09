<?php
// app/Http/Controllers/Auth/FaceController.php

namespace App\Http\Controllers\Auth;

use App\Models\UserEmbedding;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\UsuariosBiometricos;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class FaceController extends Controller
{


    public function validateFace(Request $request)
    {
        $distancias = [];

        // 1. Comparar contra TODOS los embeddings existentes
        $usuarios = UsuariosBiometricos::select('id', 'embedding')
        ->whereDoesntHave('user.jurado') // filtra solo los biometricos cuyo usuario no tiene jurado
        ->get();
        $raw = $request->embedding;

        // Si viene como string tipo CSV (coma separada), conviértalo
        if (is_string($raw) && str_contains($raw, ',')) {
            $data = array_map('floatval', explode(',', $raw));
        } else {
            // Intente decodificar si viene como JSON
            $data = json_decode($raw, true);
        }

        $input = collect($data);

        // Validar que el input tenga 128 dimensiones
        if ($input->count() !== 128) {
            Log::warning('Dimensiones incompatibles entre embeddings.', [
                'input_count' => $input->count(),
                'usuario_id' => $request->usuario_id,
            ]);
            return response()->json(['error' => 'El embedding debe tener 128 dimensiones.'], 400);
        }

        foreach ($usuarios as $usuario) {
            $stored = collect(json_decode($usuario->embedding));



            // Validar que ambos embeddings tienen la misma longitud
            if ($input->count() !== $stored->count()) {
                Log::warning("Dimensiones incompatibles entre embeddings. ID usuario: {$usuario->id}");
                continue;
            }


            // Calcular la distancia Euclidiana
            $distance = sqrt($input->zip($stored)->reduce(function ($acc, $pair) {
                $pair = $pair->toArray(); // 👈 convertir a array directamente

                if (count($pair) === 2 && is_numeric($pair[0]) && is_numeric($pair[1])) {
                    $diff = $pair[0] - $pair[1];
                    // Log::info("Diff: {$pair[0]} - {$pair[1]} = {$diff}");
                    return $acc + pow($diff, 2);
                } else {
                    Log::warning('Par inválido en zip:', ['pair' => $pair]);
                }

                return $acc;
            }, 0));




            // Guardar la distancia calculada
            $distancias[] = [
                'id' => $usuario->id,
                'distance' => $distance
            ];

            // Si la distancia es menor que el umbral (0.5), es un rostro duplicado
            if ($distance < 0.5) {
                
                return response()->json([
                    'match' => true,
                    'duplicado' => true,
                    'error' => 'El rostro ya está registrado',
                    'id' => $usuario->id,
                    'distance' => $distance
                ]);
            }
        }

        // Si no se encontró duplicado, retornar que el rostro es válido
        return response()->json([
            'match' => false,
            'message' => 'El rostro es válido y no está duplicado',
            'distancias_analizadas' => count($distancias)
        ]);
    }

    //Validacion biometrica de jurado
    public function validateFaceJurado(Request $request)
    {
        $distancias = [];

        // 1. Comparar contra TODOS los embeddings existentes
        $usuarios = UsuariosBiometricos::select('id', 'embedding')
            ->whereHas('user.jurado') // filtra solo los biometricos cuyo usuario tiene jurado
            ->get();

        $raw = $request->embedding;

        // Si viene como string tipo CSV (coma separada), conviértalo
        if (is_string($raw) && str_contains($raw, ',')) {
            $data = array_map('floatval', explode(',', $raw));
        } else {
            // Intente decodificar si viene como JSON
            $data = json_decode($raw, true);
        }

        $input = collect($data);

        // Validar que el input tenga 128 dimensiones
        if ($input->count() !== 128) {
            Log::warning('Dimensiones incompatibles entre embeddings.', [
                'input_count' => $input->count(),
                'usuario_id' => $request->usuario_id,
            ]);
            return response()->json(['error' => 'El embedding debe tener 128 dimensiones.'], 400);
        }

        foreach ($usuarios as $usuario) {
            $stored = collect(json_decode($usuario->embedding));



            // Validar que ambos embeddings tienen la misma longitud
            if ($input->count() !== $stored->count()) {
                Log::warning("Dimensiones incompatibles entre embeddings. ID usuario: {$usuario->id}");
                continue;
            }


            // Calcular la distancia Euclidiana
            $distance = sqrt($input->zip($stored)->reduce(function ($acc, $pair) {
                $pair = $pair->toArray(); // 👈 convertir a array directamente

                if (count($pair) === 2 && is_numeric($pair[0]) && is_numeric($pair[1])) {
                    $diff = $pair[0] - $pair[1];
                    Log::info("Diff: {$pair[0]} - {$pair[1]} = {$diff}");
                    return $acc + pow($diff, 2);
                } else {
                    Log::warning('Par inválido en zip:', ['pair' => $pair]);
                }

                return $acc;
            }, 0));


            Log::Info($distance);

            // Guardar la distancia calculada
            $distancias[] = [
                'id' => $usuario->id,
                'distance' => $distance
            ];

            // Si la distancia es menor que el umbral (0.5), es un rostro duplicado
            if ($distance < 0.5) {
                return response()->json([
                    'match' => true,
                    'duplicado' => true,
                    'error' => 'El rostro ya está registrado',
                    'id' => $usuario->id,
                    'distance' => $distance
                ]);
            }
        }

        // Si no se encontró duplicado, retornar que el rostro es válido
        return response()->json([
            'match' => false,
            'message' => 'El rostro es válido y no está duplicado',
            'distancias_analizadas' => count($distancias)
        ]);
    }
}
