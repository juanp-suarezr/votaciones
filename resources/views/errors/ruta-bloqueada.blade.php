<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $titulo ?? 'Sección Temporalmente Deshabilitada - Alcaldía de Pereira' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/png" href="{{ asset('assets/img/escudo.webp') }}">
    <style>
        /* ESTILOS CRÍTICOS AUTOCONTENIDOS - NO DEPENDEN DE CDN EXTERNO */
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background-color: #f1f5f9;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem;
        }
        .card-container {
            width: 100%;
            max-width: 520px;
            background: #ffffff;
            border-radius: 1.25rem;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            border: 1px solid #e2e8f0;
            text-align: center;
            position: relative;
        }
        /* Franja institucional Bicolor Alcaldía de Pereira (Rojo y Amarillo) */
        .header-stripe {
            height: 6px;
            width: 100%;
            background: linear-gradient(90deg, #C20E1A 0%, #C20E1A 50%, #FFB800 50%, #FFB800 100%);
        }
        .card-header-brand {
            padding: 1.5rem 1.5rem 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.75rem;
            border-bottom: 1px solid #f1f5f9;
        }
        .brand-logo {
            max-height: 42px;
            max-width: 160px;
            object-fit: contain;
        }
        .brand-divider {
            width: 1px;
            height: 28px;
            background-color: #cbd5e1;
        }
        .brand-text {
            font-size: 0.75rem;
            font-weight: 800;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #64748b;
            text-align: left;
            line-height: 1.2;
        }
        .brand-text strong {
            color: #C20E1A;
            display: block;
        }
        .card-body {
            padding: 2rem 1.75rem 2.25rem 1.75rem;
        }
        /* Contenedor del icono con dimensiones estrictas - NUNCA SALE GIGANTE */
        .icon-circle {
            width: 76px;
            height: 76px;
            max-width: 76px;
            max-height: 76px;
            min-width: 76px;
            min-height: 76px;
            margin: 0 auto 1.5rem auto;
            border-radius: 50%;
            background-color: #FEF2F2;
            border: 2px solid #FECACA;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #DC2626;
            box-shadow: 0 4px 6px -1px rgba(220, 38, 38, 0.1);
        }
        .icon-svg {
            width: 40px !important;
            height: 40px !important;
            max-width: 40px !important;
            max-height: 40px !important;
            min-width: 40px !important;
            min-height: 40px !important;
            display: block !important;
            stroke: #C20E1A;
            stroke-width: 2;
        }
        .badge-status {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            background-color: #FFF1F2;
            color: #9F1239;
            border: 1px solid #FFE4E6;
            border-radius: 9999px;
            font-size: 0.7rem;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 0.75rem;
        }
        .card-title {
            font-size: 1.35rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.3;
            margin-bottom: 0.75rem;
        }
        .card-message {
            font-size: 0.875rem;
            color: #475569;
            line-height: 1.6;
            margin-bottom: 1.5rem;
            padding: 0 0.5rem;
        }
        .info-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            border-radius: 0.75rem;
            padding: 0.85rem 1rem;
            margin-bottom: 1.75rem;
            font-size: 0.775rem;
            color: #334155;
            text-align: left;
        }
        .info-box-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 0.25rem 0;
        }
        .info-box-label {
            font-weight: 600;
            color: #64748b;
        }
        .info-box-val {
            font-weight: 700;
            color: #0f172a;
        }
        .btn-return {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            width: 100%;
            padding: 0.85rem 1.5rem;
            background-color: #C20E1A;
            color: #ffffff;
            font-size: 0.875rem;
            font-weight: 700;
            text-decoration: none;
            border-radius: 0.75rem;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 4px 6px -1px rgba(194, 14, 26, 0.25);
        }
        .btn-return:hover {
            background-color: #991b1b;
            box-shadow: 0 6px 12px -2px rgba(194, 14, 26, 0.35);
            transform: translateY(-1px);
        }
        .footer-note {
            margin-top: 1.25rem;
            font-size: 0.7rem;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <div class="card-container">
        <!-- Franja bicolor oficial de Pereira -->
        <div class="header-stripe"></div>

        <!-- Encabezado de Identidad Institucional -->
        <div class="card-header-brand">
            <img src="{{ asset('assets/img/logo.webp') }}" 
                 onerror="this.onerror=null; this.src='{{ asset('assets/img/logo.png') }}';" 
                 alt="Alcaldía de Pereira" 
                 class="brand-logo">
            <div class="brand-divider"></div>
            <div class="brand-text">
                <strong>Alcaldía de Pereira</strong>
                Plataforma Electoral VOTA YA
            </div>
        </div>

        <!-- Cuerpo del mensaje -->
        <div class="card-body">
            <!-- Icono con dimensiones fijas de 40px en círculo de 76px (imposible que se desborde o salga gigante) -->
            <div class="icon-circle">
                <svg class="icon-svg" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" stroke="currentColor" stroke-width="2"/>
                    <path d="M7 11V7a5 5 0 0 1 10 0v4" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                    <circle cx="12" cy="16" r="1.5" fill="currentColor"/>
                </svg>
            </div>

            <span class="badge-status">Acceso Restringido</span>

            <h1 class="card-title">{{ $titulo ?? 'Ruta Temporalmente Deshabilitada' }}</h1>
            
            <p class="card-message">
                {{ $mensaje ?? 'Esta sección se encuentra temporalmente inactiva de conformidad con el cronograma del proceso electoral. Por favor intente más tarde o consulte con el administrador del sistema.' }}
            </p>

            @if(isset($ruta) && ($ruta->fecha_inicio || $ruta->fecha_fin))
            <div class="info-box">
                @if($ruta->fecha_inicio)
                <div class="info-box-row">
                    <span class="info-box-label">Fecha de apertura:</span>
                    <span class="info-box-val">{{ $ruta->fecha_inicio }}</span>
                </div>
                @endif
                @if($ruta->fecha_fin)
                <div class="info-box-row">
                    <span class="info-box-label">Fecha de cierre:</span>
                    <span class="info-box-val">{{ $ruta->fecha_fin }}</span>
                </div>
                @endif
            </div>
            @endif

            <a href="{{ url('/') }}" class="btn-return">
                <svg style="width: 16px; height: 16px; display: inline-block;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                </svg>
                Volver al Inicio
            </a>

            <p class="footer-note">
                Secretaría de las TIC · Alcaldía de Pereira
            </p>
        </div>
    </div>
</body>
</html>
