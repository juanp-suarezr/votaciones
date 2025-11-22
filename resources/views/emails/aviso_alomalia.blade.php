<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aclaración votaciones</title>
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

        .project-item {
            background-color: #f9f9f9;
            border: 1px solid #dddddd;
            border-radius: 5px;
            padding: 10px;
            margin: 10px 0;
            text-align: left;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="https://www.pereira.gov.co/info/pereira_se/media/bloque36.png" alt="Alcaldía de Pereira">
        </div>
        <div class="content">
            <h1>Cordial saludo, {{ $nombre }}!</h1>
            <p><b>La Alcaldía de Pereira</b>, a través de la <b>Secretaría de Planeación</b> informa que el correo que
                recibió recientemente sobre la apertura de las elecciones del Presupuesto Participativo fue enviado de
                manera accidental durante la realización de pruebas internas de la plataforma de votación electrónica.
            </p>
            <br><br>
            <p>Le confirmamos que las elecciones aún no han iniciado.</p>
            <br>
            <p>Cuando el proceso comience oficialmente, usted recibirá un correo con la información correcta, las fechas
                autorizadas y las instrucciones para participar mediante voto electrónico.</p>
            <br><br>
            <p>
                Agradecemos su comprensión y ofrecemos disculpas por cualquier confusión ocasionada.
            </p>
            <br>
            <p>
                Estamos trabajando para garantizar un proceso transparente, seguro y eficiente para todos los
                ciudadanos.
            </p>


            <br><br>
            Cordialmente,
            <br>
            Secretaría de Planeación
            <br>
            Alcaldía de Pereira

        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Alcaldía de Pereira. Todos los derechos reservados.
        </div>
    </div>
</body>

</html>
