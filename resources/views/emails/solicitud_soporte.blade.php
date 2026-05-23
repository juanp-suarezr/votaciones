<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitud de Soporte</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 100%;
            max-width: 600px;
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
            display: flex;
            justify-content: center;
            align-items: center;
            width: 60%;
            margin: 0 auto;
        }

        .header img {
            max-width: 100%;
            height: 100px;
            margin: 0 0px;
            background-size: contain;
            background-repeat: no-repeat;
        }

        .content {
            padding: 20px;
            text-align: left;
        }

        .content h2 {
            color: #c8102e;
            margin-bottom: 20px;
        }

        .content p {
            color: #333333;
            line-height: 1.6;
            margin: 8px 0;
        }

        .descripcion {
            white-space: pre-wrap;
            background-color: #f9f9f9;
            padding: 12px;
            border-left: 4px solid #c8102e;
            border-radius: 4px;
            font-family: inherit;
            margin-top: 8px;
        }

        .footer {
            text-align: center;
            padding-top: 20px;
            border-top: 1px solid #dddddd;
            color: #999999;
            font-size: 12px;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="https://www.pereira.gov.co/info/pereira_se/media/bloque36.png" alt="Alcaldía de Pereira">
        </div>
        <div class="content">
            <h2>📩 Nueva solicitud de soporte</h2>

            <p><strong>Nombre:</strong> {{ $nombre }}</p>
            <p><strong>Identificación:</strong> {{ $identificacion }}</p>
            <p><strong>Celular:</strong> {{ $celular }}</p>

            <p><strong>Descripción del problema:</strong></p>
            <div class="descripcion">{{ $descripcion }}</div>

            <p style="margin-top: 20px; font-size: 0.9em; color: #666;">Esta solicitud fue enviada desde el aplicativo de Votación Electrónica.</p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Alcaldía de Pereira. Todos los derechos reservados.
        </div>
    </div>
</body>

</html>
