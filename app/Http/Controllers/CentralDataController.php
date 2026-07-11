<?php

namespace App\Http\Controllers;

use App\Services\CentralDataService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CentralDataController extends Controller
{
    protected CentralDataService $centralData;

    public function __construct(CentralDataService $centralData)
    {
        $this->centralData = $centralData;
    }

    /**
     * Consulta una persona en Central Data.
     */
    public function find(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'required|string',
            'numero_documento' => 'required|string',
            'source_project' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'error' => $validator->errors()], 422);
        }

        $result = $this->centralData->findPerson(
            $request->tipo_documento,
            $request->numero_documento,
            $request->source_project ?? 'votaciones'
        );

        return response()->json($result, $result['ok'] ? 200 : ($result['status'] ?: 500));
    }

    /**
     * Sincroniza una persona en Central Data.
     */
    public function sync(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tipo_documento' => 'required|string',
            'numero_documento' => 'required|string',
            'nombres' => 'required|string',
            'apellidos' => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'edad' => 'nullable|integer',
            'genero' => 'required|string',
            'dignatario' => 'nullable|string',
            'condicion' => 'nullable|string',
            'etnia' => 'nullable|string',
            'nivel_estudio' => 'nullable|string',
            'correo' => 'nullable|email',
            'telefono' => 'nullable|string',
            'comuna' => 'nullable|string',
            'barrio' => 'nullable|string',
            'direccion' => 'nullable|string',
            'municipio' => 'nullable|string',
            'departamento' => 'nullable|string',
            'source_project' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['ok' => false, 'error' => $validator->errors()], 422);
        }

        $payload = [
            'tipo_documento' => $this->centralData->getTipoDocumentoDataCenter($request->tipo_documento),
            'numero_documento' => $request->numero_documento,
            'nombres' => trim($request->nombres),
            'apellidos' => trim($request->apellidos),
            'fecha_nacimiento' => $request->edad ? $this->centralData->getDateDataCenter($request->edad) : $request->fecha_nacimiento,
            'edad' => $request->edad,
            'genero' => $this->centralData->getGeneroDataCenter($request->genero),
            'dignatario' => $request->dignatario,
            'condicion' => $this->centralData->getNombreCondicionDataCenter($request->condicion),
            'etnia' => $this->centralData->getNombreEtniaDataCenter($request->etnia),
            'nivel_estudio' => $this->centralData->getNombreNivelEstudioDataCenter($request->nivel_estudio),
            'correo' => $request->correo,
            'telefono' => $request->telefono,
            'comuna' => $this->centralData->limpiarNombreComuna($request->comuna),
            'barrio' => $request->barrio,
            'direccion' => [
                'via principal' => trim($request->direccion ?? ''),
                'municipio' => $request->municipio ?? ($request->comuna != 'Otro' ? 'Pereira' : ''),
                'departamento' => $request->departamento ?? ($request->comuna != 'Otro' ? 'Risaralda' : ''),
            ],
            'source_project' => $request->source_project,
        ];

        $result = $this->centralData->syncPerson($payload);

        return response()->json($result, $result['ok'] ? 200 : ($result['status'] ?: 500));
    }
}
