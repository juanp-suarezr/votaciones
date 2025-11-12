<?php

namespace App\Http\Controllers;

use App\Exports\AuditoriaRegistrosExports;
use Illuminate\Support\Facades\Request as RequestFacade;
use App\Http\Controllers\Controller;
use App\Models\AuditoriaRegistro;
use App\Models\AuditoriaVotos;
use App\Models\Eventos;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Log;

class auditoriasController extends Controller
{


    /**
     * Display a listing of the resource.
     */
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:auditoria', ['only' => ['index', 'auditoriaValidaciones']]);
    }

    public function index()
    {
        $id_evento = 15;
        if(RequestFacade::input('id_evento')){
            $id_evento = RequestFacade::input('id_evento');

        }


        $voto_auditoria = AuditoriaVotos::select('id_evento', 'voto_id', 'accion', 'tipo_voto', 'usuario_id', 'ip_address', 'user_agent', 'created_at')
        ->where('id_evento', $id_evento)
        ->with('usuario:id,name,identificacion', 'voto:id_votante,id', 'voto.votante:id,nombre,identificacion')
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString(); // Mantener los parámetros en la URL

            return Inertia::render(
            'Auditoria/Index',
            [
                'voto_auditoria' => $voto_auditoria,
                'eventos' => Eventos::select('id', 'nombre')->whereHas('votos')->get(),
                'filters' => RequestFacade::only(['id_evento']),
            ]
        );


    }

    public function auditoriaValidaciones()
    {
        $id_evento = 15;
        if(RequestFacade::input('id_evento')){
            $id_evento = RequestFacade::input('id_evento');
        }
        $id_user = null;
        if(RequestFacade::input('id_user')){
            $id_user = RequestFacade::input('id_user');
        }

        $auditoria_registro = AuditoriaRegistro::select('id_evento', 'accion', 'votante_id', 'usuario_id', 'ip_address', 'user_agent', 'created_at')
        ->where('id_evento', $id_evento)
        ->where(function($query) use ($id_user) {
            if ($id_user) {
                $query->where('usuario_id', $id_user);
            }
        })
        ->with('usuario:id,name', 'hash_votante:id_votante,id', 'hash_votante.votante:id,nombre,identificacion')
            ->orderBy('created_at', 'desc')
            ->paginate(5)
            ->withQueryString(); // Mantener los parámetros en la URL

        return Inertia::render('Auditoria/AuditoriaValidaciones',
            [
                'auditoria_registro' => $auditoria_registro,
                'eventos' => Eventos::select('id', 'nombre')->whereHas('votos')->get(),
                'filters' => RequestFacade::only(['id_evento']),
            ]
        );
    }

    public function excel()
    {
        try {
            // limpiar buffers previos y preparar salida
            @ob_end_clean();
            ob_start();

            $id_evento = 15;
            if (RequestFacade::input('id_evento')) {
                $id_evento = intval(RequestFacade::input('id_evento'));
            }

            return Excel::download(new AuditoriaRegistrosExports($id_evento), 'auditoria_registros.xls', \Maatwebsite\Excel\Excel::XLS);
        } catch (\Throwable $e) {
            // registrar error con detalle para debugging
            Log::error('Error generando Excel de auditoria_registros', [
                'message'   => $e->getMessage(),
                'file'      => $e->getFile(),
                'line'      => $e->getLine(),
                'trace'     => $e->getTraceAsString(),
                'id_evento' => $id_evento ?? null,
                'request'   => RequestFacade::all(),
            ]);

            // intentar limpiar buffers abiertos
            @ob_end_clean();

            // responder al cliente con mensaje de error visible
            return redirect()->back()->with('error', 'Ocurrió un error al generar el archivo. Revise los logs.');
        }
    }
}
