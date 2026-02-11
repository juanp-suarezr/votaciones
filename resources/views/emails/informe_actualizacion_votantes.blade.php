<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Actualización de Votantes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 800px;
            margin: 0 auto;
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 1px solid #dddddd;
        }

        .header img {
            max-width: 200px;
            height: auto;
            margin: 0 auto;
            display: block;
        }

        .header h1 {
            color: #333333;
            margin: 10px 0;
        }

        .resumen {
            background-color: #f9f9f9;
            border: 1px solid #dddddd;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
            text-align: center;
        }

        .resumen h2 {
            color: #333333;
            margin-top: 0;
        }

        .resumen .stats {
            display: flex;
            justify-content: space-around;
            flex-wrap: wrap;
            margin-top: 15px;
        }

        .resumen .stat-item {
            text-align: center;
            padding: 10px;
        }

        .resumen .stat-number {
            font-size: 24px;
            font-weight: bold;
        }

        .resumen .stat-label {
            font-size: 12px;
            color: #666666;
        }

        .stat-actualizados { color: #16a34a; }
        .stat-no-encontrados { color: #f59e0b; }
        .stat-errores { color: #dc2626; }
        .stat-total { color: #2563eb; }

        .section {
            margin: 20px 0;
        }

        .section h3 {
            color: #333333;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            font-size: 12px;
        }

        table th, table td {
            border: 1px solid #dddddd;
            padding: 8px;
            text-align: left;
        }

        table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .table-container {
            max-height: 300px;
            overflow-y: auto;
        }

        .no-actualizados {
            background-color: #fef2f2;
            border: 1px solid #fecaca;
        }

        .no-actualizados h3 {
            color: #dc2626;
            border-bottom: 2px solid #dc2626;
        }

        .actualizados {
            background-color: #f0fdf4;
            border: 1px solid #bbf7d0;
        }

        .actualizados h3 {
            color: #16a34a;
            border-bottom: 2px solid #16a34a;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #dddddd;
            color: #999999;
            font-size: 12px;
            margin-top: 30px;
        }

        .info-evento {
            background-color: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 20px;
            text-align: center;
            color: #1e40af;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="https://www.pereira.gov.co/info/pereira_se/media/bloque36.png" alt="Alcaldía de Pereira">
            <h1>Informe de Actualización de Información de Votantes</h1>
            <p>Proceso realizado el {{ $fechaProceso }}</p>
        </div>

        <div class="info-evento">
            📋 Solo se actualizaron votantes con relación al evento {{ $nombreEvento }}
        </div>

        <div class="resumen">
            <h2>Resumen del Proceso</h2>
            <div class="stats">
                <div class="stat-item">
                    <div class="stat-number stat-total">{{ $totalRegistros }}</div>
                    <div class="stat-label">Total Registros</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number stat-actualizados">{{ $actualizados }}</div>
                    <div class="stat-label">Actualizados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number stat-no-encontrados">{{ $noEncontrados }}</div>
                    <div class="stat-label">No Encontrados</div>
                </div>
                <div class="stat-item">
                    <div class="stat-number stat-errores">{{ $errores }}</div>
                    <div class="stat-label">Errores</div>
                </div>
            </div>
        </div>

        @if(isset($comunasProcesadas) && count($comunasProcesadas) > 0)
        <div class="section" style="background-color: #eff6ff; border: 1px solid #bfdbfe; border-radius: 5px; padding: 15px;">
            <h3 style="color: #1e40af; border-bottom: 2px solid #1e40af; padding-bottom: 5px;">🏘️ Comunas Procesadas</h3>
            <p>Las siguientes comunas fueron encontradas en la actualización:</p>
            <ul>
                @foreach($comunasProcesadas as $id => $nombre)
                <li><strong>ID {{ $id }}:</strong> {{ $nombre }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        @if(count($votantesNoActualizados) > 0)
        <div class="section no-actualizados">
            <h3>⚠️ Votantes NO Actualizados (Requieren Atención)</h3>
            <p>Estos son los registros que no se pudieron actualizar. Por favor verificar:</p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Fila Excel</th>
                            <th>Cédula</th>
                            <th>Nombre</th>
                            <th>Motivo</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($votantesNoActualizados as $votante)
                        <tr>
                            <td>{{ $votante['fila'] }}</td>
                            <td>{{ $votante['identificacion'] }}</td>
                            <td>{{ $votante['nombre'] ?? 'N/A' }}</td>
                            <td>{{ $votante['error'] }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        @if(count($votantesActualizados) > 0 && count($votantesActualizados) <= 50)
        <div class="section actualizados">
            <h3>✅ Votantes Actualizados Exitosamente</h3>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Cédula</th>
                            <th>Nombre</th>
                            <th>Comuna</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($votantesActualizados as $votante)
                        <tr>
                            <td>{{ $votante['identificacion'] }}</td>
                            <td>{{ $votante['nombre'] }}</td>
                            <td>{{ $votante['comuna'] ?? 'N/A' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @elseif(count($votantesActualizados) > 50)
        <div class="section actualizados">
            <h3>✅ Votantes Actualizados Exitosamente</h3>
            <p>Se actualizaron {{ count($votantesActualizados) }} registros exitosamente.</p>
            <p><em>(Lista completa no mostrada por exceso de registros. Todos los registros fueron actualizados correctamente.)</em></p>
        </div>
        @endif

        <div class="footer">
            &copy; {{ date('Y') }} Alcaldía de Pereira - Secretaría de Planeación<br>
            Plataforma de Votaciones - Presupuesto Participativo
        </div>
    </div>
</body>

</html>
