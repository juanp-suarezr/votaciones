<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $config['codigo'] }} - {{ $config['titulo'] }}</title>
    <style>
        @page {
            margin: 28mm 18mm 24mm 18mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1f2937;
            font-size: 11pt;
            line-height: 1.5;
            margin: 0;
            padding: 0;
        }

        /* Encabezado fijo */
        .header {
            width: 100%;
            border-bottom: 2px solid #0284c7;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }

        .header table {
            width: 100%;
            border-collapse: collapse;
        }

        .logo-col {
            width: 32%;
            vertical-align: middle;
            text-align: left;
        }

        .logo-img {
            max-width: 160px;
            max-height: 60px;
        }

        .title-col {
            width: 68%;
            vertical-align: middle;
            text-align: right;
        }

        .header-title {
            font-size: 14pt;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-subtitle {
            font-size: 9pt;
            color: #475569;
            margin: 2px 0 0 0;
            font-weight: 600;
        }

        .badge-codigo {
            display: inline-block;
            background-color: #0284c7;
            color: #ffffff;
            padding: 3px 8px;
            font-size: 8pt;
            font-weight: bold;
            border-radius: 4px;
            margin-top: 4px;
        }

        /* Título del documento */
        .doc-title-container {
            text-align: center;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-left: 4px solid #0284c7;
            padding: 12px 14px;
            margin-bottom: 18px;
            border-radius: 4px;
        }

        .doc-title {
            font-size: 12pt;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 4px 0;
            text-transform: uppercase;
        }

        .doc-subtitle {
            font-size: 9.5pt;
            color: #0284c7;
            font-weight: 700;
            margin: 0;
            text-transform: uppercase;
        }

        /* Tabla de metadatos */
        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
            font-size: 9.5pt;
        }

        .meta-table td {
            padding: 5px 8px;
            border: 1px solid #e2e8f0;
        }

        .meta-label {
            background-color: #f1f5f9;
            font-weight: bold;
            color: #334155;
            width: 25%;
        }

        .meta-value {
            color: #0f172a;
            width: 75%;
        }

        /* Contenido y párrafos */
        .section-title {
            font-size: 10.5pt;
            font-weight: bold;
            color: #0f172a;
            border-bottom: 1.5px solid #cbd5e1;
            padding-bottom: 3px;
            margin-top: 16px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }

        p {
            margin: 0 0 10px 0;
            text-align: justify;
        }

        /* Cajas de resumen / estadísticas */
        .kpi-container {
            width: 100%;
            margin-bottom: 14px;
        }

        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            text-align: center;
        }

        .kpi-box {
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            padding: 8px 6px;
            border-radius: 4px;
        }

        .kpi-num {
            font-size: 14pt;
            font-weight: bold;
            color: #0284c7;
            margin-bottom: 2px;
        }

        .kpi-txt {
            font-size: 8pt;
            color: #475569;
            text-transform: uppercase;
            font-weight: 600;
        }

        /* Tablas de datos */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 8.5pt;
            margin-top: 10px;
            margin-bottom: 16px;
        }

        .data-table th {
            background-color: #0f172a;
            color: #ffffff;
            padding: 6px 6px;
            font-weight: bold;
            text-align: left;
            border: 1px solid #0f172a;
        }

        .data-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
        }

        .data-table tr:nth-child(even) td {
            background-color: #f8fafc;
        }

        .badge-estado {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }

        .badge-aprobado {
            background-color: #dcfce7;
            color: #166534;
        }

        .badge-rechazado {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .badge-pendiente {
            background-color: #fef3c7;
            color: #92400e;
        }

        /* Bloque de firmas */
        .firmas-section {
            margin-top: 28px;
            page-break-inside: avoid;
        }

        .firmas-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .firma-col {
            width: 50%;
            vertical-align: bottom;
            text-align: center;
            padding: 10px 18px;
        }

        .firma-imagen-wrapper {
            height: 70px;
            text-align: center;
            margin-bottom: 4px;
        }

        .firma-img {
            max-height: 65px;
            max-width: 180px;
        }

        .firma-linea {
            border-top: 1.5px solid #334155;
            margin: 0 auto 5px auto;
            width: 85%;
        }

        .firma-nombre {
            font-size: 9pt;
            font-weight: bold;
            color: #0f172a;
            margin: 0;
        }

        .firma-cargo {
            font-size: 8pt;
            color: #475569;
            margin: 0;
            text-transform: uppercase;
        }

        .firma-id {
            font-size: 8pt;
            color: #64748b;
            margin: 0;
        }

        /* Footer */
        .footer {
            position: fixed;
            bottom: -15mm;
            left: 0;
            right: 0;
            border-top: 1px solid #e2e8f0;
            padding-top: 6px;
            font-size: 8pt;
            color: #64748b;
            text-align: center;
        }
    </style>
</head>
<body>

    <!-- Encabezado Oficial -->
    <div class="header">
        <table>
            <tr>
                <td class="logo-col">
                    @if(!empty($logo_base64))
                        <img src="{{ $logo_base64 }}" class="logo-img" alt="Alcaldía de Pereira">
                    @else
                        <strong style="color: #0284c7; font-size: 13pt;">ALCALDÍA DE PEREIRA</strong>
                    @endif
                </td>
                <td class="title-col">
                    <h1 class="header-title">Alcaldía de Pereira</h1>
                    <div class="header-subtitle">
                        {{ $dependencia_evento ?? 'Secretaría / Dependencia Responsable' }}
                    </div>
                    <span class="badge-codigo">{{ $config['codigo'] }}</span>
                </td>
            </tr>
        </table>
    </div>

    <!-- Título Principal del Acta -->
    <div class="doc-title-container">
        <div class="doc-title">{{ $config['titulo'] }}</div>
        <div class="doc-subtitle">{{ $config['subtitulo'] }}</div>
    </div>

    <!-- Información General del Proceso -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Entidad Oficial:</td>
            <td class="meta-value"><strong>ALCALDÍA DE PEREIRA</strong> - Risaralda, Colombia</td>
        </tr>
        <tr>
            <td class="meta-label">Evento Electoral:</td>
            <td class="meta-value">
                [ID: {{ $evento->id ?? 25 }}] <strong>{{ $evento->nombre ?? 'Evento Institucional' }}</strong>
            </td>
        </tr>
        <tr>
            <td class="meta-label">Dependencia Responsable:</td>
            <td class="meta-value">{{ $dependencia_evento ?? 'Alcaldía de Pereira' }}</td>
        </tr>
        <tr>
            <td class="meta-label">Fecha y Hora Oficial:</td>
            <td class="meta-value">
                <strong>{{ \Carbon\Carbon::parse($config['fecha_acta'])->translatedFormat('d \d\e F \d\e Y \a \l\a\s h:i A') }}</strong>
            </td>
        </tr>
        <tr>
            <td class="meta-label">Fuente de Fechas:</td>
            <td class="meta-value">{{ $config['origen_fecha'] }}</td>
        </tr>
        <tr>
            <td class="meta-label">Plataforma Tecnológica:</td>
            <td class="meta-value">Sistema Oficial de Votación Virtual <strong>VOTA YA</strong></td>
        </tr>
    </table>

    <!-- Objeto del Acta -->
    <div class="section-title">1. OBJETO Y CONSIDERACIONES LEGALES</div>
    <p>{{ $config['objeto'] }}</p>
    <p>
        El presente proceso se desarrolla en estricto cumplimiento de la normatividad legal e institucional vigente, garantizando los principios constitucionales de transparencia, publicidad, imparcialidad, voto libre y secreto, y seguridad técnica del sufragio.
    </p>

    <!-- ------------------------------------------------------------- -->
    <!-- CONTENIDO ESPECÍFICO SEGÚN EL TIPO DE ACTA                     -->
    <!-- ------------------------------------------------------------- -->

    @if($tipo === 'inscripcion_apertura')
        <div class="section-title">2. CONDICIONES Y PARÁMETROS DE APERTURA</div>
        <p>
            En la fecha y hora señaladas en el encabezado, el Administrador del Sistema y los Secretarios Electorales verifican la habilitación formal del formulario público digital para la recepción de postulaciones e inscripciones para {{ $evento->nombre }}.
        </p>

        <table class="meta-table">
            <tr>
                <td class="meta-label">Inicio Inscripciones:</td>
                <td class="meta-value"><strong>{{ \Carbon\Carbon::parse($fechas['inscripcion_inicio'])->translatedFormat('d \d\e F \d\e Y - h:i A') }}</strong></td>
            </tr>
            <tr>
                <td class="meta-label">Cierre Previsto:</td>
                <td class="meta-value"><strong>{{ \Carbon\Carbon::parse($fechas['inscripcion_fin'])->translatedFormat('d \d\e F \d\e Y - h:i A') }}</strong></td>
            </tr>
            <tr>
                <td class="meta-label">Ruta Digital:</td>
                <td class="meta-value"><code>/{{ $fechas['inscripcion_ruta'] }}</code></td>
            </tr>
            <tr>
                <td class="meta-label">Requisitos de Postulación:</td>
                <td class="meta-value">
                    1. Acreditar condición de funcionario habilitado en el censo oficial.<br>
                    2. Cargar fotocopia legible del documento de identidad.<br>
                    3. Cargar certificado vigente de antecedentes de la Procuraduría.<br>
                    4. Foto digital formal para el tarjetón virtual.
                </td>
            </tr>
        </table>

        <div class="section-title">3. CONSTANCIA DE APERTURA</div>
        <p>
            Se deja expresa constancia de que los servidores se encuentran sincronizados y dispuestos para la radicación ininterrumpida de solicitudes digitales, asignando un código de radicado único, sellado de tiempo criptográfico y registro de auditoría de cada solicitud recibida.
        </p>

    @elseif($tipo === 'inscripcion_cierre')
        <div class="section-title">2. CONSOLIDADO ESTADÍSTICO DE POSTULACIONES</div>
        <p>
            Cumplido el término legal y reglamentario establecido en el cronograma oficial del bloqueo de rutas, se procede al cierre definitivo del formulario de postulaciones, arrojando el siguiente balance:
        </p>

        <div class="kpi-container">
            <table class="kpi-table">
                <tr>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-num">{{ $totales['total_postulaciones'] }}</div>
                            <div class="kpi-txt">Total Radicadas</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-num" style="color: #16a34a;">{{ $totales['total_aprobados'] }}</div>
                            <div class="kpi-txt">Aprobadas / Habilitadas</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-num" style="color: #dc2626;">{{ $totales['total_rechazados'] }}</div>
                            <div class="kpi-txt">Rechazadas</div>
                        </div>
                    </td>
                    <td style="width: 25%;">
                        <div class="kpi-box">
                            <div class="kpi-num" style="color: #d97706;">{{ $totales['total_pendientes'] }}</div>
                            <div class="kpi-txt">En Validación</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <p style="font-size: 9pt; color: #475569; margin-top: 4px;">
            * Desglose de Candidatos Habilitados por Planta: <strong>Planta Central: {{ $totales['aprobados_planta_central'] }}</strong> | <strong>SGP: {{ $totales['aprobados_sgp'] }}</strong>.
        </p>

        <div class="section-title">3. LISTADO OFICIAL DE ASPIRANTES RADICADOS Y CANDIDATOS HABILITADOS</div>
        @if($postulaciones->count() > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 14%;">Radicado</th>
                        <th style="width: 28%;">Nombre del Funcionario</th>
                        <th style="width: 14%;">Identificación</th>
                        <th style="width: 16%;">Planta</th>
                        <th style="width: 16%;">Dependencia</th>
                        <th style="width: 12%;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($postulaciones as $p)
                        <tr>
                            <td><strong>{{ $p->radicado ?? ('RAD-CP-' . str_pad($p->id, 4, '0', STR_PAD_LEFT)) }}</strong></td>
                            <td>{{ $p->nombre }}</td>
                            <td>{{ $p->identificacion }}</td>
                            <td>{{ $p->tipo_planta ?? 'N/A' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($p->dependencia ?? 'N/A', 22) }}</td>
                            <td>
                                @if($p->estado === 'aprobado')
                                    <span class="badge-estado badge-aprobado">Habilitado</span>
                                @elseif($p->estado === 'rechazado')
                                    <span class="badge-estado badge-rechazado">Rechazado</span>
                                @else
                                    <span class="badge-estado badge-pendiente">Pendiente</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="font-style: italic; color: #64748b;">No se registraron postulaciones durante el período habilitado.</p>
        @endif

        <div class="section-title">4. CONSTANCIA DE CIERRE Y BLOQUEO DE PLATAFORMA</div>
        <p>
            Se certifica que la ruta digital fue cerrada oportunamente impidiendo la radicación de solicitudes extemporáneas, quedando en firme el listado de candidatos habilitados para conformar el tarjetón electoral.
        </p>

    @elseif($tipo === 'votacion_apertura')
        <div class="section-title">2. CERTIFICACIÓN DE PUESTA A CERO DE LA URNA ELECTRÓNICA</div>
        <p>
            Los suscritos secretarios y supervisores del evento certifican que a la hora exacta de inicio de la jornada de votación, se efectuó la revisión y validación de las tablas de almacenamiento de sufragios en la base de datos de la plataforma VOTA YA, verificándose lo siguiente:
        </p>

        <div class="kpi-container">
            <table class="kpi-table">
                <tr>
                    <td style="width: 50%;">
                        <div class="kpi-box">
                            <div class="kpi-num" style="color: #16a34a;">0 VOTOS</div>
                            <div class="kpi-txt">Urna Electrónica en Cero</div>
                        </div>
                    </td>
                    <td style="width: 50%;">
                        <div class="kpi-box">
                            <div class="kpi-num">{{ $totales['censo_votantes'] }}</div>
                            <div class="kpi-txt">Votantes en Censo Habilitado</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-title">3. OPCIONES Y CANDIDATOS HABILITADOS EN EL TARJETÓN VIRTUAL</div>
        <p>
            Se verificó la carga correcta de las opciones electorales debidamente configuradas en la plataforma para este evento:
        </p>

        @if(count($resultados_candidatos) > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">No.</th>
                        <th style="width: 36%;">Opción / Candidato / Proyecto</th>
                        <th style="width: 18%;">Identificador</th>
                        <th style="width: 18%;">Categoría</th>
                        <th style="width: 20%;">Dependencia / Detalle</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resultados_candidatos as $idx => $cand)
                        <tr>
                            <td>{{ $idx + 1 }}</td>
                            <td><strong>{{ $cand['nombre'] }}</strong></td>
                            <td>{{ $cand['identificacion'] }}</td>
                            <td>{{ $cand['subtitulo'] ?? 'N/A' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($cand['dependencia'] ?? 'N/A', 25) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="font-style: italic; color: #64748b;">No hay opciones o candidatos registrados para este evento.</p>
        @endif

        <div class="section-title">4. CANALES Y SEGURIDAD DEL VOTO</div>
        <p>
            Se declara abierta formalmente la votación virtual para que todos los ciudadanos y funcionarios habilitados ejerzan su derecho al sufragio secreto, libre y seguro a través de los mecanismos criptográficos de la plataforma VOTA YA.
        </p>

    @elseif($tipo === 'votacion_cierre')
        <div class="section-title">2. CONSOLIDADO DE PARTICIPACIÓN ELECTORAL</div>
        <p>
            Llegada la hora reglamentaria de finalización de la jornada electoral, el sistema suspendió automáticamente la emisión de nuevos sufragios. Se consolida el nivel de participación registrado:
        </p>

        <div class="kpi-container">
            <table class="kpi-table">
                <tr>
                    <td style="width: 33.3%;">
                        <div class="kpi-box">
                            <div class="kpi-num">{{ $totales['censo_votantes'] }}</div>
                            <div class="kpi-txt">Censo Habilitado</div>
                        </div>
                    </td>
                    <td style="width: 33.3%;">
                        <div class="kpi-box">
                            <div class="kpi-num" style="color: #0284c7;">{{ $totales['total_votos'] }}</div>
                            <div class="kpi-txt">Votos Emitidos</div>
                        </div>
                    </td>
                    <td style="width: 33.3%;">
                        <div class="kpi-box">
                            <div class="kpi-num" style="color: #16a34a;">{{ $totales['porcentaje_participacion'] }}%</div>
                            <div class="kpi-txt">% Participación</div>
                        </div>
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-title">3. ESCRUTINIO FINAL Y RESULTADOS OFICIALES</div>
        <p>
            Efectuado el cómputo y discriminación informática de los sufragios depositados en la urna virtual, se obtuvieron los siguientes resultados oficiales:
        </p>

        @if(count($resultados_candidatos) > 0)
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width: 8%;">Pos.</th>
                        <th style="width: 34%;">Opción / Candidato / Proyecto</th>
                        <th style="width: 18%;">Categoría</th>
                        <th style="width: 22%;">Dependencia / Detalle</th>
                        <th style="width: 9%; text-align: right;">Votos</th>
                        <th style="width: 9%; text-align: right;">%</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($resultados_candidatos as $idx => $r)
                        <tr>
                            <td><strong>#{{ $idx + 1 }}</strong></td>
                            <td><strong>{{ $r['nombre'] }}</strong></td>
                            <td>{{ $r['subtitulo'] ?? 'N/A' }}</td>
                            <td>{{ \Illuminate\Support\Str::limit($r['dependencia'] ?? 'N/A', 25) }}</td>
                            <td style="text-align: right; font-weight: bold; color: #0284c7;">{{ $r['votos'] }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ $r['porcentaje'] }}%</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p style="font-style: italic; color: #64748b;">No se registraron votos o candidatos para este evento.</p>
        @endif

        <div class="section-title">4. CONSTANCIA DE TRANSPARENCIA Y DECLARATORIA</div>
        <p>
            Los Secretarios y Supervisores certifican que el proceso se surtió sin alteraciones técnicas ni vulneraciones en la integridad del almacenamiento seguro de sufragios, garantizando la fidelidad de los resultados electorales.
        </p>
    @endif

    <!-- Constancia Final de Cierre -->
    <p style="margin-top: 14px;">
        En constancia de lo actuado, se firma la presente acta en la ciudad de Pereira, a los <strong>{{ \Carbon\Carbon::parse($config['fecha_acta'])->translatedFormat('d \d\í\a\s \d\e\l \m\e\s \d\e F \d\e Y') }}</strong>, por el Secretario de las TIC y los secretarios o delegados oficiales del evento.
    </p>

    <!-- Bloque de Firmas -->
    <div class="firmas-section">
        @if(!empty($firmantes_pdf) && count($firmantes_pdf) > 0)
            <table class="firmas-table">
                <tr>
                    @foreach($firmantes_pdf as $index => $firmante)
                        <td class="firma-col">
                            <div class="firma-imagen-wrapper">
                                @if(!empty($firmante['firma_base64']))
                                    <img src="{{ $firmante['firma_base64'] }}" class="firma-img" alt="Firma">
                                @else
                                    <div style="height: 50px;"></div>
                                @endif
                            </div>
                            <div class="firma-linea"></div>
                            <div class="firma-nombre">{{ $firmante['nombre'] }}</div>
                            <div class="firma-cargo">{{ $firmante['cargo'] }}</div>
                            <div class="firma-id">{{ $firmante['identificacion'] }}</div>
                        </td>
                        @if(($index + 1) % 2 === 0 && ($index + 1) < count($firmantes_pdf))
                            </tr><tr>
                        @endif
                    @endforeach
                </tr>
            </table>
        @else
            <table class="firmas-table">
                <tr>
                    <td class="firma-col">
                        <div class="firma-imagen-wrapper" style="height: 50px;"></div>
                        <div class="firma-linea"></div>
                        <div class="firma-nombre">SECRETARIO DE LAS TIC</div>
                        <div class="firma-cargo">Secretario de las TIC</div>
                        <div class="firma-id">Alcaldía de Pereira</div>
                    </td>
                    <td class="firma-col">
                        <div class="firma-imagen-wrapper" style="height: 50px;"></div>
                        <div class="firma-linea"></div>
                        <div class="firma-nombre">SECRETARIO(A) / DELEGADO(A)</div>
                        <div class="firma-cargo">{{ $dependencia_evento ?? 'Alcaldía de Pereira' }}</div>
                        <div class="firma-id">Alcaldía de Pereira</div>
                    </td>
                </tr>
            </table>
        @endif
    </div>

    <!-- Pie de página fijo -->
    <div class="footer">
        Alcaldía de Pereira - Carrera 7 No. 18-55, Palacio Municipal | Plataforma VOTA YA | Generado el {{ $fecha_generacion }}
    </div>

</body>
</html>
