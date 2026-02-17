{{-- resources/views/emails/customers/layout.blade.php --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Vitamin Swiss' }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            line-height: 1.6;
            color: #232222;
            background-color: #f9fafb;
        }

        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
        }

        /* Header con logo */
        .email-header {
            background: linear-gradient(135deg, #0E870E 0%, #096909 100%);
            padding: 32px 24px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .email-header::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            transform: translate(50px, -50px);
        }

        .email-header__logo {
            height: 40px;
            margin-bottom: 12px;
            filter: brightness(0) invert(1);
        }

        .email-header__title {
            font-size: 28px;
            font-weight: 900;
            color: #ffffff;
            text-transform: uppercase;
            letter-spacing: 0.025em;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .email-header__subtitle {
            font-size: 14px;
            color: rgba(255, 255, 255, 0.8);
            position: relative;
            z-index: 1;
        }

        /* Barra roja decorativa */
        .email-accent-bar {
            height: 4px;
            background-color: #D40000;
        }

        /* Contenido principal */
        .email-content {
            padding: 32px 24px;
        }

        .email-content h1 {
            font-size: 24px;
            font-weight: 900;
            color: #171717;
            text-transform: uppercase;
            margin-bottom: 12px;
            letter-spacing: 0.025em;
        }

        .email-content h2 {
            font-size: 18px;
            font-weight: 700;
            color: #0E870E;
            margin-top: 24px;
            margin-bottom: 12px;
        }

        .email-content h3 {
            font-size: 16px;
            font-weight: 600;
            color: #232222;
            margin-top: 16px;
            margin-bottom: 8px;
        }

        .email-content p {
            margin-bottom: 16px;
            line-height: 1.8;
            font-size: 15px;
        }

        .email-content ul,
        .email-content ol {
            margin-left: 20px;
            margin-bottom: 16px;
        }

        .email-content li {
            margin-bottom: 8px;
            line-height: 1.8;
        }

        .email-content a {
            color: #0E870E;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 1px solid #0E870E;
        }

        .email-content a:hover {
            color: #096909;
            border-bottom-color: #096909;
        }

        /* Sección de introducción */
        .email-intro {
            background-color: #f3f4f6;
            padding: 20px;
            border-left: 4px solid #0E870E;
            margin-bottom: 24px;
            border-radius: 4px;
        }

        .email-intro p {
            margin-bottom: 0;
            font-size: 16px;
            font-weight: 500;
            color: #171717;
        }

        /* Botón principal (CTA) */
        .email-action {
            text-align: center;
            margin: 32px 0;
        }

        .email-action__button {
            display: inline-block;
            padding: 16px 48px;
            background-color: #0E870E;
            color: #ffffff;
            text-decoration: none;
            font-weight: 700;
            font-size: 16px;
            border-radius: 6px;
            transition: background-color 0.2s ease;
            border: 2px solid #0E870E;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        .email-action__button:hover {
            background-color: #096909;
            border-color: #096909;
        }

        .email-action__button--secondary {
            background-color: transparent;
            color: #0E870E;
        }

        .email-action__button--secondary:hover {
            background-color: #f3f4f6;
        }

        /* Boxes de información */
        .email-info-box {
            background-color: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
        }

        .email-info-box strong {
            color: #171717;
        }

        /* Tabla de productos/datos */
        .email-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }

        .email-table th {
            background-color: #f3f4f6;
            padding: 12px;
            text-align: left;
            font-weight: 700;
            color: #171717;
            border-bottom: 2px solid #e5e7eb;
        }

        .email-table td {
            padding: 12px;
            border-bottom: 1px solid #e5e7eb;
        }

        .email-table tr:last-child td {
            border-bottom: none;
        }

        /* Sección de totales */
        .email-totals {
            background-color: #f3f4f6;
            padding: 20px;
            border-radius: 6px;
            margin: 20px 0;
            font-size: 16px;
        }

        .email-totals-row {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .email-totals-row:last-child {
            border-bottom: none;
        }

        .email-totals-row strong {
            color: #171717;
        }

        .email-total-final {
            font-size: 18px;
            font-weight: 900;
            color: #0E870E;
            padding-top: 12px;
            border-top: 2px solid #0E870E;
            margin-top: 12px;
        }

        /* Nota al pie */
        .email-footer-note {
            background-color: #fef2f2;
            border-left: 4px solid #D40000;
            padding: 16px;
            margin: 24px 0;
            border-radius: 4px;
            font-size: 14px;
            line-height: 1.6;
            color: #7f1d1d;
        }

        /* Footer */
        .email-footer {
            background-color: #171717;
            color: rgba(255, 255, 255, 0.7);
            padding: 32px 24px;
            font-size: 13px;
            line-height: 1.8;
            border-top: 4px solid #dbc005;
        }

        .email-footer a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .email-footer a:hover {
            border-bottom-color: rgba(255, 255, 255, 0.7);
        }

        .email-footer__section {
            margin-bottom: 16px;
        }

        .email-footer__section:last-child {
            margin-bottom: 0;
        }

        .email-footer__title {
            font-weight: 700;
            color: #ffffff;
            margin-bottom: 8px;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .email-footer__divider {
            height: 1px;
            background-color: rgba(255, 255, 255, 0.2);
            margin: 16px 0;
        }

        /* Responsive */
        @media (max-width: 600px) {
            .email-container {
                width: 100% !important;
            }

            .email-content {
                padding: 24px 16px;
            }

            .email-header {
                padding: 24px 16px;
            }

            .email-content h1 {
                font-size: 20px;
            }

            .email-action__button {
                width: 100%;
                padding: 14px 24px;
            }

            .email-table {
                font-size: 13px;
            }

            .email-table th,
            .email-table td {
                padding: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- HEADER -->
        <div class="email-header">
            <img src="{{ asset('images/logo-white.svg') }}" alt="Vitamin Swiss" class="email-header__logo">
            <h1 class="email-header__title">{{ $title ?? 'Vitamin Swiss' }}</h1>
            @if (isset($subtitle))
                <p class="email-header__subtitle">{{ $subtitle }}</p>
            @endif
        </div>

        <div class="email-accent-bar"></div>

        <!-- CONTENIDO -->
        <div class="email-content">
            {{-- Introducción opcional --}}
            @if (isset($intro) && $intro)
                <div class="email-intro">
                    <p>{{ $intro }}</p>
                </div>
            @endif

            {{-- Contenido principal (HTML sin procesar) --}}
            {!! $content !!}

            {{-- Botón de acción principal --}}
            @if (isset($mainAction) && $mainAction)
                <div class="email-action">
                    <a href="{{ $mainAction['url'] }}" 
                       class="email-action__button"
                       style="background-color: {{ $mainAction['color'] ?? '#0E870E' }}; 
                               border-color: {{ $mainAction['color'] ?? '#0E870E' }};">
                        {{ $mainAction['label'] }}
                    </a>
                </div>
            @endif

            {{-- Nota importante al pie del contenido --}}
            @if (isset($footerNote) && $footerNote)
                <div class="email-footer-note">
                    {!! $footerNote !!}
                </div>
            @endif
        </div>

        <!-- FOOTER -->
        <footer class="email-footer">
            <div class="email-footer__section">
                <div class="email-footer__title">Contacto</div>
                <p>
                    <strong>Email:</strong> 
                    <a href="mailto:soporte@vitamin-swiss.eu">soporte@vitamin-swiss.eu</a>
                </p>
                <p>
                    <strong>Teléfono:</strong> 
                    <a href="tel:+34123456789">+34 123 456 789</a>
                </p>
            </div>

            <div class="email-footer__divider"></div>

            <div class="email-footer__section">
                <div class="email-footer__title">Enlaces Rápidos</div>
                <p>
                    <a href="{{ route('home') }}">Inicio</a> | 
                    <a href="{{ route('catalog.index') }}">Catálogo</a> | 
                    <a href="{{ route('contact') }}">Contacto</a>
                </p>
            </div>

            <div class="email-footer__divider"></div>

            <div class="email-footer__section">
                <p style="font-size: 12px; color: rgba(255, 255, 255, 0.5);">
                    © {{ date('Y') }} Vitamin Swiss. Todos los derechos reservados.<br>
                    Este es un email automatizado. Por favor, no respondas a este mensaje.<br>
                    <a href="{{ route('account.settings.unsubscribe') }}" style="color: rgba(255, 255, 255, 0.5);">Desuscribirse</a>
                </p>
            </div>
        </footer>
    </div>
</body>
</html>
