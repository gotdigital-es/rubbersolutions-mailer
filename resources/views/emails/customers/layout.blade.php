{{-- 
    Layout universal para emails de clientes.
    Todos los valores visuales vienen de $theme (resuelto por STORE_CODE).
--}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? ($theme['company_name'] ?? 'Rubber Solutions') }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

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

        /* ── Header ── */
        .email-header {
            background: linear-gradient(135deg, {{ $theme['primary_color'] ?? '#0E870E' }} 0%, {{ $theme['primary_color_dark'] ?? '#096909' }} 100%);
            padding: 32px 24px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .email-header::after {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 200px; height: 200px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            transform: translate(50px, -50px);
        }

        .email-header__logo { height: 40px; margin-bottom: 12px; }

        .email-header__title {
            font-size: 28px; font-weight: 900;
            color: #ffffff; text-transform: uppercase;
            letter-spacing: 0.025em; margin-bottom: 8px;
            position: relative; z-index: 1;
        }

        .email-header__subtitle {
            font-size: 14px;
            color: rgba(255,255,255,0.8);
            position: relative; z-index: 1;
        }

        /* ── Accent bar ── */
        .email-accent-bar {
            height: 4px;
            background-color: {{ $theme['danger_color'] ?? '#D40000' }};
        }

        /* ── Content ── */
        .email-content { padding: 32px 24px; }

        .email-content h1 {
            font-size: 24px; font-weight: 900; color: #171717;
            text-transform: uppercase; margin-bottom: 12px;
            letter-spacing: 0.025em;
        }

        .email-content h2 {
            font-size: 18px; font-weight: 700;
            color: {{ $theme['primary_color'] ?? '#0E870E' }};
            margin-top: 24px; margin-bottom: 12px;
        }

        .email-content p { margin-bottom: 16px; line-height: 1.8; font-size: 15px; }

        .email-content ul, .email-content ol {
            margin-left: 20px; margin-bottom: 16px;
        }

        .email-content li { margin-bottom: 8px; line-height: 1.8; }

        .email-content a {
            color: {{ $theme['primary_color'] ?? '#0E870E' }};
            text-decoration: none; font-weight: 600;
            border-bottom: 1px solid {{ $theme['primary_color'] ?? '#0E870E' }};
        }

        /* ── Intro box ── */
        .email-intro {
            background-color: #f3f4f6; padding: 20px;
            border-left: 4px solid {{ $theme['primary_color'] ?? '#0E870E' }};
            margin-bottom: 24px; border-radius: 4px;
        }

        .email-intro p { margin-bottom: 0; font-size: 16px; font-weight: 500; color: #171717; }

        /* ── CTA Button ── */
        .email-action { text-align: center; margin: 32px 0; }

        .email-action__button {
            display: inline-block; padding: 16px 48px;
            color: #ffffff; text-decoration: none;
            font-weight: 700; font-size: 16px;
            border-radius: 6px; letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* ── Footer note ── */
        .email-footer-note {
            background-color: #fef2f2;
            border-left: 4px solid {{ $theme['danger_color'] ?? '#D40000' }};
            padding: 16px; margin: 24px 0;
            border-radius: 4px; font-size: 14px;
            line-height: 1.6; color: #7f1d1d;
        }

        /* ── Footer ── */
        .email-footer {
            background-color: {{ $theme['dark_color'] ?? '#171717' }};
            color: rgba(255,255,255,0.7);
            padding: 32px 24px; font-size: 13px;
            line-height: 1.8;
            border-top: 4px solid {{ $theme['accent_color'] ?? '#dbc005' }};
        }

        .email-footer a {
            color: rgba(255,255,255,0.9); text-decoration: none;
            border-bottom: 1px solid rgba(255,255,255,0.3);
        }

        .email-footer__title {
            font-weight: 700; color: #ffffff;
            margin-bottom: 8px; font-size: 12px;
            text-transform: uppercase; letter-spacing: 0.05em;
        }

        .email-footer__divider {
            height: 1px;
            background-color: rgba(255,255,255,0.2);
            margin: 16px 0;
        }

        .email-footer__section { margin-bottom: 16px; }
        .email-footer__section:last-child { margin-bottom: 0; }

        /* ── Responsive ── */
        @media (max-width: 600px) {
            .email-container { width: 100% !important; }
            .email-content { padding: 24px 16px; }
            .email-header { padding: 24px 16px; }
            .email-content h1 { font-size: 20px; }
            .email-action__button { width: 100%; padding: 14px 24px; }
        }
    </style>
</head>
<body>
    <div class="email-container">

        {{-- HEADER --}}
        <div class="email-header">
            @if(!empty($theme['logo_url']))
                <img src="{{ $theme['logo_url'] }}" alt="{{ $theme['company_name'] ?? '' }}" class="email-header__logo">
            @endif
            <h1 class="email-header__title">{{ $title ?? ($theme['company_name'] ?? 'Rubber Solutions') }}</h1>
        </div>

        <div class="email-accent-bar"></div>

        {{-- CONTENT --}}
        <div class="email-content">

            @if(!empty($intro))
                <div class="email-intro">
                    <p>{{ $intro }}</p>
                </div>
            @endif

            {!! $content !!}

            @if(!empty($mainAction))
                <div class="email-action">
                    <a href="{{ $mainAction['url'] }}"
                       class="email-action__button"
                       style="background-color: {{ $mainAction['color'] ?? ($theme['primary_color'] ?? '#0E870E') }};
                              border: 2px solid {{ $mainAction['color'] ?? ($theme['primary_color'] ?? '#0E870E') }};">
                        {{ $mainAction['label'] }}
                    </a>
                </div>
            @endif

            @if(!empty($footerNote))
                <div class="email-footer-note">
                    {!! $footerNote !!}
                </div>
            @endif
        </div>

        {{-- FOOTER --}}
        <footer class="email-footer">
            <div class="email-footer__section">
                <div class="email-footer__title">Contacto</div>
                <p>
                    @if(!empty($theme['support_email']))
                        <strong>Email:</strong>
                        <a href="mailto:{{ $theme['support_email'] }}">{{ $theme['support_email'] }}</a><br>
                    @endif
                    @if(!empty($theme['support_phone']))
                        <strong>Teléfono:</strong>
                        <a href="tel:{{ $theme['support_phone'] }}">{{ $theme['support_phone'] }}</a>
                    @endif
                </p>
            </div>

            <div class="email-footer__divider"></div>

            <div class="email-footer__section">
                <p style="font-size: 12px; color: rgba(255,255,255,0.5);">
                    © {{ date('Y') }} {{ $theme['company_name'] ?? 'Rubber Solutions' }}. Todos los derechos reservados.<br>
                    Este es un email automatizado. Por favor, no respondas a este mensaje.
                </p>
            </div>
        </footer>

    </div>
</body>
</html>
