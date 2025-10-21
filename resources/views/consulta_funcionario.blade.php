<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Ficha del Funcionario</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <style>
        :root {
            --blue: #1e3a8a;
            --muted: #6b7280;
            --card: #ffffff;
            --bg: #f3f4f6;
            --success: #10b981;
            --danger: #ef4444;
        }

        body {
            font-family: Inter, Arial, sans-serif;
            background: var(--bg) url('{{ asset('assets/img/escudo.png') }}') center center no-repeat;
            background-size: 350px auto;
            background-attachment: fixed;
            background-blend-mode: lighten;
            margin: 0;
            padding: 16px;
        }

        .container {
            max-width: 980px;
            margin: 0 auto;
        }

        /* --- HEADER --- */
        .header {
            text-align: center;
            margin-bottom: 28px;
            padding: 12px;
        }

        .header .logo {
            width: 100%;
            max-width: 320px;
            height: auto;
            object-fit: contain;
            margin-bottom: 14px;
            transition: all 0.3s ease;
        }

        .header h1 {
            font-size: 24px;
            color: var(--blue);
            margin: 0;
            line-height: 1.3;
        }

        .header .subtitle {
            font-size: 14px;
            color: var(--muted);
            margin-top: 6px;
            max-width: 90%;
            margin-left: auto;
            margin-right: auto;
        }

        /* --- CARD --- */
        .card {
            background: var(--card);
            border-radius: 12px;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
            padding: 20px;
            display: flex;
            gap: 20px;
            align-items: flex-start;
            flex-wrap: wrap;
        }

        .profile {
            min-width: 180px;
            max-width: 340px;
            text-align: center;
            flex: 1 1 200px;
        }

        .avatar {
            width: 150px;
            height: 150px;
            border-radius: 12px;
            object-fit: cover;
            border: 4px solid #fff;
            box-shadow: 0 6px 12px rgba(2, 6, 23, 0.08);
        }

        .name {
            margin-top: 12px;
            font-weight: 700;
            font-size: 18px;
            color: #0f172a;
        }

        .meta {
            font-size: 13px;
            color: var(--muted);
            margin-top: 6px;
        }

        .info {
            flex: 2 1 300px;
        }

        .row {
            display: flex;
            gap: 12px;
            flex-wrap: wrap;
            margin-bottom: 10px;
        }

        .field {
            background: #f8fafc;
            padding: 12px;
            border-radius: 8px;
            flex: 1;
            min-width: 180px;
        }

        .label {
            font-size: 12px;
            color: var(--muted);
            margin-bottom: 6px;
            display: block;
        }

        .value {
            font-weight: 600;
            color: #0f172a;
        }

        .badges {
            display: flex;
            gap: 8px;
            align-items: center;
            margin-top: 10px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .badge {
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 600;
            font-size: 13px;
            color: #fff;
        }

        .badge--active {
            background: var(--success);
        }

        .badge--inactive {
            background: var(--danger);
        }

        .footer-note {
            margin-top: 14px;
            color: var(--muted);
            font-size: 13px;
        }

        /* --- RESPONSIVE --- */
        @media (max-width: 720px) {
            body {
                padding: 12px;
                background-size: 280px auto;
            }

            .header .logo {
                max-width: 160px;
                margin-bottom: 10px;
            }

            .header h1 {
                font-size: 18px;
            }

            .header .subtitle {
                font-size: 12px;
            }

            .card {
                flex-direction: column;
                padding: 16px;
                gap: 16px;
            }

            .avatar {
                width: 120px;
                height: 120px;
            }

            .name {
                font-size: 16px;
            }

            .field {
                min-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <img src="{{ asset('assets/img/logo.webp') }}" alt="Logo Alcaldía de Pereira y Secretaría de Planeación" class="logo" />
            <h1>Consulta pública del funcionario</h1>
            <div class="subtitle">
                Verifique la información y póngase en contacto con la Secretaría de Planeación si hay inconsistencias.
            </div>
        </div>

        <div class="card">
            <div class="profile">
                @php
                    $photo = $funcionario->foto && $funcionario->foto !== 'NA'
                        ? asset('assets/img/funcionarios_planeacion/' . $funcionario->foto)
                        : asset('assets/img/user.png');
                @endphp

                <img src="{{ $photo }}" alt="Foto {{ $funcionario->nombre }}" class="avatar" />
                <div class="name">{{ $funcionario->nombre }}</div>
                <div class="meta">ID interno: {{ $funcionario->id }}</div>

                <div class="badges">
                    <div class="badge {{ $funcionario->estado ? 'badge--active' : 'badge--inactive' }}">
                        {{ $funcionario->estado ? 'Activo' : 'Inactivo' }}
                    </div>
                    @if(!empty($funcionario->area))
                        <div class="badge" style="background:#2563eb">{{ $funcionario->area }}</div>
                    @endif
                </div>
            </div>

            <div class="info">
                <div class="row">
                    <div class="field">
                        <span class="label">Nombre completo</span>
                        <div class="value">{{ $funcionario->nombre }}</div>
                    </div>
                    <div class="field">
                        <span class="label">Documento</span>
                        <div class="value">{{ $funcionario->identificacion }}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="field">
                        <span class="label">Grupo sanguíneo</span>
                        <div class="value">{{ $funcionario->grupo_sanguineo ?? 'No registra' }}</div>
                    </div>
                    <div class="field">
                        <span class="label">Área</span>
                        <div class="value">{{ $funcionario->area ?? 'No registrada' }}</div>
                    </div>
                </div>

                <div class="row">
                    <div class="field">
                        <span class="label">Fecha creación</span>
                        <div class="value">{{ optional($funcionario->created_at)->format('Y-m-d H:i') ?? 'No registra' }}</div>
                    </div>
                </div>

                <div class="footer-note">
                    Si desea imprimir este comprobante, use la opción de imprimir del navegador.<br>
                    Para consultas administrativas, contacte Secretaría de Planeación.
                </div>
            </div>
        </div>
    </div>
</body>
</html>
