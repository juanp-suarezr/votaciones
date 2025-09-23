<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecimiento de contraseña</title>
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
            <p>Hola,</p>
            <p>Estás recibiendo este email porque se ha solicitado un cambio de contraseña para tu cuenta.</p>
            <a href="{{ url('/reset-password/token=' . $token . '?email=' . urlencode($email)). '&isPPT=1' }}" target="_blank" style="background-color: #007bff; color: white; padding: 4px 8px; text-decoration: none; border-radius: 5px;">
                Restablecer contraseña
            </a>
            <p>Este enlace para restablecer la contraseña caduca en 60 minutos.
                <br>
                Si no has solicitado un cambio de contraseña, puedes ignorar o eliminar este e-mail.
            </p>
            <p>Saludos,</p>
            <p>

                Si tiene problemas para hacer clic en el botón "Restablecer contraseña", copie y pegue la siguiente URL en su navegador web:
                {{ url('/reset-password/token=' . $token . '?email=' . urlencode($email). '&isPPT=1') }}
            </p>
        </div>
        <div class="footer">
            &copy; {{ date('Y') }} Alcaldía de Pereira. Todos los derechos reservados.
        </div>
    </div>
</body>

</html>
