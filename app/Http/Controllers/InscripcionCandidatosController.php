<?php

namespace App\Http\Controllers;

use App\Models\Eventos;
use App\Models\Funcionarios_planeacion;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\Postulaciones_candidatos;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Inertia\Inertia;

class InscripcionCandidatosController extends Controller
{
    /**
     * Muestra el formulario público de inscripción de candidatos.
     */
    public function formulario()
    {
        // Buscar si existe un evento relacionado con Comisión de Personal o el evento activo más relevante
        $evento = Eventos::where('nombre', 'like', '%Comisi%')
            ->orWhere('nombre', 'like', '%Personal%')
            ->where('estado', 'Activo')
            ->first();

        if (!$evento) {
            $evento = Eventos::where('estado', 'Activo')->orderBy('id', 'desc')->first();
        }

        $hoy = Carbon::now();

        return Inertia::render('InscripcionCandidatos/Formulario', [
            'evento' => $evento ? [
                'id' => $evento->id,
                'nombre' => $evento->nombre,
                'descripcion' => $evento->descripcion,
            ] : null,
            'fecha_actual' => [
                'completa' => $hoy->format('Y-m-d'),
                'dia' => $hoy->format('d'),
                'mes' => $hoy->format('m'),
                'anio' => $hoy->format('Y'),
                'texto' => $hoy->translatedFormat('d \d\e F \d\e Y'),
            ],
            'titulo_encabezado' => 'SECRETARÍA DE GESTIÓN ADMINISTRATIVA',
            'subtitulo_encabezado' => 'DIRECCIÓN ADMINISTRATIVA DE TALENTO HUMANO',
            'nombre_formulario' => 'FORMULARIO DE INSCRIPCIÓN – COMISIÓN DE PERSONAL 2025-2026',
        ]);
    }

    /**
     * Consulta el número de documento para auto-llenar los datos del funcionario.
     * Solo retorna datos si el usuario es funcionario (is_funcionario == 1 o true).
     */
    public function consultarFuncionario(Request $request)
    {
        $request->validate([
            'identificacion' => 'required|string|max:30',
        ]);

        $identificacion = trim($request->identificacion);

        // 1. Buscar en Informacion_votantes (tabla votantes)
        $votante = Informacion_votantes::where('identificacion', $identificacion)->first();

        // 2. Buscar en funcionarios_planeacion si existe
        $funcionarioPlaneacion = null;
        if (class_exists(Funcionarios_planeacion::class)) {
            $funcionarioPlaneacion = Funcionarios_planeacion::where('identificacion', $identificacion)->first();
        }

        $esFuncionario = false;
        $nombre = null;
        $tipoDoc = 'CC';
        $email = null;
        $celular = null;
        $tipoPlanta = 'PLANTA CENTRAL';

        if ($votante) {
            $nombre = $votante->nombre;
            $tipoDoc = $votante->tipo_documento ?: 'CC';
            $email = $votante->email;
            $celular = $votante->celular;

            // Verificar si es funcionario:
            // a) is_funcionario == 1 / true
            // b) campo relacion contiene funcionario / planta central / sgp / carrera
            // c) o existe en funcionarios_planeacion
            if (isset($votante->is_funcionario) && ($votante->is_funcionario == 1 || $votante->is_funcionario === true || $votante->is_funcionario === '1')) {
                $esFuncionario = true;
            } elseif (!empty($votante->relacion) && in_array(mb_strtolower(trim($votante->relacion)), ['funcionario', 'planta central', 'sgp', 'carrera administrativa'])) {
                $esFuncionario = true;
                if (stripos($votante->relacion, 'sgp') !== false) {
                    $tipoPlanta = 'SGP';
                }
            } elseif ($funcionarioPlaneacion) {
                $esFuncionario = true;
            }
        } elseif ($funcionarioPlaneacion) {
            $esFuncionario = true;
            $nombre = $funcionarioPlaneacion->nombre;
        }

        if (!$esFuncionario) {
            return response()->json([
                'success' => false,
                'encontrado' => false,
                'mensaje' => 'El número de identificación no corresponde a un funcionario habilitado para esta postulación (is_funcionario = 1) o no se encuentra registrado en el censo oficial. Por favor contacte a la Dirección de Talento Humano.',
            ], 422);
        }

        // Verificar si ya tiene una postulación registrada
        $postulacionExistente = Postulaciones_candidatos::where('identificacion', $identificacion)
            ->whereIn('estado', ['Pendiente', 'Aprobado'])
            ->first();

        if ($postulacionExistente) {
            return response()->json([
                'success' => true,
                'encontrado' => true,
                'ya_postulado' => true,
                'radicado' => $postulacionExistente->radicado,
                'fecha_postulacion' => $postulacionExistente->fecha_postulacion,
                'estado' => $postulacionExistente->estado,
                'tipo_planta' => $postulacionExistente->tipo_planta,
                'mensaje' => "El funcionario ya cuenta con una postulación registrada con radicado {$postulacionExistente->radicado} en estado '{$postulacionExistente->estado}'.",
            ]);
        }

        return response()->json([
            'success' => true,
            'encontrado' => true,
            'ya_postulado' => false,
            'votante_id' => $votante ? $votante->id : null,
            'nombre' => $nombre,
            'identificacion' => $identificacion,
            'tipo_documento' => $tipoDoc,
            'email' => $email,
            'celular' => $celular,
            'tipo_planta' => $tipoPlanta,
        ]);
    }

    /**
     * Guarda la postulación del candidato y procesa la carga de documentos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'identificacion' => 'required|string|max:30',
            'nombre' => 'required|string|max:255',
            'tipo_documento' => 'required|string|max:20',
            'tipo_planta' => 'required|string|in:PLANTA CENTRAL,SGP',
            'email' => 'nullable|email|max:191',
            'celular' => 'nullable|string|max:30',
            'fecha_postulacion' => 'required|date',
            'declaracion' => 'required|accepted',
            'archivo_procuraduria' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'archivo_cedula' => 'required|file|mimes:pdf,jpg,jpeg,png|max:5120',
            'foto' => 'nullable|file|mimes:jpg,jpeg,png|max:3072',
            'evento_id' => 'nullable|integer|exists:eventos,id',
        ], [
            'declaracion.accepted' => 'Debe aceptar la declaración juramentada de cumplimiento de normatividad.',
            'archivo_procuraduria.required' => 'Debe adjuntar el certificado vigente no mayor a tres meses de la Procuraduría.',
            'archivo_procuraduria.mimes' => 'El certificado de Procuraduría debe estar en formato PDF, JPG o PNG.',
            'archivo_cedula.required' => 'Debe adjuntar la fotocopia legible de la cédula de ciudadanía.',
            'archivo_cedula.mimes' => 'La fotocopia de la cédula debe estar en formato PDF, JPG o PNG.',
        ]);

        $identificacion = trim($request->identificacion);

        // Verificar duplicados
        $existe = Postulaciones_candidatos::where('identificacion', $identificacion)
            ->whereIn('estado', ['Pendiente', 'Aprobado'])
            ->first();

        if ($existe) {
            return redirect()->back()->with('error', "Ya existe una inscripción registrada para este documento con radicado {$existe->radicado}.");
        }

        DB::beginTransaction();
        try {
            $timestamp = time();
            $safeId = preg_replace('/[^0-9]/', '', $identificacion);

            // 1. Guardar archivo de Procuraduría
            $procExt = $request->file('archivo_procuraduria')->getClientOriginalExtension();
            $procFileName = "procuraduria_{$safeId}_{$timestamp}.{$procExt}";
            $request->file('archivo_procuraduria')->storeAs('uploads/documentos_candidatos', $procFileName, 'public');

            // 2. Guardar archivo de Cédula legible
            $ccExt = $request->file('archivo_cedula')->getClientOriginalExtension();
            $ccFileName = "cedula_{$safeId}_{$timestamp}.{$ccExt}";
            $request->file('archivo_cedula')->storeAs('uploads/documentos_candidatos', $ccFileName, 'public');

            // 3. Guardar foto (opcional)
            $fotoFileName = null;
            if ($request->hasFile('foto')) {
                $fotoExt = $request->file('foto')->getClientOriginalExtension();
                $fotoFileName = "foto_{$safeId}_{$timestamp}.{$fotoExt}";
                $request->file('foto')->storeAs('uploads/candidatos', $fotoFileName, 'public');
            }

            // Generar número de radicado único
            $radicado = 'CP-' . date('Y') . '-' . strtoupper(Str::random(6));

            $votante = Informacion_votantes::where('identificacion', $identificacion)->first();

            $eventoId = $request->evento_id;
            if (!$eventoId) {
                $eventoDefault = Eventos::where('nombre', 'like', '%Comisi%')
                    ->orWhere('nombre', 'like', '%Personal%')
                    ->where('estado', 'Activo')
                    ->first();
                $eventoId = $eventoDefault ? $eventoDefault->id : null;
            }

            // Crear registro de postulación
            $postulacion = Postulaciones_candidatos::create([
                'id_votante' => $votante ? $votante->id : null,
                'id_evento' => $eventoId,
                'identificacion' => $identificacion,
                'tipo_documento' => $request->tipo_documento ?: 'CC',
                'nombre' => $request->nombre,
                'email' => $request->email ?: ($votante ? $votante->email : null),
                'celular' => $request->celular ?: ($votante ? $votante->celular : null),
                'tipo_planta' => $request->tipo_planta,
                'fecha_postulacion' => $request->fecha_postulacion,
                'cumple_requisitos' => true,
                'archivo_procuraduria' => $procFileName,
                'archivo_cedula' => $ccFileName,
                'foto' => $fotoFileName,
                'estado' => 'Pendiente',
                'radicado' => $radicado,
            ]);

            // Si se suministró foto y existe el votante, actualizar la foto de candidato
            if ($fotoFileName && $votante && ($votante->imagen == 'user.png' || empty($votante->imagen))) {
                $votante->imagen = $fotoFileName;
                $votante->save();
            }

            DB::commit();

            return redirect()->back()->with('success_postulacion', [
                'radicado' => $radicado,
                'nombre' => $request->nombre,
                'identificacion' => $identificacion,
                'tipo_planta' => $request->tipo_planta,
                'fecha' => $request->fecha_postulacion,
                'mensaje' => 'Su postulación a la Comisión de Personal ha sido radicada exitosamente y entrará en etapa de validación de requisitos por parte de la Dirección de Talento Humano.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error registrando postulación de candidato: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Ocurrió un error al procesar la postulación: ' . $e->getMessage());
        }
    }
}

