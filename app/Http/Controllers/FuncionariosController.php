<?php

// app/Http/Controllers/CertificadosController.php
namespace App\Http\Controllers;


use App\Exports\FuncionariosExports;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Request as RequestFacade;

use App\Models\Funcionarios_planeacion;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use ZipArchive;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Response;

class FuncionariosController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:funcionario-listar|funcionario-crear|funcionario-editar|funcionario-eliminar', ['only' => ['index', 'store']]);
        $this->middleware('permission:funcionario-crear', ['only' => ['create', 'store']]);
        $this->middleware('permission:funcionario-editar', ['only' => ['edit', 'update']]);
    }

    public function index()
    {

        $estado = RequestFacade::input('estado');


        $funcionarios = Funcionarios_planeacion::when(RequestFacade::input('search'), function ($query, $search) {
            $query->where('nombre', 'like', '%' . $search . '%')
                ->orWhere('identificacion', 'like', '%' . $search . '%');
        })
            ->when($estado !== null, function ($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->paginate(10)
            ->withQueryString();

        return Inertia::render(
            'Funcionarios/Index',
            [
                'funcionarios' => $funcionarios,
                'filters' => RequestFacade::only(['search', 'estado']),
            ]
        );
    }

    //register
    public function create(Request $request)
    {


        return Inertia::render('Funcionarios/Create', []);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'identificacion' => 'required|string|max:20|unique:funcionarios_planeacion,identificacion',
            'area' => 'required|string',
            'grupo_sanguineo' => 'required',
        ]);

        DB::beginTransaction();
        try {

            $imageName = 'NA';
            //manejo de imagen
            if ($request->hasFile('foto')) {
                $image = $request->file('foto');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('assets/img/funcionarios_planeacion'), $imageName);
            }

            $funcionario = new Funcionarios_planeacion();
            $funcionario->nombre = $request->nombre;
            $funcionario->identificacion = $request->identificacion;
            $funcionario->area = $request->area;
            $funcionario->grupo_sanguineo = $request->grupo_sanguineo;
            $funcionario->estado = 1;
            $funcionario->foto = $imageName;
            $funcionario->save();

            DB::commit();
            return redirect()->back()->with('success', 'Jurado registrado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al registrar jurado: ' . $e->getMessage());
            return redirect()->back()->withErrors('error', 'Error al registrar el jurado.');
        }
    }

    public function edit($id)
    {
        $funcionario = Funcionarios_planeacion::findOrFail($id);

        return Inertia::render('Funcionarios/Edit', [
            'funcionario' => $funcionario,
        ]);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:200',
            'identificacion' => 'required|string|max:20|unique:funcionarios_planeacion,identificacion,' . $id,
            'area' => 'required|string',
            'grupo_sanguineo' => 'required',
        ]);

        DB::beginTransaction();
        try {

            $funcionario = Funcionarios_planeacion::findOrFail($id);

            //manejo de imagen
            if ($request->hasFile('foto')) {
                //eliminar imagen anterior si existe
                if ($funcionario->foto && $funcionario->foto != 'NA') {
                    $oldImagePath = public_path('assets/img/funcionarios_planeacion/' . $funcionario->foto);
                    if (file_exists($oldImagePath)) {
                        unlink($oldImagePath);
                    }
                }
                $image = $request->file('foto');
                $imageName = time() . '_' . $image->getClientOriginalName();
                $image->move(public_path('assets/img/funcionarios_planeacion'), $imageName);
                $funcionario->foto = $imageName;
            }

            $funcionario->nombre = $request->nombre;
            $funcionario->identificacion = $request->identificacion;
            $funcionario->area = $request->area;
            $funcionario->grupo_sanguineo = $request->grupo_sanguineo;
            $funcionario->estado = $request->estado;
            $funcionario->save();

            DB::commit();
            return redirect()->back()->with('success', 'Funcionario actualizado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar funcionario: ' . $e->getMessage());
            return redirect()->back()->withErrors('error', 'Error al actualizar el funcionario.');
        }
    }

    //descargar zip
    public function zip()
    {
        $estado = RequestFacade::input('estado');
        $search = RequestFacade::input('search');

        // 1️⃣ Obtener los funcionarios
        $funcionarios = Funcionarios_planeacion::when($search, function ($query) use ($search) {
            $query->where('nombre', 'like', "%$search%")
                ->orWhere('identificacion', 'like', "%$search%");
        })
            ->when($estado !== null, function ($query) use ($estado) {
                $query->where('estado', $estado);
            })
            ->get();

        if ($funcionarios->isEmpty()) {
            Log::info('No hay funcionarios para generar ZIP.');
            return redirect()->back()->with('error', 'No hay funcionarios para exportar.');
        }

        // 2️⃣ Carpeta temporal única
        $tempDir = storage_path('app/public/qr_temp_' . now()->format('Ymd_His') . '_' . Str::random(6));
        if (!is_dir($tempDir)) {
            @mkdir($tempDir, 0777, true);
        }

        $generatedFiles = [];

        // 3️⃣ Generar los SVG individualmente (mismo renderer que usas)
        $renderer = new ImageRenderer(
            new RendererStyle(300),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);

        foreach ($funcionarios as $funcionario) {
            try {
                $url = url('/consulta-funcionario/' . $funcionario->identificacion . '/' . $funcionario->id);

                // Sanitizar identificación y nombre para usar en el filename
                $safeIdent = preg_replace('/[^A-Za-z0-9_\-]/', '_', (string) $funcionario->identificacion);
                $safeNombre = preg_replace('/[^A-Za-z0-9_\-]/u', '_', (string) $funcionario->nombre);
                $safeNombre = mb_substr($safeNombre, 0, 40); // acortar si es muy largo

                $filePath = "{$tempDir}/QR_{$funcionario->id}_{$safeIdent}_{$safeNombre}.svg";

                $svgData = $writer->writeString($url);
                file_put_contents($filePath, $svgData);
                $generatedFiles[] = $filePath;
            } catch (\Throwable $e) {
                Log::error("Error generando QR para funcionario {$funcionario->id}: {$e->getMessage()}");
                // continuar con siguientes
            }
        }

        if (empty($generatedFiles)) {
            // limpiar carpeta temporal
            @rmdir($tempDir);
            Log::error("No se generaron archivos QR para incluir en ZIP.");
            return redirect()->back()->with('error', 'No se pudieron generar los códigos QR.');
        }

        // 4️⃣ Crear el ZIP
        $zipFileName = 'funcionarios_qr_' . now()->format('Y_m_d_H_i') . '.zip';
        $zipFile = storage_path('app/public/' . $zipFileName);

        $zip = new ZipArchive;
        $opened = $zip->open($zipFile, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        if ($opened !== true) {
            // limpiar temporales
            foreach ($generatedFiles as $f) {
                @unlink($f);
            }
            @rmdir($tempDir);
            Log::error("No se pudo crear el archivo ZIP en: {$zipFile}");
            return redirect()->back()->with('error', 'No se pudo crear el archivo ZIP.');
        }

        foreach ($generatedFiles as $file) {
            $zip->addFile($file, basename($file));
        }
        $zip->close();

        // 5️⃣ Limpiar archivos temporales (dejamos el ZIP)
        foreach ($generatedFiles as $file) {
            @unlink($file);
        }
        @rmdir($tempDir);


        // 6️⃣ Retornar descarga y borrar ZIP después de enviar
        if (file_exists($zipFile)) {
            return response()->download($zipFile, $zipFileName, [
                'Content-Type' => 'application/zip',
            ])->deleteFileAfterSend(true);
        }

        Log::error("ZIP no encontrado al intentar descargar: {$zipFile}");
        return redirect()->back()->with('error', 'Error al preparar la descarga del ZIP.');
    }
}
