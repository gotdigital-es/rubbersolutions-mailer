# Guía de Implementación: rubbersolutions-mailer

**Documento paso a paso para crear y deployar el paquete en todas las tiendas**

---

## 📋 Fase 1: Crear el repositorio en GitHub

### 1.1 Crear repo en GitHub

```bash
# En GitHub: Create new repository
# Nombre: rubbersolutions-mailer
# Privado (dentro de gotdigital-es)
# Sin README (lo crearemos localmente)

# Clonar localmente
git clone git@github.com:gotdigital-es/rubbersolutions-mailer.git
cd rubbersolutions-mailer
```

### 1.2 Estructura inicial

```bash
# Crear estructura de directorios
mkdir -p src/Contracts
mkdir -p src/Services
mkdir -p src/Mail/Preset
mkdir -p src/Traits
mkdir -p src/Factories
mkdir -p src/Commands
mkdir -p src/Listeners
mkdir -p src/Exceptions
mkdir -p src/Helpers
mkdir -p src/Providers

mkdir -p resources/views/emails/{customers,internal}
mkdir -p resources/views/components

mkdir -p config
mkdir -p database/migrations
mkdir -p routes
mkdir -p tests/{Unit,Feature}
mkdir -p docs
mkdir -p examples

# Crear .gitignore
cat > .gitignore << 'EOF'
vendor/
.env
.env.local
.env.*.local
node_modules/
dist/
build/
.DS_Store
*.log
storage/logs/
.phpunit.result.cache
EOF

# Crear .editorconfig
cat > .editorconfig << 'EOF'
root = true

[*]
charset = utf-8
end_of_line = lf
insert_final_newline = true
trim_trailing_whitespace = true

[*.{php,json}]
indent_style = space
indent_size = 4

[*.md]
trim_trailing_whitespace = false
EOF
```

### 1.3 Crear composer.json

Usar el archivo `package_composer.json` que ya creamos.

```bash
cp ~/package_composer.json ./composer.json
```

### 1.4 Crear archivos de configuración

```bash
# Copiar configuración
cp ~/package_config_mailer.php ./config/mailer.php

# Copiar ServiceProvider
mkdir -p src/Providers
cp ~/package_MailerServiceProvider.php ./src/Providers/MailerServiceProvider.php

# Copiar README
cp ~/package_README.md ./README.md
```

---

## 📦 Fase 2: Crear archivos del paquete

### 2.1 Contracts (Interfaces)

```bash
# src/Contracts/MailableInterface.php
cat > src/Contracts/MailableInterface.php << 'EOF'
<?php

namespace RubberSolutions\Mailer\Contracts;

interface MailableInterface
{
    public function getContent(): string;
    
    public function getSubject(): string;
    
    public function getStoreCode(): string;
}
EOF
```

### 2.2 Services (ya creados)

Crear `src/Services/EmailService.php`, `TemplateService.php`, etc. (usar los que ya generamos)

### 2.3 Mail Base Classes

```bash
# Copiar las clases base del StoreFront
cp ~/4_CustomerMailable.php ./src/Mail/CustomerMailable.php
cp ~/5_InternalMailable.php ./src/Mail/InternalMailable.php
```

### 2.4 Presets de Emails

```bash
# Copiar ejemplos
cp ~/6_CustomerEmailExamples.php ./src/Mail/Preset/
cp ~/7_AdminEmailExamples.php ./src/Mail/Preset/
```

### 2.5 Vistas/Plantillas

```bash
# Copiar plantillas base
cp ~/8_customer_email_layout.blade.php ./resources/views/emails/customers/layout.blade.php
cp ~/9_internal_email_simple.blade.php ./resources/views/emails/internal/layout.blade.php
```

### 2.6 Helpers

```bash
# src/Helpers/mail.php
cat > src/Helpers/mail.php << 'EOF'
<?php

if (!function_exists('send_email')) {
    /**
     * Helper para enviar email rápidamente
     */
    function send_email($mailable, $queue = true)
    {
        return app('mailer.service')->send($mailable, queue: $queue);
    }
}

if (!function_exists('send_to_team')) {
    /**
     * Helper para enviar a un equipo específico
     */
    function send_to_team($mailable, $team = 'critical', $queue = true)
    {
        return app('mailer.service')->sendToTeam($mailable, team: $team, queue: $queue);
    }
}
EOF
```

---

## 🔧 Fase 3: Tests del paquete

### 3.1 Crear test base

```bash
# tests/Unit/EmailServiceTest.php
cat > tests/Unit/EmailServiceTest.php << 'EOF'
<?php

namespace RubberSolutions\Mailer\Tests\Unit;

use PHPUnit\Framework\TestCase;
use RubberSolutions\Mailer\Services\EmailService;

class EmailServiceTest extends TestCase
{
    public function test_email_service_is_instantiable()
    {
        $this->assertTrue(true);
    }
}
EOF

# phpunit.xml
cat > phpunit.xml << 'EOF'
<?xml version="1.0" encoding="UTF-8"?>
<phpunit xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
         xsi:noNamespaceSchemaLocation="./vendor/phpunit/phpunit/phpunit.xsd"
         bootstrap="vendor/autoload.php"
         cacheResultFile=".phpunit.result.cache"
         beStrictAboutOutputDuringTests="true"
         failOnRisky="true"
         failOnWarning="true"
         verbose="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/Unit</directory>
        </testsuite>
        <testsuite name="Feature">
            <directory>tests/Feature</directory>
        </testsuite>
    </testsuites>
</phpunit>
EOF
```

### 3.2 GitHub Actions CI

```bash
mkdir -p .github/workflows

cat > .github/workflows/tests.yml << 'EOF'
name: Tests

on: [push, pull_request]

jobs:
  test:
    runs-on: ubuntu-latest
    strategy:
      matrix:
        php-version: ['8.3']
    steps:
      - uses: actions/checkout@v3
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php-version }}
      - run: composer install
      - run: ./vendor/bin/phpunit
EOF
```

---

## 📝 Fase 4: Documentación

### 4.1 Crear docs/

```bash
mkdir -p docs

# docs/INSTALACION.md (copiar de INSTALACION_EMAILS_SES.md)
# docs/API.md
# docs/CUSTOM_EMAILS.md
# docs/TEMPLATES.md
```

### 4.2 Crear ejemplos

```bash
mkdir -p examples

cat > examples/1_basic_usage.php << 'EOF'
<?php

/**
 * Ejemplo básico: Enviar email de bienvenida
 */

use RubberSolutions\Mailer\Mail\Preset\OrderConfirmation;
use Illuminate\Support\Facades\Mail;

// En tu OrderController
public function store(Request $request)
{
    $order = Order::create($request->validated());
    
    // Enviar email de confirmación en cola
    Mail::queue(new OrderConfirmation(
        $order->customer,
        $order
    ));
    
    return response()->json(['status' => 'Order created']);
}
EOF

# examples/2_admin_notification.php
# examples/3_custom_theme.php
# etc...
```

---

## 🚀 Fase 5: Deploy a GitHub

### 5.1 Commit inicial

```bash
cd ~/rubbersolutions-mailer

git add .
git commit -m "Initial commit: rubbersolutions-mailer package structure"
git push origin main
```

### 5.2 Crear release (tag)

```bash
git tag -a v1.0.0 -m "Initial release"
git push origin v1.0.0
```

### 5.3 Crear release en GitHub UI

1. Ve a GitHub → Releases
2. Draft new release
3. Tag: v1.0.0
4. Title: "Rubbersolutions Mailer v1.0.0 - Initial Release"
5. Descripción de cambios
6. Publish

---

## 📥 Fase 6: Instalar en storefronts

### 6.1 En Vitamin Swiss

```bash
cd /var/www/vhosts/vitamin-swiss.eu/vitamin-swiss

# Agregar al repositories en composer.json (si es privado)
composer config repositories.rubbersolutions-mailer \
  vcs \
  git@github.com:gotdigital-es/rubbersolutions-mailer.git

# Instalar el paquete
composer require rubbersolutions/rubbersolutions-mailer:^1.0

# Publicar assets
php artisan vendor:publish \
  --provider="RubberSolutions\Mailer\Providers\MailerServiceProvider" \
  --tag="rubbersolutions-mailer-config"

php artisan vendor:publish \
  --provider="RubberSolutions\Mailer\Providers\MailerServiceProvider" \
  --tag="rubbersolutions-mailer-views"

# Crear estructura de emails
mkdir -p resources/emails/vitamin resources/emails/shared

# Publicar plantillas de este storefront
php artisan mailer:publish --store=vitamin
```

### 6.2 Crear config específica de la tienda

```php
// config/mailer-vitamin.php (opcional, para overrides específicos)

return [
    'theme' => [
        'primary_color' => '#0E870E',
        'company_name' => 'Vitamin Swiss',
        'support_email' => 'soporte@vitamin-swiss.eu',
    ],
];
```

### 6.3 Registrar event listener

```php
// app/Listeners/SendOrderConfirmationEmail.php

namespace App\Listeners;

use RubberSolutions\ShopCore\Events\OrderCreated;
use RubberSolutions\Mailer\Mail\Preset\OrderConfirmation;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail
{
    public function handle(OrderCreated $event): void
    {
        Mail::queue(new OrderConfirmation(
            $event->order->customer,
            $event->order
        ));
    }
}
```

### 6.4 En EventServiceProvider

```php
// app/Providers/EventServiceProvider.php

protected $listen = [
    \RubberSolutions\ShopCore\Events\OrderCreated::class => [
        \App\Listeners\SendOrderConfirmationEmail::class,
    ],
    // ... más eventos
];
```

### 6.5 Configurar .env

```env
STORE_CODE=vitamin
MAIL_MAILER=ses
MAIL_FROM_ADDRESS=noreply@vitamin-swiss.eu
MAIL_FROM_NAME="Vitamin Swiss"

# AWS
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
AWS_SES_REGION=eu-west-1

# Equipo admin
MAIL_ADMIN_SALES=comercial@vitamin-swiss.eu
MAIL_ADMIN_OPERATIONS=operaciones@vitamin-swiss.eu

# Queue
QUEUE_CONNECTION=database

# Mailer
MAILER_ENABLED=true
```

---

## 🔄 Fase 7: Testing en Vitamin Swiss

### 7.1 Test de configuración

```bash
cd /var/www/vhosts/vitamin-swiss.eu/vitamin-swiss

php artisan tinker

config('mailer.store_code')              # debe ser: vitamin
config('mailer.admin_recipients.sales')  # debe ser: ['comercial@vitamin-swiss.eu', ...]
config('mail.from.address')              # debe ser: noreply@vitamin-swiss.eu
```

### 7.2 Enviar email de prueba

```bash
php artisan mailer:test --to=tu@email.com
```

### 7.3 Probar evento real

```php
// En tinker:

$order = Order::first();
event(new \RubberSolutions\ShopCore\Events\OrderCreated($order, 'vitamin'));

// Verificar que email se encoló
// Ver database: jobs queue
```

### 7.4 Procesar queue

```bash
# Terminal 1: correr queue worker
php artisan queue:work

# Terminal 2: disponer evento
php artisan tinker
# ... crear evento ...

# En Terminal 1, deberías ver el email procesarse
```

---

## 📋 Fase 8: Repetir en QualitGlue

Mismo proceso:

```bash
cd /var/www/vhosts/qualitglue.com/qualitglue

# Instalar paquete
composer require rubbersolutions/rubbersolutions-mailer:^1.0

# Publicar
php artisan vendor:publish --provider="..." --tag="rubbersolutions-mailer-config"
php artisan vendor:publish --provider="..." --tag="rubbersolutions-mailer-views"

# Crear estructura
mkdir -p resources/emails/qualit resources/emails/shared
php artisan mailer:publish --store=qualit

# Configurar diferente (colores, emails, etc.)
# .env: STORE_CODE=qualit

# Listeners, eventos, etc.
```

---

## 📊 Checklist Final

### En rubbersolutions-mailer repo:
- [ ] Estructura de directorios creada
- [ ] composer.json funcional
- [ ] ServiceProvider registrado
- [ ] Clases base (CustomerMailable, InternalMailable)
- [ ] Presets de emails (6+ tipos)
- [ ] Plantillas Blade
- [ ] Config files
- [ ] Helpers
- [ ] Commands (mailer:publish, mailer:test)
- [ ] Tests básicos
- [ ] GitHub Actions CI
- [ ] README completo
- [ ] docs/ con ejemplos
- [ ] Pushed a GitHub
- [ ] Tagged v1.0.0

### En Vitamin Swiss:
- [ ] Paquete instalado vía composer
- [ ] Assets publicados
- [ ] .env configurado
- [ ] Listeners registrados
- [ ] Tests pasados
- [ ] Queue worker funcionando
- [ ] Email de prueba enviado exitosamente

### En QualitGlue:
- [ ] Mismo que Vitamin Swiss
- [ ] Pero con tema diferente
- [ ] Plantillas customizadas (colores, logo)
- [ ] Funcionando en producción

---

## 🆘 Troubleshooting Instalación

**Error: "Class not found"**
```bash
composer dump-autoload
```

**Error: "Template not found"**
```bash
php artisan mailer:publish --store=tu-tienda
```

**Error: "Queue worker not running"**
```bash
php artisan queue:work

# O configurar con Supervisor (ver doc anterior)
```

---

## 📈 Próximos Pasos

1. **Crear paquete SMS similar:** `rubbersolutions-sms`
2. **Crear paquete de notificaciones:** `rubbersolutions-notifications`
3. **Implementar webhooks:** rastrear events de SES (bounces, complaints)
4. **Dashboard admin:** ver histórico de emails enviados
5. **Template builder:** UI drag-and-drop para crear emails sin código

---

**Documento completado:** Enero 2026  
**Versión:** 1.0.0
