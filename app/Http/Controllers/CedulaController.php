<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use App\Services\OCRService;

class CedulaController extends Controller
{
    public function validateCedula(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string',
            'identificacion' => 'required|string',
            'fecha_nacimiento' => 'required|date_format:Y-m-d',
            'fecha_expedicion' => 'required|date_format:Y-m-d',
            'lugar_expedicion' => '',
            'cedula_front' => 'required|image|mimes:jpg,png,jpeg|max:2048',
            'cedula_back' => 'required|image|mimes:jpg,png,jpeg|max:2048',
        ]);

        Log::info($request->all());
        set_time_limit(120); // 120 segundos

        if (!$request->file('cedula_front')->isValid() || !$request->file('cedula_back')->isValid()) {
            return response()->json([
                'message' => 'Error al cargar los archivos. Por favor, intente nuevamente.',
            ], 422);
        }

        try {
            // Obtener la extensión original de los archivos
            $frontExtension = $request->file('cedula_front')->getClientOriginalExtension();
            $backExtension = $request->file('cedula_back')->getClientOriginalExtension();

            // Guardar los archivos con su extensión original
            $frontPath = $request->file('cedula_front')->storeAs('temp', 'cedula_front.' . $frontExtension);
            $backPath = $request->file('cedula_back')->storeAs('temp', 'cedula_back.' . $backExtension);

            $ocrService = new OCRService();

            // Extraer texto de las imágenes
            $frontText = $ocrService->extractText(storage_path('app/' . $frontPath));
            $backText = $ocrService->extractText(storage_path('app/' . $backPath));

            // Limpiar texto extraído
            $frontTextClean = $this->cleanText($frontText);
            $backTextClean = $this->cleanText($backText);

            // Combinar textos extraídos
            $ocrTexts = [$frontTextClean, $backTextClean];

            Log::info('Texto extraído:', ['Text' => $ocrTexts]);

            // Validar identificación
            $identificacion = preg_replace('/[^0-9]/', '', $request->identificacion); // Limpia puntos o guiones
            if (!$this->existsInOcr($identificacion, $ocrTexts)) {
                return response()->json(['message' => 'La identificación no coincide.'], 422);
            }

            // Validar nombre
            $nombre = strtoupper($request->nombre);
            $nombreParts = explode(' ', $nombre); // Por si OCR lo divide mal
            $nombreCoincide = false;
            foreach ($nombreParts as $part) {
                if ($this->existsInOcr($part, $ocrTexts)) {
                    $nombreCoincide = true;
                    break;
                }
            }
            if (!$nombreCoincide) {
                return response()->json(['message' => 'El nombre no coincide.'], 422);
            }

            // Validar fecha de expedición (solo el año)
            $fechaExpedicionInput = date('Y', strtotime($request->fecha_expedicion)); // Extraer solo el año
            if (!$this->existsInOcr($fechaExpedicionInput, $ocrTexts)) {
                return response()->json(['message' => 'El año de expedición no coincide.'], 422);
            }

            // Validar lugar de expedición
            if ($request->lugar_expedicion != NULL) {
                Log::info('lugar exp', ['lug_exp' => $request->lugar_expedicion]);
                $lugar = strtoupper($request->lugar_expedicion);
                if (!$this->existsInOcr($lugar, $ocrTexts)) {
                    return response()->json(['message' => 'El lugar de expedición no coincide.'], 422);
                }
            }
        } catch (\Exception $e) {
            Log::error('Error en el servicio OCR', ['exception' => $e->getMessage()]);
            return response()->json([
                'message' => 'Error al procesar las imágenes: ' . $e->getMessage(),
            ], 422);
        }

        return response()->json([
            'message' => 'Validación exitosa.',
        ]);
    }

    // Limpieza de texto extraído de OCR
    private function cleanText($text)
    {
        return strtoupper(preg_replace('/\s+/', ' ', $text));
    }

    // Buscar campos si existen en el texto extraído
    private function existsInOcr($value, $texts)
    {
        foreach ($texts as $text) {
            if (strpos($text, $value) !== false) {
                return true;
            }
        }
        return false;
    }
}
