<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Acta_fin;
use App\Models\Acta_inicio;
use App\Models\Delegados;
use App\Models\Eventos;
use App\Models\ParametrosDetalle;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Request as RequestFacade;
use Inertia\Inertia;

class ActasVirtualesController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    function __construct()
    {
        $this->middleware('permission:actas-virtuales-listar', ['only' => ['index', 'show']]);
    }


    public function index(Request $request)
    {
        $id_evento = 16;

        if (RequestFacade::input('id_evento')) {
            $id_evento = RequestFacade::input('id_evento');
        }

        $eventos = Eventos::where('tipos', 'LIKE', '%Proyecto%')->get();

        // Actas iniciales
        $acta_inicio = Acta_inicio::when($id_evento, function ($query, $id_evento) {
            $query->where('id_evento',  $id_evento);
        })
            ->when(RequestFacade::input('modalidad'), function ($query, $modalidad) {
                $query->where('modalidad',  $modalidad);
            })
            ->when(RequestFacade::input('subtipo'), function ($query, $subtipo) {
                $query->where('comunas',  $subtipo);
            })
            ->where(function ($query) {
                $search = RequestFacade::input('search');
                if ($search) {
                    $query->whereHas('jurado', function ($q) use ($search) {
                        $q->where('nombre', 'like', '%' . $search . '%')
                            ->orWhere('identificacion', $search);
                    });
                }
            })
            ->with('jurado')
            ->get()
            ->map(function ($acta) {
                $acta->tipo = 'inicial';
                return $acta;
            });

        // Actas finales
        $acta_fin = Acta_fin::when($id_evento, function ($query, $id_evento) {
            $query->where('id_evento',  $id_evento);
        })
            ->when(RequestFacade::input('modalidad'), function ($query, $modalidad) {
                $query->where('modalidad',  $modalidad);
            })
            ->when(RequestFacade::input('subtipo'), function ($query, $subtipo) {
                $query->where('comunas',  $subtipo);
            })
            ->where(function ($query) {
                $search = RequestFacade::input('search');
                if ($search) {
                    $query->whereHas('jurado', function ($q) use ($search) {
                        $q->where('nombre', 'like', '%' . $search . '%')
                            ->orWhere('identificacion', $search);
                    });
                }
            })
            ->with('jurado')
            ->get()
            ->map(function ($acta) {
                $acta->tipo = 'cierre';
                return $acta;
            });

        // Unir ambas colecciones en un solo array
        $actas = $acta_inicio->merge($acta_fin)->values();

        // Paginador manual
        $perPage = 10;
        $page = $request->input('page', 1);
        $actasCollection = collect($actas);
        $paginator = new LengthAwarePaginator(
            $actasCollection->forPage($page, $perPage)->values(),
            $actasCollection->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        return Inertia::render('ActasVirtuales/Index', [
            'eventos' => $eventos,
            'actas' => $paginator,
            'parametros' => ParametrosDetalle::where('estado', 1)->get(),
        ]);
    }

    public function show(Request $request, $id)
    {
        $tipo = $request->query('tipo');

        $query = Acta_fin::query();
        if ($tipo == 'inicial') {
            $query = Acta_inicio::query();
        }
        $acta = $query->with(['jurado.user.biometrico', 'evento:id,nombre'])
            ->findOrFail($id);

        $delegados = Delegados::where('tipo', 'secretario')->get();



        return Inertia::render('ActasVirtuales/Show', [
            'acta' => $acta,
            'parametros' => ParametrosDetalle::where('estado', 1)->get(),
            'tipo' => $tipo,
            'delegados' => $delegados,

        ]);
    }
}
