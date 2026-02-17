# Arquitectura Final: Sistema de Emails - rubbersolutions-mailer

**Documento de arquitectura: cómo todo encaja junto**

---

## 🎯 Decisión Final

**✅ PAQUETE INDEPENDIENTE: `rubbersolutions-mailer`**

```
github.com/gotdigital-es/rubbersolutions-mailer
```

---

## 🏗️ Arquitectura del Ecosistema

```
┌─────────────────────────────────────────────────────────────────────┐
│                     Rubber Solutions Ecosystem                       │
├─────────────────────────────────────────────────────────────────────┤
│                                                                      │
│  ┌─────────────────────┐      ┌──────────────────────┐             │
│  │  shop-core (Core)   │      │ rubbersolutions-     │             │
│  ├─────────────────────┤      │ mailer (Email)       │             │
│  │ • Models            │      ├──────────────────────┤             │
│  │ • Relations         │      │ • Mail Classes       │             │
│  │ • Services          │◄─────┤ • Templates          │             │
│  │ • Events            │      │ • Services           │             │
│  │ • Validation        │      │ • Presets            │             │
│  └─────────────────────┘      └──────────────────────┘             │
│          △                                △                         │
│          │                                │                         │
│          │ composer require               │ composer require       │
│          │                                │                         │
│  ┌─────────────────────────────┬──────────────────────────────┐    │
│  │                             │                              │    │
│  │  vitamin-swiss.eu           │  qualitglue.com             │    │
│  │  (B2B Vitaminas)            │  (B2C Cianoacrilato)        │    │
│  ├─────────────────────────────┼──────────────────────────────┤    │
│  │ • App (Controllers, etc)    │ • App (Controllers, etc)     │    │
│  │ • app/Listeners             │ • app/Listeners              │    │
│  │ • resources/emails/vitamin  │ • resources/emails/qualit    │    │
│  │ • config/mailer-vitamin.php │ • config/mailer-qualit.php  │    │
│  │ • .env (STORE_CODE=vitamin) │ • .env (STORE_CODE=qualit)   │    │
│  └─────────────────────────────┴──────────────────────────────┘    │
│                                                                      │
│  ┌─────────────────────────────────────────────────────────────┐    │
│  │              AWS (Infraestructura Compartida)               │    │
│  ├─────────────────────────────────────────────────────────────┤    │
│  │ • SES (Simple Email Service)                                │    │
│  │ • SNS (Notificaciones de eventos)                          │    │
│  │ • CloudWatch (Monitoreo)                                    │    │
│  │ • EventBridge (Tracking)                                    │    │
│  └─────────────────────────────────────────────────────────────┘    │
│                                                                      │
└─────────────────────────────────────────────────────────────────────┘
```

---

## 🔄 Flujo de Envío de Email

### Scenario: Confirmación de pedido en Vitamin Swiss

```
┌─────────────────────────────────────────────────────────────────┐
│ Usuario realiza pedido en vitamin-swiss.eu                       │
└──────────────────────┬──────────────────────────────────────────┘
                       │
                       ▼
        ┌──────────────────────────────┐
        │ OrderController@store()       │
        │ Order::create($data)          │
        └──────────┬───────────────────┘
                   │
                   ▼ (dispara evento)
    ┌─────────────────────────────────────┐
    │ OrderCreated Event (de shop-core)   │
    │ - $order (modelo)                   │
    │ - $storeCode = 'vitamin'            │
    └──────────┬────────────────────────┘
               │
               ▼ (escucha listener)
    ┌──────────────────────────────────────────┐
    │ SendOrderConfirmationEmail (Listener)     │
    │ app/Listeners/SendOrderConfirmationEmail  │
    └──────────┬───────────────────────────────┘
               │
               ▼ Crea Mailable
    ┌──────────────────────────────────────────┐
    │ OrderConfirmation (extends CustomerMail) │
    │ - Cliente + Orden                         │
    │ - Renderiza: resources/emails/vitamin/   │
    │   order-confirmation.blade.php           │
    └──────────┬───────────────────────────────┘
               │
               ▼ Encola en Queue
    ┌──────────────────────────────────────────┐
    │ Queue (Database o Redis)                 │
    │ Job almacenado en 'jobs' table           │
    └──────────┬───────────────────────────────┘
               │
               ▼ Queue Worker procesa
    ┌──────────────────────────────────────────┐
    │ php artisan queue:work                   │
    │ - Lee job de la queue                    │
    │ - Ejecuta Mail::send()                   │
    └──────────┬───────────────────────────────┘
               │
               ▼ Envía vía SES
    ┌──────────────────────────────────────────┐
    │ AWS SES (Simple Email Service)           │
    │ - Autentica con AWS credentials          │
    │ - SMTP sobre puerto 587                  │
    │ - Valida SPF/DKIM/DMARC                  │
    └──────────┬───────────────────────────────┘
               │
               ▼ Notifica cliente
    ┌──────────────────────────────────────────┐
    │ Email llega a cliente@empresa.es         │
    │ - Diseño profesional                     │
    │ - Con datos del pedido                   │
    │ - Botón CTA: "Ver pedido"                │
    │ - Footer con logo Vitamin Swiss          │
    └──────────────────────────────────────────┘
```

---

## 📂 Resolución de Plantillas (Template Resolver)

```
Cuando TemplateService busca una plantilla para Vitamin Swiss:

1. ¿Existe resources/emails/vitamin/order-confirmation.blade.php?
   ✓ SÍ → Usar ese (personalizado de la tienda)
   ✗ NO → Siguiente

2. ¿Existe resources/emails/shared/order-confirmation.blade.php?
   ✓ SÍ → Usar ese (compartido entre tiendas)
   ✗ NO → Siguiente

3. ¿Existe vendor/rubbersolutions-mailer/resources/views/
         emails/customers/order-confirmation.blade.php?
   ✓ SÍ → Usar ese (default del paquete)
   ✗ NO → Throw TemplateNotFoundException

┌─────────────────────────────────────────────────────────┐
│                  Búsqueda en orden                       │
├─────────────────────────────────────────────────────────┤
│ resources/emails/vitamin/          (Tienda específica)  │
│ resources/emails/shared/           (Compartidas)        │
│ vendor/.../rubbersolutions-mailer  (Default del paquete)│
└─────────────────────────────────────────────────────────┘
```

### Ejemplo práctico:

```
OrderConfirmation.php renderiza...
↓
Template: 'order-confirmation'
Store: 'vitamin'
↓
TemplateService->resolve('order-confirmation', 'vitamin')
↓
Encuentra en: resources/emails/vitamin/order-confirmation.blade.php
↓
Pasa datos:
  - $order (del Mailable)
  - $customer (del Mailable)
  - $theme (colores corporativos)
  - $storeCode
↓
Renderiza HTML
↓
Envía vía SES
```

---

## 🎨 Personalización por Tienda

### Vitamin Swiss (B2B)

```
config/
├── mailer-vitamin.php
└── theme: {
    'primary_color': '#0E870E',    // Verde corporativo
    'company_name': 'Vitamin Swiss',
    'support_email': 'soporte@vitamin-swiss.eu'
}

resources/emails/
├── vitamin/
│   ├── layout.blade.php           // Override diseño
│   ├── order-confirmation.blade.php
│   └── welcome.blade.php
└── shared/
    └── footer.blade.php           // Compartido
```

**Resultado:** Emails verdes con logo Vitamin Swiss

### QualitGlue (B2C)

```
config/
├── mailer-qualit.php
└── theme: {
    'primary_color': '#dc2626',    // Rojo industrial
    'company_name': 'Qualit Glue',
    'support_email': 'soporte@qualitglue.com'
}

resources/emails/
├── qualit/
│   ├── layout.blade.php           // Diseño diferente
│   ├── order-confirmation.blade.php
│   └── welcome.blade.php
└── shared/
    └── footer.blade.php           // Compartido
```

**Resultado:** Emails rojos con logo QualitGlue

---

## 🔗 Dependencias y Relaciones

```
┌─────────────────────────────────────────────────────────┐
│         rubbersolutions-mailer (Paquete)                │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  ┌─────────────────────┐  ┌──────────────────────┐   │
│  │ Mail Classes        │  │ Services             │   │
│  ├─────────────────────┤  ├──────────────────────┤   │
│  │ • BaseMailable      │  │ • EmailService       │   │
│  │ • CustomerMailable  │──┤ • TemplateService    │   │
│  │ • InternalMailable  │  │ • ConfigManager      │   │
│  │ • Presets (6+)      │  │ • MailableFactory    │   │
│  └─────────────────────┘  └──────────────────────┘   │
│           △                                △           │
│           │                                │           │
│           └────────────────────────────────┘           │
│                       │                                 │
│                       ▼                                 │
│           ┌──────────────────────┐                    │
│           │  Vistas/Templates    │                    │
│           ├──────────────────────┤                    │
│           │ • layout.blade.php   │                    │
│           │ • welcome.blade.php  │                    │
│           │ • components/*       │                    │
│           └──────────────────────┘                    │
│                       │                                 │
│                       ▼                                 │
│           ┌──────────────────────┐                    │
│           │  Configuración       │                    │
│           ├──────────────────────┤                    │
│           │ • config/mailer.php  │                    │
│           │ • temas por tienda   │                    │
│           │ • admin recipients   │                    │
│           └──────────────────────┘                    │
│                                                         │
│  ┌──────────────────────────────────────────┐        │
│  │          Service Provider                │        │
│  ├──────────────────────────────────────────┤        │
│  │ • Registra todos los servicios           │        │
│  │ • Publica configuración                  │        │
│  │ • Registra comandos                      │        │
│  │ • Registra event listeners               │        │
│  └──────────────────────────────────────────┘        │
│                                                         │
└─────────────────────────────────────────────────────────┘
         │
         ▼ (se instala en cada storefront)
┌─────────────────────────────────────────────────────────┐
│              En vitamin-swiss.eu / qualitglue.com        │
├─────────────────────────────────────────────────────────┤
│                                                         │
│  app/Listeners/                                        │
│  ├── SendOrderConfirmationEmail.php                   │
│  ├── SendWelcomeEmail.php                             │
│  └── SendAdminNotifications.php                       │
│                                                         │
│  app/Providers/EventServiceProvider.php               │
│  └── Registra listeners anteriores                    │
│                                                         │
│  resources/emails/{store_code}/                       │
│  ├── layout.blade.php (override)                      │
│  ├── order-confirmation.blade.php                     │
│  └── welcome.blade.php                                │
│                                                         │
│  .env                                                  │
│  └── STORE_CODE=vitamin o STORE_CODE=qualit          │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

---

## 🔌 Integration Points

### 1. Con shop-core

```php
// shop-core lanza eventos
event(new \RubberSolutions\ShopCore\Events\OrderCreated($order, $storeCode));
event(new \RubberSolutions\ShopCore\Events\CustomerCreated($customer, $storeCode));

// Cada storefront escucha
class SendOrderConfirmationEmail implements ShouldQueue {
    public function handle(OrderCreated $event) {
        Mail::queue(new OrderConfirmation($event->order->customer, $event->order));
    }
}
```

### 2. Con AWS SES

```php
// El servicio EmailService sabe hablar con SES
Mail::send($mailable);  // Se configura en config/mail.php

// SES credenciales vienen del .env
MAIL_MAILER=ses
AWS_ACCESS_KEY_ID=...
AWS_SECRET_ACCESS_KEY=...
```

### 3. Con Laravel Queue

```php
// Los emails se encolan
Mail::queue($mailable);

// Queue worker procesa
php artisan queue:work

// El sistema es agnóstico: Database, Redis, etc.
```

---

## 📊 Versionamiento e Independencia

```
Scenarios donde cada tienda puede tener versiones diferentes:

Vitamin Swiss:                    QualitGlue:
composer.json                     composer.json
├── rubbersolutions-mailer: ^1.0  ├── rubbersolutions-mailer: ^2.0
├── shop-core: ^1.2               ├── shop-core: ^1.2
└── laravel/framework: 11.*        └── laravel/framework: 11.*

Si hago breaking change en rubbersolutions-mailer v2.0:
- Vitamin Swiss sigue funcionando con v1.0
- QualitGlue puede actualizar cuando quiera
- Sin dependencia entre storefronts
```

---

## 🚀 Escalabilidad: Futuros Paquetes

```
Mismo patrón, mismo lugar:

RubberSolutions/
├── rubbersolutions-shop-core          ✅ Ya existe
├── rubbersolutions-mailer             ✅ Nuevo (emails)
├── rubbersolutions-sms                ⏳ Siguiente (SMS)
├── rubbersolutions-notifications      ⏳ Push notifications
├── rubbersolutions-payments           ⏳ Pagos
├── rubbersolutions-analytics          ⏳ Tracking
└── rubbersolutions-crm-bridge         ⏳ Integración Krayin

Todos independientes, todos en la misma cuenta de GitHub
```

---

## 📋 Ventajas Finales de esta Arquitectura

| Aspecto | Beneficio |
|---------|-----------|
| **Separación** | Mail ≠ Core. Responsabilidades claras |
| **Reuso** | Mismo paquete en todas las tiendas |
| **Personalización** | Cada tienda personaliza templates y tema |
| **Versionamiento** | Versiones independientes por tienda |
| **Testing** | Mailer tiene tests propios |
| **Mantenimiento** | Cambios en mailer no afectan shop-core |
| **Escalabilidad** | Patrón para otros paquetes futuros |
| **Seguridad** | Updates de SES sin romper core |
| **Deployment** | `composer update` traes cambios de mailer |
| **Git History** | Repo limpio y separado |

---

## 📝 Checklist de Decisión

- ✅ Decisión: **Paquete Independiente**
- ✅ Nombre: **rubbersolutions-mailer**
- ✅ Ubicación: **github.com/gotdigital-es/rubbersolutions-mailer**
- ✅ Integración: **vía composer.json en cada storefront**
- ✅ Personalización: **resources/emails/{store_code}/** por tienda
- ✅ Configuración: **config/mailer.php** global + overrides por tienda
- ✅ Eventos: **shop-core lanza, storefronts escuchan**
- ✅ Independencia: **mailer no depende de shop-core, solo shop-core de mailer**
- ✅ Futuro: **Patrón repetible para SMS, notifications, etc.**

---

## 🎬 Próximos Pasos Inmediatos

1. **Crear repo en GitHub:** `rubbersolutions-mailer`
2. **Implementar estructura completa** (ver GUIA_IMPLEMENTACION_PACKAGE.md)
3. **Deploy a Vitamin Swiss** (testing y validation)
4. **Deploy a QualitGlue** (con tema diferente)
5. **Monitoreo en AWS SES**
6. **Documentar para futuros storefronts**

---

**Documento finalizado:** Enero 2026  
**Decisión:** ✅ APROBADA - Proceder con rubbersolutions-mailer como paquete independiente
