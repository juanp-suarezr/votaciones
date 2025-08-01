<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Consulta de Certificado Canino</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@400;700&display=swap');

        body {
            font-family: 'Montserrat', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }

        .card {
            width: 100%;
            max-width: 800px;
            margin: 10px auto;
            background-color: #fff;
            border-radius: 18px;
            box-shadow: 0 6px 18px rgba(30, 58, 138, 0.10);
            overflow: hidden;
            border: 2px solid #d1d5db;
            position: relative;
        }

        /* Fondo con logo de la alcaldía */
        .card::before {
            content: "";
            position: absolute;
            top: 20%;
            left: 50%;
            width: 340px;
            height: 340px;
            transform: translate(-50%, -50%);
            background-image: url('{{ public_path('assets/img/escudo.png') }}');
            background-repeat: no-repeat;
            background-position: center;
            background-size: contain;
            opacity: 0.06;
            z-index: 0;
            pointer-events: none;
        }

        .header {
            background-color: #C20E1A;
            color: white;
            padding: 15px 0;
            text-align: center;
            position: relative;
            z-index: 1;
            letter-spacing: 1px;
        }

        .header h1 {
            font-size: 26px;
            margin: 0;
            font-weight: 700;
            letter-spacing: 1px;
        }

        .content {
            padding: 28px 24px 18px 24px;
            position: relative;
            z-index: 1;
        }

        .content table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 8px;
        }

        .content td {
            vertical-align: top;
            padding: 10px 12px;
        }

        .image-container img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 12px;
            border: 3px solid #1e3a8a;
            display: block;
            margin: 0 auto;
            background: #f3f4f6;
        }

        .details p {
            margin: 7px 0;
            font-size: 16px;
            color: #22223b;
        }

        .details p strong {
            color: #1e3a8a;
            font-weight: 700;
        }

        .footer {
            background-color: #f3f4f6;
            padding: 0px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            position: relative;
            z-index: 1;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>

<body>
    <div class="card">
        <!-- Header -->
        <div class="header">
            <h1>Certificado de Participación {{$evento->nombre}} {{$annio_actual}}</h1>
        </div>

        <!-- Content -->
        <div class="content">
            <table>
                <tr>

                    <!-- Details -->
                    <td class="details">
                        <h3>Pereira, {{ $created_at }}</h3>
                        <p><strong>Nombre:</strong> {{ $votante->nombre }}</p>
                        <p><strong>identificación:</strong> {{ $votante->tipo_documento }} - {{ $votante->identificacion }}</p>
                        <p><strong>Comuna/Corregimiento:</strong> {{$comuna }}</p>
                        <p><strong>Modalidad:</strong> {{ $voto->isVirtual ? 'Virtual' : 'Virtual-TIC' }}</p>
                    </td>
                    <!-- QR -->
                    <td style="width:150px; text-align:center; vertical-align:top;">
                        <div>
                            <img src="{{ $qr }}" width="90" height="90" alt="QR">
                            <div style="font-size:10px; color:#888; margin-top:5px;">Escanea para validar</div>
                        </div>
                    </td>

                </tr>
            </table>

            <!-- Firma y logo después del table -->
            <div style="margin-top: 10px;">
                <table>
                    <tr>
                        <!-- Logo a la izquierda -->
                        <td style="text-align: left;">

                            <img src="{{ public_path('assets/img/logo.webp') }}" height="60">
                        </td>

                        <!-- Firma a la derecha -->
                        <td style="text-align: right;">
                            <img src="{{ public_path('storage/uploads/delegado/' . $delegado->firma) }}"
                                alt="Foto de firma">
                            <p style="margin-top:-10px;">
                                __________________<br>
                                <span style="font-weight: bold;">{{ $delegado->nombre }}</span><br>
                                {{ $delegado->cargo }}<br>
                                Alcaldía de Pereira
                            </p>
                        </td>


                    </tr>
                </table>

            </div>

        </div>

        <!-- Footer -->
        <div class="footer">
            <p>Certificado validado por alcaldía de Pereria y secretaría de planeación</p>

        </div>
    </div>
</body>

</html>
