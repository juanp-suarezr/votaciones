<!-- filepath: resources/views/consulta_certificado.blade.php -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Consulta de Certificado Canino</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; }
        .card { max-width: 500px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #ccc; padding: 30px; }
        .card h2 { color: #1e3a8a; }
        .info { margin: 20px 0; }
        .info p { margin: 8px 0; }
    </style>
</head>
<body>
    <div class="card">
        <h2>Certificado de Registro Canino</h2>
        <div class="info">
            <p><strong>Nombre del canino:</strong> {{ $canino->nombre }}</p>
            <p><strong>Raza:</strong> {{ $canino->raza }}</p>
            <p><strong>Propietario:</strong> {{ $canino->user->informacionUsuario->nombre_completo ?? 'N/A' }}</p>
            <p><strong>Número de documento:</strong> {{ $canino->user->informacionUsuario->numero_documento ?? 'N/A' }}</p>
            <p><strong>Estado:</strong> {{ $canino->estado ?? 'N/A' }}</p>
            <p><strong>Válido hasta:</strong>
                @php
                    use Carbon\Carbon;
                    $fechaVencimiento = isset($canino->certificado['fecha_vencimiento'])
                        ? Carbon::parse($canino->certificado['fecha_vencimiento'])
                        : null;
                @endphp
                {{ $fechaVencimiento ? $fechaVencimiento->format('d/m/Y') : 'N/A' }}
            </p>
        </div>
        <p style="color: #888; font-size: 13px;">Si tienes dudas sobre este registro, comunícate con la Alcaldía de Pereira.</p>
    </div>
</body>
</html>
