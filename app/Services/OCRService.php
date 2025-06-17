<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OCRService
{
    protected $apiKey = 'K88262078288957'; // Clave pública gratuita (puede registrar una propia)

    public function extractText($filePath)
    {
        if (!file_exists($filePath)) {
            throw new \Exception('El archivo no existe: ' . $filePath);
        }

        $response = Http::timeout(60) // Aumenta el tiempo de espera a 60 segundos
            ->asMultipart()
            ->post('https://api.ocr.space/parse/image', [
                ['name' => 'apikey', 'contents' => $this->apiKey],
                ['name' => 'language', 'contents' => 'spa'],
                ['name' => 'file', 'contents' => fopen($filePath, 'r')],
            ]);

        $data = $response->json();

        if (!isset($data['ParsedResults'][0]['ParsedText'])) {
            Log::error('OCR Error Response: ' . json_encode($data));
            throw new \Exception('No se pudo extraer texto.');
        }

        return strtoupper($data['ParsedResults'][0]['ParsedText']);
    }
}
