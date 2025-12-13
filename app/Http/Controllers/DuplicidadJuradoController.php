<?php

namespace App\Http\Controllers;

use App\Models\Informacion_votantes;
use App\Models\ParametrosDetalle;
use App\Models\Votos;
use Illuminate\Http\Request;
use Inertia\Inertia;

class DuplicidadJuradoController extends Controller
{
    public function index($id)
    {
        $comuna = ParametrosDetalle::where('id', $id)
            ->where('codParametro', 'com01')
            ->where('estado', 1)
            ->firstOrFail();

        return Inertia::render('Duplicidad/Index', [
            'comuna' => $comuna,
        ]);
    }

    public function validar(Request $request, $id)
    {
        $request->validate([
            'numero_identificacion' => 'required|string',
        ]);

        $numero_identificacion = $request->numero_identificacion;

        // Buscar votante en la comuna
        $votante = Informacion_votantes::where('identificacion', $numero_identificacion)
            ->where('comuna', $id)
            ->first();

        if (!$votante) {
            return response()->json([
                'valid' => false,
                'message' => 'Número de identificación no encontrado en esta comuna.',
            ]);
        }

        // Verificar si ya votó (evitar duplicidad)
        $yaVoto = Votos::where('id_votante', $votante->id)->exists();

        if ($yaVoto) {
            return response()->json([
                'valid' => false,
                'message' => 'Ya has votado anteriormente.',
            ]);
        }

        return response()->json([
            'valid' => true,
            'message' => 'Validación exitosa. Puedes proceder a votar.',
            'votante_id' => $votante->id,
        ]);
    }
}