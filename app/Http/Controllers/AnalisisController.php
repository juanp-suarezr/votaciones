<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Controller;
use App\Models\Eventos;
use App\Models\Hash_proyectos;
use App\Models\Hash_votantes;
use App\Models\Informacion_votantes;
use App\Models\Tipos;
use App\Models\Votos;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\SimpleType\Jc;
use PhpOffice\PhpWord\SimpleType\JcTable;

class AnalisisController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:analisis-listar|analisis-crear|analisis-editar|analisis-eliminar', [
            'only' => ['index', 'analisisPresupuesto', 'exportarPdf', 'exportarWord']
        ]);
        $this->middleware('permission:analisis-crear', ['only' => ['create', 'store']]);
        $this->middleware('permission:analisis-editar', ['only' => ['edit', 'update']]);
        $this->middleware('permission:analisis-eliminar', ['only' => ['destroy']]);
    }
    function index()
    {
        $user = Auth::user();
        $isSupervisor = $user && ($user->hasRole('Supervisor de Evento') || $user->hasRole('Jurado de Evento'));
        $eventoAsignadoId = $isSupervisor ? $user->getEventoAsignadoId() : null;

        $eventosQuery = Eventos::where('estado', '!=', 'Pendiente')
            ->whereNot('nombre', 'Admin')
            ->with('votos');

        if ($isSupervisor && $eventoAsignadoId) {
            $eventosQuery->where('id', $eventoAsignadoId);
        }

        $eventos = $eventosQuery->get()
            ->filter(function ($evento) {
                // Filtra eventos cuyo tipo contiene "proyectos" (mayúsculas o minúsculas)
                return stripos($evento->tipos, 'Presupuesto Participativo') === false;
            })
            ->values();

        $candidatosQuery = Hash_votantes::where('candidato', 1)->with('votante');
        $votantesQuery = Hash_votantes::whereNot('tipo', 'Admin')
            ->whereHas('votante.user.roles', function ($query) {
                $query->where('name', '!=', 'votoBlanco'); // Excluye roles con 'votoBlanco'
            })
            ->with(['votante.user.roles']);

        if ($isSupervisor && $eventoAsignadoId) {
            $candidatosQuery->where('id_evento', $eventoAsignadoId);
            $votantesQuery->where('id_evento', $eventoAsignadoId);
        }

        return Inertia::render(
            'Analisis/Index',
            [

                'eventos' => $eventos,
                'candidatos' => $candidatosQuery->get(),
                'votantes' => $votantesQuery->get(),
            ]
        );
    }

    function analisisPresupuesto()
    {
        $user = Auth::user();
        $isSupervisor = $user && ($user->hasRole('Supervisor de Evento') || $user->hasRole('Jurado de Evento'));
        $eventoAsignadoId = $isSupervisor ? $user->getEventoAsignadoId() : null;

        //variables
        $id_evento = ($isSupervisor && $eventoAsignadoId) ? $eventoAsignadoId : 15;
        $subtipo = 1;
        $votos_virtuales = [];
        $votos_fisicos = [];

        if (!$isSupervisor && RequestFacade::input('id_evento')) {
            # code...
            $id_evento = RequestFacade::input('id_evento');
        }
        if (RequestFacade::input('subtipo')) {
            # code...
            $subtipo = RequestFacade::input('subtipo');
        }

        $eventos = Eventos::where('estado', '!=', 'Pendiente')
            ->whereNot('nombre', 'Admin')
            ->with('votos')
            ->get()
            ->filter(function ($evento) {
                // Filtra eventos cuyo tipo contiene "proyectos" (mayúsculas o minúsculas)
                return stripos($evento->tipos, 'Presupuesto Participativo') !== false;
            })
            ->values();

        $proyectos = Hash_proyectos::when($id_evento, function ($query, $id_evento) {
            $query->where('id_evento',  $id_evento);
        })
            ->whereHas('proyecto', function ($query) use ($subtipo) {
                $query->when($subtipo, function ($query, $subtipo) {
                    $query->where('subtipo', $subtipo);
                });
            })->get();




        $votos_virtuales = Votos::select('id', 'id_eventos', 'id_proyecto', 'subtipo', 'isVirtual')
        ->where('id_eventos', $id_evento)
        ->get();

        $votos_fisicos = Votos::select('id', 'id_eventos', 'id_proyecto', 'subtipo', 'isVirtual')
        ->where('id_eventos', $id_evento)
        ->get();


        return Inertia::render(
            'Analisis/PresupuestoParticipativo',
            [

                'eventos' => $eventos,
                'proyectos' => $proyectos,
                'votos_virtuales' => $votos_virtuales,
                'votos_fisicos' => $votos_fisicos,
            ]
        );
    }

    /**
     * Genera y descarga el reporte de resultados en formato PDF
     */
    public function exportarPdf($idEvento)
    {
        $data = $this->generarDatosReporte($idEvento);
        $eventoSlug = preg_replace('/[^A-Za-z0-9]/', '_', $data['evento']->nombre ?? 'Evento');
        $nombreArchivo = 'Reporte_Resultados_' . $eventoSlug . '_' . date('Ymd_His') . '.pdf';

        $pdf = Pdf::loadView('pdf.reporte_resultados', $data)
            ->setPaper('letter', 'portrait')
            ->setOptions([
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled' => true,
                'defaultFont' => 'Helvetica, sans-serif',
            ]);

        return $pdf->download($nombreArchivo);
    }

    /**
     * Genera y descarga el reporte de resultados en formato Word (.docx)
     */
    public function exportarWord($idEvento)
    {
        $data = $this->generarDatosReporte($idEvento);
        $evento = $data['evento'];

        $phpWord = new PhpWord();
        $phpWord->setDefaultFontName('Arial');
        $phpWord->setDefaultFontSize(10);

        // Configuración de página y márgenes
        $section = $phpWord->addSection([
            'marginLeft' => 1200,
            'marginRight' => 1200,
            'marginTop' => 1200,
            'marginBottom' => 1200,
        ]);

        // Encabezado con Logo a la derecha
        $headerTable = $section->addTable([
            'borderSize' => 0,
            'borderColor' => 'FFFFFF',
            'width' => 100 * 50,
            'unit' => 'pct'
        ]);
        $headerTable->addRow();
        $cellLeft = $headerTable->addCell(6000);
        $cellLeft->addText(
            'REPÚBLICA DE COLOMBIA — ALCALDÍA DE PEREIRA',
            ['name' => 'Arial', 'size' => 8.5, 'color' => '64748B', 'bold' => true]
        );

        $cellRight = $headerTable->addCell(4000, ['alignment' => JcTable::END]);
        if (!empty($data['logo_path']) && file_exists($data['logo_path'])) {
            $cellRight->addImage($data['logo_path'], [
                'width' => 120,
                'height' => 45,
                'alignment' => Jc::END,
            ]);
        }

        $section->addText('', [], ['spaceAfter' => 100]);

        // Título del documento
        $section->addText(
            'INFORME DE REPORTE DE RESULTADOS PROCESO DE ELECCIÓN VIRTUAL',
            ['name' => 'Arial', 'size' => 12.5, 'bold' => true, 'color' => '0F172A'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 50]
        );
        $section->addText(
            mb_strtoupper($evento->nombre, 'UTF-8'),
            ['name' => 'Arial', 'size' => 12.5, 'bold' => true, 'color' => 'C20E1A'],
            ['alignment' => Jc::CENTER, 'spaceAfter' => 180]
        );

        // Subtítulo
        $section->addText(
            'Secretaría de Tecnologías de la Información y las Comunicaciones',
            ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => '1E293B'],
            ['spaceAfter' => 100]
        );

        // Párrafo introductorio
        $section->addText(
            "La Secretaría TIC de la alcaldía de Pereira, presenta el siguiente informe correspondiente al sistema de reporte y visualización de resultados del proceso de elección virtual para {$evento->nombre}, llevado a cabo entre el {$data['fecha_inicio']} y el {$data['fecha_fin']}.",
            ['name' => 'Arial', 'size' => 9.5, 'color' => '334155'],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 140]
        );

        // 1. Objetivo
        $section->addText('1. Objetivo', ['name' => 'Arial', 'size' => 10.5, 'bold' => true, 'color' => '0F172A'], ['spaceAfter' => 50]);
        $section->addText(
            'Presentar el tablero de monitoreo y reporte de resultados implementado para el proceso de elección virtual, permitiendo la visualización del comportamiento electoral, participación y consolidación de los votos por cada estamento.',
            ['name' => 'Arial', 'size' => 9.5, 'color' => '334155'],
            ['alignment' => Jc::BOTH, 'spaceAfter' => 140]
        );

        // 2. Alcance del Sistema
        $section->addText('2. Alcance del Sistema', ['name' => 'Arial', 'size' => 10.5, 'bold' => true, 'color' => '0F172A'], ['spaceAfter' => 50]);
        $section->addListItem('Visualización de resultados transparentes.', 0, ['name' => 'Arial', 'size' => 9.5]);
        $section->addListItem('Monitoreo de participación y abstención por categorías.', 0, ['name' => 'Arial', 'size' => 9.5]);
        $section->addListItem('Consolidación automática de votos por tipo de cargo.', 0, ['name' => 'Arial', 'size' => 9.5]);
        $section->addListItem('Trazabilidad y transparencia del proceso electoral virtual.', 0, ['name' => 'Arial', 'size' => 9.5]);
        $section->addText('', [], ['spaceAfter' => 120]);

        // 3. Dashboard General del Proceso Electoral
        $section->addText('3. Dashboard General del Proceso Electoral', ['name' => 'Arial', 'size' => 10.5, 'bold' => true, 'color' => '0F172A'], ['spaceAfter' => 50]);
        $pDash = $section->addTextRun(['alignment' => Jc::BOTH, 'spaceAfter' => 100]);
        $pDash->addText('El sistema cuenta con un dashboard general que permite visualizar el estado consolidado del proceso. Se reporta un total de ');
        $pDash->addText(number_format($data['total_habilitados'], 0, ',', '.') . ' votantes habilitados', ['bold' => true]);
        $pDash->addText(', de los cuales ');
        $pDash->addText(number_format($data['total_votos'], 0, ',', '.') . ' realizaron el voto virtual', ['bold' => true]);
        $pDash->addText(" ({$data['porcentaje_participacion']}% de participación).");

        $section->addText('La distribución del total de votos según el tipo es la siguiente:', ['name' => 'Arial', 'size' => 9.5, 'bold' => true], ['spaceAfter' => 50]);
        foreach ($data['distribucion_tipos'] as $dist) {
            $section->addListItem("{$dist['tipo']}: " . number_format($dist['votos'], 0, ',', '.') . " votos.", 0, ['name' => 'Arial', 'size' => 9.5]);
        }
        $section->addText('', [], ['spaceAfter' => 140]);

        // 4. Resultados por Categorías
        $section->addText('4. Resultados por Categorías', ['name' => 'Arial', 'size' => 10.5, 'bold' => true, 'color' => '0F172A'], ['spaceAfter' => 50]);
        $section->addText('A continuación, se detalla el comportamiento de la votación por cada grupo de participación:', ['name' => 'Arial', 'size' => 9.5], ['spaceAfter' => 80]);

        $catIdx = 1;
        foreach ($data['categorias'] as $cat) {
            $section->addText("4.{$catIdx}. {$cat['tipo']}", ['name' => 'Arial', 'size' => 10, 'bold' => true, 'color' => '004884'], ['spaceAfter' => 50]);
            
            $pCat = $section->addTextRun(['spaceAfter' => 80]);
            $pCat->addText('En esta categoría se registró un nivel de votación de ');
            $pCat->addText(number_format($cat['votos'], 0, ',', '.') . ' votos realizados', ['bold' => true]);
            $pCat->addText(' frente a una abstención de ');
            $pCat->addText(number_format($cat['abstencion'], 0, ',', '.') . " ({$cat['porcentaje']}% de participación).", ['bold' => true]);
            $pCat->addText(' Los resultados por candidato fueron:');

            foreach ($cat['candidatos'] as $cand) {
                if (!empty($cand['es_ganador'])) {
                    $section->addListItem("{$cand['nombre']}: {$cand['votos']} votos ({$cand['porcentaje']}%) — Ganador/a", 0, ['name' => 'Arial', 'size' => 9.5, 'bold' => true, 'color' => '15803D']);
                } else {
                    $section->addListItem("{$cand['nombre']}: {$cand['votos']} votos ({$cand['porcentaje']}%)", 0, ['name' => 'Arial', 'size' => 9.5]);
                }
            }
            $section->addText('', [], ['spaceAfter' => 50]);

            // Tabla de candidatos
            $table = $section->addTable([
                'borderColor' => 'D1D5DB',
                'borderSize'  => 6,
                'cellMargin'  => 70,
                'width'       => 100 * 50,
                'unit'        => 'pct'
            ]);
            $table->addRow(300);
            $table->addCell(5000, ['bgColor' => 'F1F5F9'])->addText('NOMBRE', ['bold' => true, 'size' => 8.5]);
            $table->addCell(2500, ['bgColor' => 'F1F5F9'])->addText('IDENTIFICACIÓN', ['bold' => true, 'size' => 8.5]);
            $table->addCell(1500, ['bgColor' => 'F1F5F9'])->addText('VOTOS', ['bold' => true, 'size' => 8.5, 'alignment' => Jc::CENTER]);
            $table->addCell(1500, ['bgColor' => 'F1F5F9'])->addText('%', ['bold' => true, 'size' => 8.5, 'alignment' => Jc::CENTER]);

            foreach ($cat['candidatos'] as $cand) {
                $bgColor = !empty($cand['es_ganador']) ? 'F0FDF4' : ($cand['es_blanco'] ? 'F8FAFC' : 'FFFFFF');
                $table->addRow();
                $cellNom = $table->addCell(5000, ['bgColor' => $bgColor]);
                $cellNom->addText(
                    $cand['nombre'] . (!empty($cand['es_ganador']) ? ' (Ganador/a)' : ''),
                    ['size' => 8.5, 'bold' => !empty($cand['es_ganador']), 'color' => !empty($cand['es_ganador']) ? '15803D' : '334155']
                );
                $table->addCell(2500, ['bgColor' => $bgColor])->addText($cand['identificacion'], ['size' => 8.5]);
                $table->addCell(1500, ['bgColor' => $bgColor])->addText((string)$cand['votos'], ['size' => 8.5, 'bold' => true, 'alignment' => Jc::CENTER]);
                $table->addCell(1500, ['bgColor' => $bgColor])->addText($cand['porcentaje'] . '%', ['size' => 8.5, 'alignment' => Jc::CENTER]);
            }
            $section->addText('', [], ['spaceAfter' => 120]);
            $catIdx++;
        }

        // 5. Características Técnicas
        $section->addText('5. Características Técnicas', ['name' => 'Arial', 'size' => 10.5, 'bold' => true, 'color' => '0F172A'], ['spaceAfter' => 50]);
        $section->addListItem('Dashboard web interactivo con actualización dinámica.', 0, ['name' => 'Arial', 'size' => 9.5]);
        $section->addListItem('Visualización gráfica de niveles de abstención y votos efectivos.', 0, ['name' => 'Arial', 'size' => 9.5]);
        $section->addListItem('Diseño responsive adaptable a diferentes dispositivos móviles.', 0, ['name' => 'Arial', 'size' => 9.5]);
        $section->addListItem('Procesamiento automatizado de consolidación de votos.', 0, ['name' => 'Arial', 'size' => 9.5]);
        $section->addText('', [], ['spaceAfter' => 120]);

        // 6. Beneficios Institucionales
        $section->addText('6. Beneficios Institucionales', ['name' => 'Arial', 'size' => 10.5, 'bold' => true, 'color' => '0F172A'], ['spaceAfter' => 50]);
        $section->addListItem('Transparencia: Disponibilidad inmediata de información consolidada para los equipos administrativos y fortalecimiento de la transparencia del proceso electoral.', 0, ['name' => 'Arial', 'size' => 9.5]);
        $section->addListItem('Eficiencia: Procesamiento automatizado que elimina errores manuales en el conteo.', 0, ['name' => 'Arial', 'size' => 9.5]);
        $section->addListItem('Accesibilidad: Facilidad de consulta para todos los niveles de la entidad.', 0, ['name' => 'Arial', 'size' => 9.5]);
        $section->addText('', [], ['spaceAfter' => 180]);

        // Cierre
        $section->addText('Secretaría TIC', ['name' => 'Arial', 'size' => 10.5, 'bold' => true]);
        $section->addText('Alcaldía de Pereira', ['name' => 'Arial', 'size' => 10]);

        $eventoSlug = preg_replace('/[^A-Za-z0-9]/', '_', $evento->nombre ?? 'Evento');
        $fileName = 'Reporte_Resultados_' . $eventoSlug . '_' . date('Ymd_His') . '.docx';
        $tempFile = tempnam(sys_get_temp_dir(), 'word_');
        $objWriter = IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save($tempFile);

        return response()->download($tempFile, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    /**
     * Helper privado para recopilar y consolidar los datos de reporte para PDF y Word
     */
    private function generarDatosReporte($idEvento)
    {
        $user = Auth::user();
        $isSupervisor = $user && ($user->hasRole('Supervisor de Evento') || $user->hasRole('Jurado de Evento'));
        $eventoAsignadoId = $isSupervisor ? $user->getEventoAsignadoId() : null;

        if ($isSupervisor && $eventoAsignadoId && $idEvento != $eventoAsignadoId) {
            abort(403, 'No tiene autorización para generar reportes de este evento.');
        }

        $evento = Eventos::with('votos')->findOrFail($idEvento);

        // Formateo de fechas
        $fechaInicioStr = $evento->fecha_inicio 
            ? Carbon::parse($evento->fecha_inicio)->locale('es')->isoFormat('D [de] MMMM [de] YYYY (HH:mm:ss)') 
            : ($evento->votos->isNotEmpty() ? Carbon::parse($evento->votos->min('created_at'))->locale('es')->isoFormat('D [de] MMMM [de] YYYY (HH:mm:ss)') : 'N/A');

        $fechaFinStr = $evento->fecha_fin 
            ? Carbon::parse($evento->fecha_fin)->locale('es')->isoFormat('D [de] MMMM [de] YYYY (HH:mm:ss)') 
            : ($evento->votos->isNotEmpty() ? Carbon::parse($evento->votos->max('created_at'))->locale('es')->isoFormat('D [de] MMMM [de] YYYY (HH:mm:ss)') : 'N/A');

        // Censo de votantes habilitados
        $totalHabilitados = Hash_votantes::where('id_evento', $evento->id)
            ->where('candidato', 0)
            ->whereNot('tipo', 'Admin')
            ->count();

        $totalVotos = $evento->votos->count();
        $totalAbstencion = max(0, $totalHabilitados - $totalVotos);
        $porcentajeParticipacion = $totalHabilitados > 0 ? round(($totalVotos / $totalHabilitados) * 100, 2) : 0;

        // Categorías o tipos del evento
        $ignoredFlags = ['withBanner', 'Proyecto', 'Delegados'];
        $tiposEvento = array_values(array_filter(
            array_map('trim', explode('|', (string) $evento->tipos)),
            fn($t) => !in_array($t, $ignoredFlags) && $t !== ''
        ));

        if (empty($tiposEvento)) {
            $tiposEvento = Hash_votantes::where('id_evento', $evento->id)
                ->whereNot('tipo', 'Admin')
                ->pluck('tipo')
                ->flatMap(fn($t) => explode('|', (string)$t))
                ->map(fn($t) => trim($t))
                ->filter(fn($t) => !in_array($t, $ignoredFlags) && $t !== '')
                ->unique()
                ->values()
                ->all();
        }

        $distribucionTipos = [];
        $categoriasDetalladas = [];

        foreach ($tiposEvento as $tipo) {
            // Censo en este tipo
            $censoTipo = Hash_votantes::where('id_evento', $evento->id)
                ->where('candidato', 0)
                ->where(function ($q) use ($tipo) {
                    $q->orWhere('tipo', $tipo)
                      ->orWhere('tipo', 'like', $tipo . '|%')
                      ->orWhere('tipo', 'like', '%|' . $tipo)
                      ->orWhere('tipo', 'like', '%|' . $tipo . '|%');
                })
                ->count();

            // Votos en este tipo
            $votosTipo = $evento->votos->filter(function ($v) use ($tipo) {
                $tokens = explode('|', (string)$v->tipo);
                return in_array($tipo, $tokens);
            })->count();

            $abstencionTipo = max(0, $censoTipo - $votosTipo);
            $porcentajeTipo = $censoTipo > 0 ? round(($votosTipo / $censoTipo) * 100, 2) : 0;

            $distribucionTipos[] = [
                'tipo' => $tipo,
                'votos' => $votosTipo,
                'censo' => $censoTipo,
                'abstencion' => $abstencionTipo,
                'porcentaje' => $porcentajeTipo,
            ];

            // Candidatos
            $candidatosHash = Hash_votantes::where('id_evento', $evento->id)
                ->where('candidato', 1)
                ->where(function ($q) use ($tipo) {
                    $q->orWhere('tipo', $tipo)
                      ->orWhere('tipo', 'like', $tipo . '|%')
                      ->orWhere('tipo', 'like', '%|' . $tipo)
                      ->orWhere('tipo', 'like', '%|' . $tipo . '|%');
                })
                ->with('votante')
                ->get();

            $candidatosData = [];
            foreach ($candidatosHash as $cand) {
                $nombre = $cand->votante ? ($cand->votante->nombre . ' ' . ($cand->votante->apellido ?? '')) : 'Candidato #' . $cand->id;
                $identificacion = $cand->votante ? $cand->votante->identificacion : 'N/A';
                $votosCand = $evento->votos->where('id_candidato', $cand->id_votante)->count();
                $esBlanco = stripos($nombre, 'blanco') !== false;

                $candidatosData[] = [
                    'id' => $cand->id,
                    'nombre' => trim($nombre),
                    'identificacion' => $identificacion,
                    'votos' => $votosCand,
                    'es_blanco' => $esBlanco,
                    'porcentaje' => $votosTipo > 0 ? round(($votosCand / $votosTipo) * 100, 2) : 0,
                    'es_ganador' => false,
                ];
            }

            // Separar regulares y voto en blanco
            $regulares = array_values(array_filter($candidatosData, fn($c) => !$c['es_blanco']));
            $blancos = array_values(array_filter($candidatosData, fn($c) => $c['es_blanco']));

            // Ordenar regulares de mayor a menor por votos
            usort($regulares, function ($a, $b) {
                if ($b['votos'] !== $a['votos']) {
                    return $b['votos'] <=> $a['votos'];
                }
                return strcmp($a['nombre'], $b['nombre']);
            });

            // Ordenar blancos de mayor a menor
            usort($blancos, fn($a, $b) => $b['votos'] <=> $a['votos']);

            // Marcar ganador
            if (!empty($regulares) && $regulares[0]['votos'] > 0) {
                $regulares[0]['es_ganador'] = true;
            }

            $candidatosFinal = array_merge($regulares, $blancos);

            $categoriasDetalladas[] = [
                'tipo' => $tipo,
                'censo' => $censoTipo,
                'votos' => $votosTipo,
                'abstencion' => $abstencionTipo,
                'porcentaje' => $porcentajeTipo,
                'candidatos' => $candidatosFinal,
            ];
        }

        $logoPath = public_path('assets/img/logo.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoData = file_get_contents($logoPath);
            $logoBase64 = 'data:image/png;base64,' . base64_encode($logoData);
        }

        return [
            'evento' => $evento,
            'fecha_inicio' => $fechaInicioStr,
            'fecha_fin' => $fechaFinStr,
            'fecha_generacion' => Carbon::now()->locale('es')->isoFormat('D [de] MMMM [de] YYYY (HH:mm:ss)'),
            'total_habilitados' => $totalHabilitados,
            'total_votos' => $totalVotos,
            'total_abstencion' => $totalAbstencion,
            'porcentaje_participacion' => $porcentajeParticipacion,
            'distribucion_tipos' => $distribucionTipos,
            'categorias' => $categoriasDetalladas,
            'logo_base64' => $logoBase64,
            'logo_path' => $logoPath,
        ];
    }
}
