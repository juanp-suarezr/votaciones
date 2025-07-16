<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Consulta de Certificado electoral</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
        }

        .card {
            max-width: 500px;
            margin: 40px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 2px 8px #ccc;
            padding: 30px;
        }

        .card h2 {
            color: #1e3a8a;
        }

        .info {
            margin: 20px 0;
        }

        .info p {
            margin: 8px 0;
        }
    </style>
</head>

<body>
    <div class="card">
        <h2>Certificado electoral {{ $evento->nombre }} {{ $annio }}</h2>
        <div class="info">

            <p><strong>Nombre:</strong> {{ $voto->votante->nombre }}</p>
            <p><strong>identificación:</strong> {{ $voto->votante->tipo_documento }} -
                {{ $voto->votante->identificacion }}</p>
            <p><strong>Comuna/Corregimiento:</strong> {{ $comuna }}</p>
            <p><strong>Modalidad:</strong> {{ $voto->isVirtual ? 'Virtual' : 'Presencial' }}</p>

        </div>
        <p style="color: #888; font-size: 13px;">Si tienes dudas sobre este registro, comunícate con la Alcaldía de
            Pereira, Secretaría de planeación.</p>
    </div>
</body>

</html>
