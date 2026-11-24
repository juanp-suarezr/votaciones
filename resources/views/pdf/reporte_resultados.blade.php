<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte de Resultados - {{ $evento->nombre }}</title>
    <style>
        @page {
            margin: 2.5cm 2cm 2cm 2cm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 10pt;
            color: #334155;
            line-height: 1.5;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .logo-cell {
            text-align: right;
            vertical-align: top;
        }
        .logo-img {
            max-width: 130px;
            max-height: 55px;
        }
        .title-doc {
            font-size: 12.5pt;
            font-weight: bold;
            color: #0f172a;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 4px;
            line-height: 1.3;
        }
        .event-name {
            font-size: 12.5pt;
            font-weight: bold;
            color: #C20E1A;
            text-align: center;
            text-transform: uppercase;
            margin-bottom: 18px;
            line-height: 1.3;
        }
        .sub-header {
            font-size: 10pt;
            font-weight: bold;
            color: #1e293b;
            margin-bottom: 8px;
        }
        .intro-text {
            text-align: justify;
            margin-bottom: 16px;
            font-size: 9.5pt;
            color: #334155;
        }
        .section-title {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f172a;
            margin-top: 14px;
            margin-bottom: 6px;
            border-bottom: 1px solid #e2e8f0;
            padding-bottom: 3px;
        }
        .subsection-title {
            font-size: 10pt;
            font-weight: bold;
            color: #004884;
            margin-top: 12px;
            margin-bottom: 4px;
        }
        ul {
            margin-top: 4px;
            margin-bottom: 12px;
            padding-left: 20px;
        }
        li {
            margin-bottom: 3px;
            font-size: 9.5pt;
        }
        .winner-tag {
            background-color: #dcfce7;
            color: #15803d;
            font-weight: bold;
            padding: 1px 6px;
            border-radius: 4px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 16px;
            font-size: 8.5pt;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #1e293b;
            font-weight: bold;
            text-align: left;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
            text-transform: uppercase;
            font-size: 8pt;
        }
        .data-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
            color: #334155;
        }
        .row-winner {
            background-color: #f0fdf4;
            font-weight: bold;
            color: #166534;
        }
        .row-blanco {
            background-color: #f8fafc;
            font-style: italic;
            color: #64748b;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .category-block {
            page-break-inside: avoid;
            margin-bottom: 14px;
        }
        .footer-sig {
            margin-top: 30px;
            font-size: 10pt;
            page-break-inside: avoid;
        }
        .page-break {
            page-break-before: always;
        }
    </style>
</head>
<body>

    <!-- Encabezado con Logo -->
    <table class="header-table">
        <tr>
            <td style="vertical-align: middle;">
                <span style="font-size: 8.5pt; color: #64748b; font-weight: bold; text-transform: uppercase;">
                    Alcaldía de Pereira
                </span>
            </td>
            <td class="logo-cell">
                @if(!empty($logo_base64))
                    <img src="{{ $logo_base64 }}" class="logo-img" alt="Alcaldía de Pereira">
                @endif
            </td>
        </tr>
    </table>

    <div class="title-doc">INFORME DE REPORTE DE RESULTADOS PROCESO DE ELECCIÓN VIRTUAL</div>
    <div class="event-name">{{ $evento->nombre }}</div>

    <div class="sub-header">Secretaría de Tecnologías de la Información y las Comunicaciones</div>

    <div class="intro-text">
        La Secretaría TIC de la alcaldía de Pereira, presenta el siguiente informe correspondiente al sistema de reporte y visualización de resultados del proceso de elección virtual para <strong>{{ $evento->nombre }}</strong>, llevado a cabo entre el {{ $fecha_inicio }} y el {{ $fecha_fin }}.
    </div>

    <!-- 1. Objetivo -->
    <div class="section-title">1. Objetivo</div>
    <div class="intro-text">
        Presentar el tablero de monitoreo y reporte de resultados implementado para el proceso de elección virtual, permitiendo la visualización del comportamiento electoral, participación y consolidación de los votos por cada estamento.
    </div>

    <!-- 2. Alcance del Sistema -->
    <div class="section-title">2. Alcance del Sistema</div>
    <ul>
        <li>Visualización de resultados transparentes.</li>
        <li>Monitoreo de participación y abstención por categorías.</li>
        <li>Consolidación automática de votos por tipo de cargo.</li>
        <li>Trazabilidad y transparencia del proceso electoral virtual.</li>
    </ul>

    <!-- 3. Dashboard General del Proceso Electoral -->
    <div class="section-title">3. Dashboard General del Proceso Electoral</div>
    <div class="intro-text">
        El sistema cuenta con un dashboard general que permite visualizar el estado consolidado del proceso. Se reporta un total de <strong>{{ number_format($total_habilitados, 0, ',', '.') }} votantes habilitados</strong>, de los cuales <strong>{{ number_format($total_votos, 0, ',', '.') }} realizaron el voto virtual</strong> ({{ $porcentaje_participacion }}% de participación).
    </div>

    <div style="font-weight: bold; margin-bottom: 4px; font-size: 9.5pt;">
        La distribución del total de votos según el tipo es la siguiente:
    </div>
    <ul>
        @foreach($distribucion_tipos as $dist)
            <li><strong>{{ $dist['tipo'] }}:</strong> {{ number_format($dist['votos'], 0, ',', '.') }} votos.</li>
        @endforeach
    </ul>

    <!-- Gráfica Dashboard General -->
    @if(!empty($grafico_dashboard['base64']))
        <div style="text-align: center; margin: 10px 0 16px 0;">
            <img src="{{ $grafico_dashboard['base64'] }}" style="width: 100%; max-width: 530px; border-radius: 6px;" alt="Dashboard General">
        </div>
    @endif

    <!-- 4. Resultados por Categorías -->
    <div class="section-title" style="margin-top: 18px;">4. Resultados por Categorías</div>
    <div class="intro-text">
        A continuación, se detalla el comportamiento de la votación por cada grupo de participación:
    </div>

    @foreach($categorias as $index => $cat)
        <div class="category-block">
            <div class="subsection-title">4.{{ $loop->iteration }}. {{ $cat['tipo'] }}</div>
            <div class="intro-text">
                En esta categoría se registró un nivel de votación de <strong>{{ number_format($cat['votos'], 0, ',', '.') }} votos realizados</strong> frente a una abstención de <strong>{{ number_format($cat['abstencion'], 0, ',', '.') }}</strong> ({{ $cat['porcentaje'] }}% de participación). Los resultados por candidato fueron:
            </div>

            <ul>
                @foreach($cat['candidatos'] as $cand)
                    <li>
                        @if(!empty($cand['es_ganador']))
                            <span class="winner-tag">
                                {{ $cand['nombre'] }}: {{ $cand['votos'] }} votos ({{ $cand['porcentaje'] }}%) — Ganador/a
                            </span>
                        @else
                            {{ $cand['nombre'] }}: {{ $cand['votos'] }} votos ({{ $cand['porcentaje'] }}%)
                        @endif
                    </li>
                @endforeach
            </ul>

            <!-- Gráfica de la Categoría (Barras + Dona) -->
            @if(!empty($cat['grafico']['base64']))
                <div style="text-align: center; margin: 8px 0 12px 0;">
                    <img src="{{ $cat['grafico']['base64'] }}" style="width: 100%; max-width: 530px; border-radius: 6px;" alt="Gráfico {{ $cat['tipo'] }}">
                </div>
            @endif

            <!-- Tabla de Resultados de la Categoría -->
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 50%;">Nombre</th>
                        <th style="width: 25%;">Identificación</th>
                        <th style="width: 13%;" class="text-center">Votos</th>
                        <th style="width: 12%;" class="text-center">Porcentaje</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($cat['candidatos'] as $cand)
                        <tr class="{{ !empty($cand['es_ganador']) ? 'row-winner' : ($cand['es_blanco'] ? 'row-blanco' : '') }}">
                            <td>
                                {{ $cand['nombre'] }}
                                @if(!empty($cand['es_ganador']))
                                    <span style="font-size: 7.5pt; color: #15803d; font-weight: bold;">(Ganador/a)</span>
                                @endif
                            </td>
                            <td>{{ $cand['identificacion'] }}</td>
                            <td class="text-center" style="font-weight: bold;">{{ $cand['votos'] }}</td>
                            <td class="text-center">{{ $cand['porcentaje'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endforeach

    <!-- 5. Características Técnicas -->
    <div class="category-block">
        <div class="section-title">5. Características Técnicas</div>
        <ul>
            <li>Dashboard web interactivo con actualización dinámica.</li>
            <li>Visualización gráfica de niveles de abstención y votos efectivos.</li>
            <li>Diseño responsive adaptable a diferentes dispositivos móviles.</li>
            <li>Procesamiento automatizado de consolidación de votos.</li>
        </ul>
    </div>

    <!-- 6. Beneficios Institucionales -->
    <div class="category-block">
        <div class="section-title">6. Beneficios Institucionales</div>
        <ul>
            <li><strong>Transparencia:</strong> Disponibilidad inmediata de información consolidada para los equipos administrativos y fortalecimiento de la transparencia del proceso electoral.</li>
            <li><strong>Eficiencia:</strong> Procesamiento automatizado que elimina errores manuales en el conteo.</li>
            <li><strong>Accesibilidad:</strong> Facilidad de consulta para todos los niveles de la entidad.</li>
        </ul>
    </div>

    <!-- Cierre / Firma -->
    <div class="footer-sig">
        <strong>Secretaría TIC</strong><br>
        Alcaldía de Pereira
    </div>

</body>
</html>
