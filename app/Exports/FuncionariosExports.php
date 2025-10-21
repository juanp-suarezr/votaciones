<?php

namespace App\Exports;

use App\Models\Funcionarios_planeacion;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

class FuncionariosExports
{
    public function generarWord($estado, $search)
    {
        $funcionarios = Funcionarios_planeacion::when($search, function ($query) use ($search) {
            $query->where('nombre', 'like', "%$search%")
                ->orWhere('identificacion', 'like', "%$search%");
        })
        ->when($estado !== null, function ($query) use ($estado) {
            $query->where('estado', $estado);
        })
        ->get();

        Log::info("Generando Word para " . $funcionarios->count() . " funcionarios.");

        $phpWord = new PhpWord();
        $section = $phpWord->addSection();

        $section->addTitle('Listado de Funcionarios', 1);
        $section->addText('Fecha de generación: ' . Carbon::now()->format('Y-m-d H:i'));
        $section->addTextBreak(1);

        $manager = new ImageManager(new GdDriver());
        $tempFiles = [];

        foreach ($funcionarios as $funcionario) {
            try {
                // ✅ 1. Generar QR como SVG (BaconQrCode lo soporta nativamente)
                $renderer = new ImageRenderer(
                    new RendererStyle(200),
                    new SvgImageBackEnd()
                );

                $writer = new Writer($renderer);
                $svgData = $writer->writeString(
                    url('/consulta-funcionario/' . $funcionario->identificacion . '/' . $funcionario->id)
                );

                // ✅ 2. Convertir SVG → PNG usando Intervention Image (GD)
                $pngPath = storage_path("app/public/qr_{$funcionario->id}.png");
                $image = $manager->read($svgData);
                $image->encodeByExtension('png');
                $image->save($pngPath);
                $tempFiles[] = $pngPath;

                // ✅ 3. Agregar datos al Word
                $section->addText("Nombre: {$funcionario->nombre}", ['bold' => true]);
                $section->addText("Identificación: {$funcionario->identificacion}");
                $section->addText("Área: {$funcionario->area}");
                $section->addText("Estado: " . ($funcionario->estado ? 'Activo' : 'Inactivo'));
                $section->addTextBreak(1);
                $section->addImage($pngPath, ['width' => 80, 'height' => 80]);
                $section->addTextBreak(2);

            } catch (\Throwable $e) {
                Log::error("Error generando QR para funcionario {$funcionario->id}: {$e->getMessage()}");
                continue;
            }
        }

        // ✅ 4. Generar archivo Word
        $fileName = 'funcionarios_' . now()->format('Y_m_d_H_i') . '.docx';
        $filePath = storage_path('app/public/' . $fileName);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($filePath);

        // ✅ 5. Limpiar PNG temporales
        foreach ($tempFiles as $file) {
            if (file_exists($file)) unlink($file);
        }

        return $filePath;
    }
}
