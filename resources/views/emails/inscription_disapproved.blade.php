<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción Rechazada</title>
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
        }

        .header img {
            max-width: 100%;
            height: 150px;
            margin: 0 0px;
            background-size: contain;
            background-repeat: no-repeat;
        }

        .content {
            padding: 20px;
            text-align: center;
        }

        .content h1 {
            color: #333333;
        }

        .content p {
            color: #666666;
            line-height: 1.5;
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
            <h1>¡Hola, {{ $nombre }}!</h1>
            <p><b>La alcaldía de Pereira</b>, a traves de la <b>Secretaria de planeación</b> te informa que tu registro, ha sido <b
                    style="color: red">rechazado.</b></p>
            @if ($motivo != 'NA')
                <b>El motivo de la desaprobación es el siguiente:</b>
                <p>{{ $motivo }}</p>
            @endif

            @if ($motivo == 'Registro duplicado')
                <p>El registro es duplicado, por lo tanto este usuario será bloqueado por temas de seguridad</p>

            @elseif($intentos == 3)
                <p>Haz superado el numero máximo permitido para corregir el registro, su usuario sera bloqueado. Para votar puede asistir a la votación presencial en las fechas establecidas y puntos destinados</p>
            @else
                <p><b>numero de intento:</b> {{ $intentos }}
                <br>
                podrás ingresar
                al <a href="https://votaciones.servicios-alcaldiapereira.com/login" target="_blank" style="background-color: #007bff; color: white; padding: 4px 8px; text-decoration: none; border-radius: 5px;">software</a>
                 para corregir lo solicitado y cambiar su estado actual
                </p>
                <br>
                <em>Link de acceso: <a href="https://votaciones.servicios-alcaldiapereira.com/login" target="_blank" style="background-color: #007bff; color: white; padding: 4px 8px; text-decoration: none; border-radius: 5px;">https://votaciones.servicios-alcaldiapereira.com/login</a></em>
            @endif

        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Alcaldía de Pereira. Todos los derechos reservados.
        </div>
    </div>
</body>

</html>
