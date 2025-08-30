<?php

// app/Http/Controllers/CertificadosController.php
namespace App\Http\Controllers;

use App\Models\Caninos;
use App\Models\InformacionUsuario;
use App\Models\PageView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Illuminate\Support\Facades\Request as RequestFacade;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;

use App\Models\Delegado;
use App\Models\Delegados;
use App\Models\Eventos;
use App\Models\ParametrosDetalle;
use App\Models\Votos;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

use SimpleSoftwareIO\QrCode\Facades\QrCode;

class CertificadosController extends Controller
{

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        // $this->middleware('permission:certificados-listar|certificados-crear|certificados-editar|certificados-eliminar', ['only' => ['index', 'store']]);
        // $this->middleware('permission:certificados-crear', ['only' => ['create', 'store']]);
        // $this->middleware('permission:certificados-editar', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:certificados-eliminar', ['only' => ['destroy']]);
    }
    public function index()
    {


        return Inertia::render(
            'Certificados/Index',
            [
                'filters' => RequestFacade::only(['identificacion', 'nombre']),
            ]
        );
    }



    //generar certificado
    public function descargarCertificado($id, $idVotante = null, $id_padre = null)
    {

        ini_set('memory_limit', '1024M'); // 1 GB, puedes ajustar el valor
        ini_set('max_execution_time', 180); // 180 segundos (3 minutos)

        $idVotante = $idVotante == 0 ? null : $idVotante;

        $idVotante = Auth::user()->votantes->id ?? $idVotante;
        
        

        if (!$idVotante) {
            abort(403, 'No se puede generar el certificado sin un votante válido.');
        }

        

        // Obtener el evento y cargar solo al votante relacionado
        $evento = Eventos::select('id', 'nombre', 'fecha_inicio')
            ->with([
                'votantes' => function ($query) use ($idVotante) {
                    $query->where('id_votante', $idVotante)
                        ->select('id_votante', 'id_evento')
                        ->with([
                            'votante:id,nombre,tipo_documento,identificacion,comuna'
                        ]);
                }
            ])
            ->findOrFail($id);

            $votante = $evento->votantes->first()->votante ?? null;
        if($id_padre){
            $evento_padre = Eventos::select('id', 'nombre', 'fecha_inicio')
            ->with([
                'votantes' => function ($query) use ($idVotante) {
                    $query->where('id_votante', $idVotante)
                        ->select('id_votante', 'id_evento')
                        ->with([
                            'votante:id,nombre,tipo_documento,identificacion,comuna'
                        ]);
                }
            ])
            ->findOrFail($id_padre);

            $votante = $evento_padre->votantes->first()->votante ?? null;
        }


        // Generar nombre único para el PDF

        $nombreLimpio = preg_replace('/[^A-Za-z0-9\-]/', '_', $evento->nombre);




        $qr = 'data:image/svg+xml;base64,' . base64_encode(QrCode::format('svg')->size(120)->generate(url('/consulta-certificado/' . $evento->id . '/' . $votante->id)));

        
        $comuna = ParametrosDetalle::select('id', 'detalle')->findOrFail($votante->comuna);
        $voto = Votos::select('id', 'id_votante', 'id_eventos', 'isVirtual', 'created_at')
            ->where('id_eventos', $evento->id)
            ->where('id_votante', $votante->id)
            ->firstOrFail();

        $delegado = Delegados::select('nombre', 'firma', 'cargo')
                ->where('estado', 1)
                ->where('tipo', 'secretario')

                ->first();


        $pdf = Pdf::loadView('pdf.certificado', [
            'evento' => $evento,
            'votante' => $votante,
            'comuna' => $comuna->detalle,
            'voto' => $voto,
            'delegado' => $delegado,
            'created_at' => $voto->created_at->format('d/m/Y H:i'),
            'annio_actual' => Carbon::parse($evento->fecha_inicio)->year,
            'qr' => $qr
        ]);

        return $pdf->download('certificado' . $nombreLimpio . '_' . $votante->identificacion . '.pdf');
    }

    //ver certificado
    public function verCertificado($id)
    {
        ini_set('memory_limit', '1024M');
        ini_set('max_execution_time', 180);

        $canino = Caninos::with([
            'user.informacionUsuario:nombre_completo,tipo_documento,numero_documento,direccion,telefono,celular,comuna,edad,zona,user_id',
            'certificado'
        ])->findOrFail($id);

        $delegado = Delegado::where('estado', 1)->first();
        $nombreLimpio = preg_replace('/[^A-Za-z0-9\-]/', '_', $canino->nombre);

        Log::info('Datos para PDF', ['canino' => $canino, 'delegado' => $delegado]);

        $qr = 'data:image/svg+xml;base64,' . base64_encode(QrCode::format('svg')->size(120)->generate(url('/consulta-certificado/' . $canino->id)));

        $pdf = Pdf::loadView('pdf.certificado', [
            'canino' => $canino,
            'delegado' => $delegado,
            'qr' => $qr
        ]);

        return $pdf->stream('certificado' . $nombreLimpio . '_' . $canino->id . '.pdf');
    }
}
