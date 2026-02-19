# rubbersolutions-mailer

**Email notification system for Rubber Solutions storefronts**

Paquete Laravel centralizado y reutilizable para gestión de emails transaccionales en todos los storefronts de Rubber Solutions.

---

## 📦 Estructura del Paquete

```
rubbersolutions-mailer/
│
├── src/                              # Código fuente del paquete
│   ├── Contracts/                    # Interfaces/contracts
│   │   ├── MailableInterface.php
│   │   ├── MailerServiceInterface.php
│   │   └── TemplateResolverInterface.php
│   │
│   ├── Services/                     # Servicios centralizados
│   │   ├── EmailService.php          # Orquestador principal
│   │   ├── TemplateService.php       # Resolución de plantillas
│   │   └── MailConfigurationManager.php # Gestión de config por tienda
│   │
│   ├── Mail/                         # Clases base y presets
│   │   ├── BaseMailable.php          # Clase base abstracta
│   │   ├── CustomerMailable.php      # Base para emails con diseño
│   │   ├── InternalMailable.php      # Base para emails internos
│   │   └── Preset/                   # Plantillas predefinidas
│   │       ├── CustomerWelcome.php
│   │       ├── OrderConfirmation.php
│   │       ├── ShipmentNotification.php
│   │       ├── AdminNewCustomer.php
│   │       ├── AdminNewOrder.php
│   │       └── ...
│   │
│   ├── Traits/                       # Traits reutilizables
│   │   ├── HasEmailTemplate.php
│   │   └── MailableDefaults.php
│   │
│   ├── Factories/                    # Factories para crear emails
│   │   └── MailableFactory.php
│   │
│   ├── Commands/                     # Comandos de Artisan
│   │   ├── PublishTemplatesCommand.php    # php artisan mailer:publish
│   │   ├── TestEmailCommand.php           # php artisan mailer:test
│   │   └── ClearEmailCacheCommand.php     # php artisan mailer:cache:clear
│   │
│   ├── Listeners/                    # Event listeners
│   │   ├── LogEmailEvent.php
│   │   └── HandleEmailFailure.php
│   │
│   ├── Exceptions/                   # Excepciones custom
│   │   ├── TemplateNotFoundException.php
│   │   └── MailerException.php
│   │
│   ├── Helpers/                      # Funciones helper
│   │   └── mail.php
│   │
│   └── Providers/
│       └── MailerServiceProvider.php # ServiceProvider principal
│
├── resources/
│   ├── views/                        # Vistas/plantillas del paquete
│   │   ├── emails/
│   │   │   ├── customers/
│   │   │   │   ├── layout.blade.php           # Template base con diseño
│   │   │   │   ├── welcome.blade.php
│   │   │   │   ├── order-confirmation.blade.php
│   │   │   │   └── ...
│   │   │   └── internal/
│   │   │       ├── layout.blade.php           # Template simple markdown
│   │   │       └── notification.blade.php
│   │   │
│   │   └── components/               # Componentes reutilizables
│   │       ├── header.blade.php
│   │       ├── footer.blade.php
│   │       ├── button.blade.php
│   │       └── ...
│   │
│   └── lang/                         # Traducciones (opcional)
│       └── es/mailer.php
│
├── config/
│   └── mailer.php                    # Configuración del paquete
│
├── database/
│   └── migrations/                   # Migraciones (si es necesario)
│       └── create_email_logs_table.php
│
├── routes/                           # Rutas (si es necesario)
│   └── web.php
│
├── tests/                            # Tests del paquete
│   ├── Unit/
│   │   ├── EmailServiceTest.php
│   │   ├── TemplateServiceTest.php
│   │   └── ...
│   └── Feature/
│       ├── SendCustomerEmailTest.php
│       └── MailablePresetsTest.php
│
├── README.md                         # Este archivo
├── composer.json                     # Definición del paquete
├── phpunit.xml                       # Configuración tests
└── .github/
    └── workflows/
        └── tests.yml                 # CI/CD

```

---

## 🚀 Instalación

### 1. Instalar el paquete

```bash
composer require rubbersolutions/rubbersolutions-mailer
```

### 2. Publicar assets del paquete

```bash
# Publicar configuración
php artisan vendor:publish --provider="RubberSolutions\Mailer\Providers\MailerServiceProvider" --tag="rubbersolutions-mailer-config"

# Publicar vistas (plantillas base)
php artisan vendor:publish --provider="RubberSolutions\Mailer\Providers\MailerServiceProvider" --tag="rubbersolutions-mailer-views"
```

### 3. Crear estructura de emails por tienda (IMPORTANTE)

```bash
# Crear directorio para emails personalizados
mkdir -p resources/emails/shared
mkdir -p resources/emails/{STORE_CODE}    # Ej: resources/emails/vitamin

# Copiar plantillas base a tu tienda (opcional)
cp resources/views/vendor/rubbersolutions-mailer/emails/customers/* resources/emails/{STORE_CODE}/
```

### 4. Configurar .env

```env
# Tienda actual
STORE_CODE=vitamin

# Mail (AWS SES)
MAIL_MAILER=ses
MAIL_FROM_ADDRESS=noreply@vitamin-swiss.eu
MAIL_FROM_NAME="Vitamin Swiss"

# AWS
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_SES_REGION=eu-west-1

# Destinatarios admin
MAIL_ADMIN_SALES=comercial@vitamin-swiss.eu
MAIL_ADMIN_OPERATIONS=operaciones@vitamin-swiss.eu
MAIL_ADMIN_TECHNICAL=tech@vitamin-swiss.eu
MAIL_ADMIN_MANAGEMENT=direccion@vitamin-swiss.eu
MAIL_ADMIN_CRITICAL=admin@vitamin-swiss.eu

# Queue (para envío asincrónico)
QUEUE_CONNECTION=database

# Mailer
MAILER_ENABLED=true
MAILER_LOG_EVENTS=true
MAILER_CACHE_TEMPLATES=true
```

---

## 💻 Uso

### Enviar email de cliente (con diseño)

```php
<?php

use RubberSolutions\Mailer\Mail\Preset\OrderConfirmation;
use Illuminate\Support\Facades\Mail;

$order = Order::find(1);

// Enviar en cola (recomendado)
Mail::queue(new OrderConfirmation($order->customer, $order));

// O inmediatamente
Mail::send(new OrderConfirmation($order->customer, $order));
```

### Enviar email interno (markdown simple)

```php
<?php

use RubberSolutions\Mailer\Mail\Preset\AdminNewOrder;

$order = Order::created($data);

// A equipo de operaciones
Mail::to(config('mailer.admin_recipients.operations'))
    ->send(new AdminNewOrder($order));
```

### Usar el servicio centralizado

```php
<?php

use RubberSolutions\Mailer\Services\EmailService;

// Inyección de dependencia
public function store(EmailService $mailer)
{
    // Enviar inmediatamente
    $mailer->send(new WelcomeEmail($customer), queue: false);

    // Enviar en cola
    $mailer->send(new WelcomeEmail($customer), queue: true);

    // A múltiples destinatarios
    $mailer->sendToMultiple(
        new NewsletterEmail(),
        ['user1@example.com', 'user2@example.com']
    );

    // Con reintentos automáticos
    $mailer->sendWithRetry(new CriticalEmail($order), maxAttempts: 5);

    // A un equipo específico
    $mailer->sendToTeam(new AdminNotification($order), team: 'operations');
}
```

### Personalizar emails en tu tienda

Cada tienda puede sobrescribir plantillas:

```
resources/
└── emails/
    ├── shared/                   # Compartidas entre tiendas
    │   └── footer.blade.php
    │
    └── vitamin/                  # Específicas de Vitamin Swiss
        ├── layout.blade.php      # Personalizar diseño
        ├── welcome.blade.php
        └── order-confirmation.blade.php
```

El sistema busca en este orden:
1. `resources/emails/{STORE_CODE}/template.blade.php`
2. `resources/emails/shared/template.blade.php`
3. `vendor/rubbersolutions-mailer/resources/views/template.blade.php` (default)

### Crear email customizado

```php
<?php

namespace App\Mail;

use RubberSolutions\Mailer\Mail\CustomerMailable;

class CustomWelcomeEmail extends CustomerMailable
{
    public function __construct(public Customer $customer)
    {
    }

    protected function getContent(): string
    {
        return <<<'HTML'
        <p>Hola {$this->customer->first_name},</p>
        <p>Bienvenido a nuestra tienda personalizada!</p>
        HTML;
    }

    protected function getSubject(): string
    {
        return 'Bienvenida personalizada';
    }
}
```

---

## 🧪 Testing

```bash
# Ejecutar todos los tests
php artisan test

# Tests específicos
php artisan test tests/Unit/EmailServiceTest.php

# Con coverage
php artisan test --coverage
```

---

## 🔧 Comandos de Artisan

```bash
# Publicar plantillas de tu tienda (crear resources/emails/{STORE_CODE}/)
php artisan mailer:publish --store=vitamin

# Enviar email de prueba
php artisan mailer:test --to=tu@email.com

# Limpiar caché de templates
php artisan mailer:cache:clear
```

---

## 📊 Configuración por tienda

Cada tienda puede customizar en su propio `config/mailer-stores.php`:

```php
// En app/Providers/AppServiceProvider.php

use RubberSolutions\Mailer\Services\MailConfigurationManager;

public function register(): void
{
    app(MailConfigurationManager::class)->setTheme('vitamin', [
        'primary_color'   => '#0E870E',
        'secondary_color' => '#171717',
        'company_name'    => 'Vitamin Swiss',
        'support_email'   => 'soporte@vitamin-swiss.eu',
    ]);
}
```

---

## 🐛 Troubleshooting

### Plantilla no encontrada
```
TemplateNotFoundException: Template 'welcome' not found for store 'vitamin'
```

**Solución:**
- Verificar que existe `resources/emails/vitamin/welcome.blade.php`
- O ejecutar `php artisan mailer:publish --store=vitamin`

### Email no se envía
```
Check that queue worker is running:
ps aux | grep queue:work
```

**Solución:**
```bash
php artisan queue:work

# O configurar con Supervisor para que corra siempre
```

### AWS SES error
```
"This email address is not verified in SES"
```

**Solución:**
- Verificar dirección en AWS SES Console
- Validar que `MAIL_FROM_ADDRESS` en .env está verificada

---

## 📝 Ejemplos

Ver carpeta `examples/` para ejemplos completos de:
- Configuración de Vitamin Swiss
- Configuración de QualitGlue
- Customización de plantillas
- Integración con eventos

---

## 📚 Documentación Completa

- [Instalación paso a paso](docs/INSTALACION.md)
- [API de EmailService](docs/API.md)
- [Crear emails personalizados](docs/CUSTOM_EMAILS.md)
- [Customizar plantillas](docs/TEMPLATES.md)
- [Troubleshooting](docs/TROUBLESHOOTING.md)
- [AWS SES Setup](docs/AWS_SES.md)

---

## 🔐 Seguridad

- ✅ Validación de emails
- ✅ Rate limiting integrado
- ✅ Sanitización de HTML
- ✅ Protección contra XSS
- ✅ Logging de eventos

---

## 📈 Roadmap

- [ ] v1.0: Lanzamiento inicial
- [ ] v1.1: SMS notifications (rubbersolutions-sms)
- [ ] v1.2: Push notifications (rubbersolutions-notifications)
- [ ] v2.0: Webhook tracking mejorado
- [ ] v2.1: Templates con drag-and-drop builder

---

## 📄 Licencia

Proprietary - Rubber Solutions

---

## 👥 Soporte

- **Email:** tech@rubbersolutions.es
- **Issues:** GitHub Issues (repo privado)
- **Documentación:** `/docs`

---

**Versión:** 1.0.0  
**Última actualización:** Enero 2026  
**Mantenedor:** José María / Rubber Solutions
