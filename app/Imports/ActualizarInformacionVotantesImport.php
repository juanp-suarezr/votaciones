<?php

namespace App\Imports;

use App\Models\Informacion_votantes;
use App\Models\Hash_votantes;
use App\Models\Eventos;
use App\Models\ParametrosDetalle;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithStartRow;
use Carbon\Carbon;

class ActualizarInformacionVotantesImport implements ToCollection, WithHeadingRow, WithStartRow
{
    private $numRegistrosActualizados = 0;
    private $numNoEncontrados = 0;
    private $numErrores = 0;
    private $erroresDetallados = [];
    private $votantesActualizados = [];
    private $votantesNoActualizados = [];
    private $totalRegistros = 0;
    private $idEventoValidar = 15; // ID del evento a validar
    private $nombreEvento = '';
    private $comunasProcesadas = []; // Para agrupar comunas

    /**
     * Constructor: Obtiene el nombre del evento automáticamente
     */
    public function __construct()
    {
        $evento = Eventos::find($this->idEventoValidar);
        $this->nombreEvento = $evento ? $evento->nombre : 'Evento ID ' . $this->idEventoValidar;
    }

    /**
     * Indica desde qué fila empezar a leer (fila 1 = encabezados, fila 2 = títulos, datos = fila 3+)
     */
    public function startRow(): int
    {
        return 3;
    }

    /**
     * Mapeo por índice numérico (0-based) según el orden especificado:
     * [0] nombre, [1] tipo documento, [2] numero identificacion, [3] email (opcional),
     * [4] fecha nacimiento, [5] Genero, [6] Grupo poblacional, [7] condicion,
     * [8] numero telefono (opcional), [9] direccion, [10] barrio, [11] comuna
     */
    private $mapeoIndices = [
        0 => 'nombre',
        1 => 'tipo_documento',
        2 => 'identificacion',
        3 => 'email',
        4 => 'nacimiento',
        5 => 'genero',
        6 => 'etnia',
        7 => 'condicion',
        8 => 'celular',
        9 => 'direccion',
        10 => 'barrio',
        11 => 'comuna',
    ];

    public function collection($rows)
    {
        $index = 0;
        $filaExcel = 3; // Los datos start en la fila 3 del Excel

        foreach ($rows as $row) {
            // Saltar filas vacías
            if ($row->filter()->isEmpty()) {
                continue;
            }

            $this->totalRegistros++;

            // Determinar si es acceso por índice o por encabezado
            $rowData = $this->normalizarDatos($row);
            $nombre = $this->obtenerNombre($rowData);

            // Verificar que exista la identificación (dato relacional) - índice 2
            $identificacion = $this->obtenerIdentificacion($rowData);

            if (empty($identificacion)) {
                $this->numErrores++;
                $this->erroresDetallados[] = [
                    'fila' => $filaExcel,
                    'identificacion' => 'N/A',
                    'nombre' => $nombre,
                    'error' => 'Fila sin número de identificación'
                ];
                $this->votantesNoActualizados[] = [
                    'fila' => $filaExcel,
                    'identificacion' => 'N/A',
                    'nombre' => $nombre,
                    'error' => 'Sin identificación'
                ];
                
                $filaExcel++;
                $index++;
                continue;
            }

            try {
                // PRIMERA VALIDACIÓN (PRINCIPAL): Verificar directamente en hash_votantes para el evento
                // Usamos whereHas para buscar solo registros del evento cuyo votante tenga esa identificación
                // Esto evita el problema de encontrar primero registros de otros eventos
                $hashVotante = Hash_votantes::where('id_evento', $this->idEventoValidar)
                    ->whereHas('votante', function ($query) use ($identificacion) {
                        $query->where('identificacion', $identificacion);
                    })
                    ->first();

                if (!$hashVotante) {
                    $this->numNoEncontrados++;
                    $this->erroresDetallados[] = [
                        'fila' => $filaExcel,
                        'identificacion' => $identificacion,
                        'nombre' => $nombre,
                        'error' => "No encontrado en el evento {$this->nombreEvento}"
                    ];
                    $this->votantesNoActualizados[] = [
                        'fila' => $filaExcel,
                        'identificacion' => $identificacion,
                        'nombre' => $nombre,
                        'error' => "No encontrado en el evento {$this->nombreEvento}"
                    ];
                    $filaExcel++;
                    $index++;
                    continue;
                }

                // Obtener el votante desde hash_votante
                $votante = $hashVotante->votante;

                // Preparar datos para actualizar
                $datosActualizar = $this->prepararDatos($rowData, $row);

                

                // Actualizar solo si hay datos para actualizar
                if (!empty($datosActualizar)) {
                    $votante->update($datosActualizar);
                    $this->numRegistrosActualizados++;
                    
                    // Trackear comuna si viene en los datos
                    $idComuna = $datosActualizar['comuna'] ?? null;
                    $nombreComuna = $this->obtenerNombreComuna($idComuna);
                    if ($idComuna && !isset($this->comunasProcesadas[$idComuna])) {
                        $this->comunasProcesadas[$idComuna] = $nombreComuna;
                    }
                    
                    $this->votantesActualizados[] = [
                        'identificacion' => $identificacion,
                        'nombre' => $votante->nombre,
                        'comuna' => $nombreComuna,
                        'id_comuna' => $idComuna,
                    ];
                    
                } else {
                    
                }
            } catch (\Exception $e) {
                $this->numErrores++;
                $this->erroresDetallados[] = [
                    'fila' => $filaExcel,
                    'identificacion' => $identificacion,
                    'nombre' => $nombre,
                    'error' => $e->getMessage()
                ];
                $this->votantesNoActualizados[] = [
                    'fila' => $filaExcel,
                    'identificacion' => $identificacion,
                    'nombre' => $nombre,
                    'error' => 'Error: ' . $e->getMessage()
                ];
                
            }

            $filaExcel++;
            $index++;
        }
    }

    /**
     * Normalizar los datos del row para manejar tanto índices como encabezados
     */
    private function normalizarDatos($row)
    {
        $rowData = [];
        
        // Si el row tiene keys numéricas (acceso por índice)
        if ($row->keys()->first() !== null && is_numeric($row->keys()->first())) {
            foreach ($row as $key => $value) {
                $rowData[$key] = $value;
            }
        } else {
            // Acceso por encabezado - convertir a índices numéricos según el orden especificado
            $keys = $row->keys()->toArray();
            foreach ($keys as $index => $key) {
                $rowData[$index] = $row[$key];
            }
        }
        
        return $rowData;
    }

    /**
     * Obtener la identificación del row (índice 2)
     */
    private function obtenerIdentificacion($rowData)
    {
        // Por índice numérico
        if (isset($rowData[2]) && !empty($rowData[2])) {
            return $rowData[2];
        }
        
        // Por nombre de columna
        $nombres = ['numero identificacion', 'numero de identificacion', 'identificacion'];
        foreach ($nombres as $nombre) {
            $nombreLower = strtolower(trim($nombre));
            if (isset($rowData[$nombreLower]) && !empty($rowData[$nombreLower])) {
                return $rowData[$nombreLower];
            }
        }
        
        return null;
    }

    /**
     * Obtener el nombre del row (índice 0)
     */
    private function obtenerNombre($rowData)
    {
        // Por índice numérico
        if (isset($rowData[0]) && !empty($rowData[0])) {
            return $rowData[0];
        }
        
        // Por nombre de columna
        $nombres = ['nombre', 'nombres'];
        foreach ($nombres as $nombre) {
            $nombreLower = strtolower(trim($nombre));
            if (isset($rowData[$nombreLower]) && !empty($rowData[$nombreLower])) {
                return $rowData[$nombreLower];
            }
        }
        
        return null;
    }

    /**
     * Preparar los datos para actualizar desde el Excel
     */
    private function prepararDatos($rowData, $rowOriginal)
    {
        $datos = [];

        // [0] nombre
        if (isset($rowData[0]) && trim($rowData[0]) !== '') {
            $datos['nombre'] = trim($rowData[0]);
        }

        // [1] tipo documento
        if (isset($rowData[1]) && !empty($rowData[1])) {
            $datos['tipo_documento'] = trim($rowData[1]);
        }

        // [3] email (opcional)
        if (isset($rowData[3]) && !empty($rowData[3])) {
            $datos['email'] = trim(strtolower($rowData[3]));
        }

        // [4] fecha nacimiento (formato: dd/mm/yyyy)
        if (isset($rowData[4]) && !empty($rowData[4])) {
            $fechaFormateada = $this->formatearFecha($rowData[4]);
            if ($fechaFormateada) {
                $datos['nacimiento'] = $fechaFormateada;
            }
        }

        // [5] Genero
        if (isset($rowData[5]) && !empty($rowData[5])) {
            $datos['genero'] = trim($rowData[5]);
        }

        // [6] Grupo poblacional (etnia)
        if (isset($rowData[6]) && !empty($rowData[6])) {
            $datos['etnia'] = trim($rowData[6]);
        }

        // [7] condicion
        if (isset($rowData[7]) && !empty($rowData[7])) {
            $datos['condicion'] = trim($rowData[7]);
        }

        // [8] numero telefono (opcional) - celular
        if (isset($rowData[8]) && !empty($rowData[8])) {
            $datos['celular'] = trim($rowData[8]);
        }

        // [9] direccion
        if (isset($rowData[9]) && !empty($rowData[9])) {
            $datos['direccion'] = trim($rowData[9]);
        }

        // [10] barrio
        if (isset($rowData[10]) && !empty($rowData[10])) {
            $datos['barrio'] = trim($rowData[10]);
        }

        // [11] comuna (ya viene con el ID)
        if (isset($rowData[11]) && $rowData[11] !== null && $rowData[11] !== '') {
            $datos['comuna'] = trim($rowData[11]);
        }

        return $datos;
    }

    /**
     * Formatear fecha de dd/mm/yyyy a formato MySQL (yyyy-mm-dd)
     */
    private function formatearFecha($fecha)
    {
        if (empty($fecha)) {
            return null;
        }

        // Si ya es un objeto Carbon (Excel lo parsea automáticamente)
        if ($fecha instanceof Carbon) {
            return $fecha->format('Y-m-d');
        }

        // Si es una cadena
        $fecha = trim($fecha);

        // Intentar diferentes formatos
        $formatos = [
            'd/m/Y',      // 23/10/1976
            'd-m-Y',      // 23-10-1976
            'd.m.Y',      // 23.10.1976
            'Y-m-d',      // 1976-10-23 (ya formato MySQL)
            'm/d/Y',      // 10/23/1976 (formato americano)
        ];

        foreach ($formatos as $formato) {
            try {
                $carbonFecha = Carbon::createFromFormat($formato, $fecha);
                if ($carbonFecha && $carbonFecha->year >= 1900 && $carbonFecha->year <= date('Y')) {
                    return $carbonFecha->format('Y-m-d');
                }
            } catch (\Exception $e) {
                // Continuar con el siguiente formato
            }
        }

        // Si no se puede parsear, registrar warning y retornar null
        return null;
    }

    public function getNumRegistrosActualizados()
    {
        return $this->numRegistrosActualizados;
    }

    public function getNumNoEncontrados()
    {
        return $this->numNoEncontrados;
    }

    public function getNumErrores()
    {
        return $this->numErrores;
    }

    public function getErroresDetallados()
    {
        return $this->erroresDetallados;
    }

    public function getVotantesActualizados()
    {
        return $this->votantesActualizados;
    }

    public function getVotantesNoActualizados()
    {
        return $this->votantesNoActualizados;
    }

    public function getTotalRegistros()
    {
        return $this->totalRegistros;
    }

    /**
     * Obtener resumen de la importación
     */
    public function getResumen()
    {
        return [
            'totalRegistros' => $this->totalRegistros,
            'actualizados' => $this->numRegistrosActualizados,
            'noEncontrados' => $this->numNoEncontrados,
            'errores' => $this->numErrores,
            'votantesActualizados' => $this->votantesActualizados,
            'votantesNoActualizados' => $this->votantesNoActualizados,
            'fechaProceso' => now()->format('d/m/Y H:i:s'),
            'nombreEvento' => $this->nombreEvento,
            'comunasProcesadas' => $this->comunasProcesadas,
        ];
    }

    /**
     * Obtener el nombre de la comuna desde parametros_detalle
     */
    private function obtenerNombreComuna($idComuna)
    {
        if (empty($idComuna)) {
            return 'Sin especificar';
        }
        
        $comuna = ParametrosDetalle::find($idComuna);
        return $comuna ? $comuna->detalle : 'Comuna ID ' . $idComuna;
    }
}
