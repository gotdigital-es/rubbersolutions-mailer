# Decisión Arquitectónica: Email System - shop-core vs paquete independiente

**Contexto:** Vitamin Swiss + QualitGlue + futuros storefronts necesitan sistema de emails centralizado

---

## 📊 Comparativa: Dos enfoques posibles

### Opción A: Ampliar `shop-core`

**Pros:**
- ✅ Menos dependencias externas en composer
- ✅ Un único punto de actualización
- ✅ Lógica de email está "cerca" de los modelos (Order, Customer)
- ✅ Las relaciones Order→Email, Customer→Email quedan naturales
- ✅ Ya tienes flujo de push/pull establecido

**Contras:**
- ❌ `shop-core` crece mucho (ya es bastante grande)
- ❌ Si cambias emails en una tienda, potencialmente afecta todas
- ❌ Mas difícil mantener si tienes 10+ storefronts
- ❌ Mezcla responsabilidades: models/services + comunicación
- ❌ shop-core es "compartido y estable", emails es "variable y personalizable"

### Opción B: Nuevo paquete `rubbersolutions-mailer`

**Pros:**
- ✅ Separación de responsabilidades clara
- ✅ Cada storefront puede tener versión diferente
- ✅ Fácil de testear independientemente
- ✅ Plugins/extensiones más fáciles de agregar
- ✅ Si breaks algo en emails, no rompe el core
- ✅ Escalable: futuros paquetes (SMS, push notifications) similares

**Contras:**
- ❌ Una dependencia más en composer
- ❌ Workflow de updates más complejo
- ❌ Pequeña duplicación en structs (configuración)

### Opción C: Dentro de `shop-core` pero como "módulo"

**Descripción:**
- Carpeta `src/Modules/Mailer/` dentro de shop-core
- Autoload aparte, casi como paquete interno
- Permite deshabilitar el módulo en .env

**Pros:**
- ✅ Lo mejor de ambos mundos
- ✅ Facil activar/desactivar por tienda
- ✅ Sin dependencias externas
- ✅ Actualización única

**Contras:**
- ❌ Estructura confusa (¿es shop-core o es modular?)
- ❌ Difícil de "extraer" si luego lo necesitas independiente

---

## 🎯 Recomendación: OPCIÓN B (paquete independiente)

### Razones principales:

1. **Emails es communicación, no core de datos**
   - shop-core = modelos, relaciones, lógica comercial
   - Mailer = notificaciones (comunicación)
   - Son capas distintas

2. **Personalización por tienda**
   - Vitamin Swiss: emails corporativos suizos
   - QualitGlue: emails industrial simple
   - Tu cliente X: emails customizados totalmente
   - Paquete independiente = versionamiento flexible

3. **Escalabilidad futura**
   - Luego: paquete `rubbersolutions-sms`
   - Luego: paquete `rubbersolutions-notifications`
   - Luego: paquete `rubbersolutions-analytics`
   - Patrón consistente de paquetes especializados

4. **Mantenimiento**
   - shop-core cambia poco (estable)
   - Mailer cambia frecuentemente (plantillas, nuevos emails)
   - Mejor separados

5. **Dependencias**
   - Si decides cambiar de AWS SES a SendGrid, es sólo en mailer
   - Si otro storefront usa otro proveedor, tiene su versión

---

## 📦 Estructura Propuesta: `rubbersolutions-mailer`

```
github.com/gotdigital-es/rubbersolutions-mailer/

rubbersolutions-mailer/
├── src/
│   ├── Contracts/
│   │   ├── Mailable.php
│   │   ├── MailerService.php
│   │   └── TemplateResolver.php
│   │
│   ├── Traits/
│   │   ├── HasEmail.php         ← Agregable a models
│   │   └── MailableDefaults.php
│   │
│   ├── Services/
│   │   ├── EmailService.php              ← Orquestador central
│   │   ├── TemplateService.php          ← Resuelve templates por tienda
│   │   └── MailConfigurationManager.php ← Gestiona config por tienda
│   │
│   ├── Mail/
│   │   ├── BaseMailable.php          ← Base para todos
│   │   ├── CustomerMailable.php      ← Con diseño
│   │   ├── InternalMailable.php      ← Markdown simple
│   │   └── Preset/                   ← Templates predefinidos
│   │       ├── WelcomeEmail.php
│   │       ├── OrderConfirmation.php
│   │       └── ...
│   │
│   ├── Views/                        ← Plantillas por defecto
│   │   ├── emails/customers/
│   │   │   ├── layout.blade.php
│   │   │   └── welcome.blade.php
│   │   └── emails/internal/
│   │
│   ├── Config/
│   │   ├── mail.php        ← Config base
│   │   └── templates.php   ← Template overrides
│   │
│   ├── Providers/
│   │   └── MailerServiceProvider.php
│   │
│   ├── Commands/
│   │   ├── PublishTemplates.php      ← Copiar templates a tienda
│   │   └── TestEmail.php             ← Enviar email de prueba
│   │
│   └── Factories/
│       └── MailableFactory.php       ← Crear emails dinámicamente
│
├── config/
│   └── mailer.php          ← Configuración del paquete
│
├── composer.json           ← rubbersolutions-mailer
├── README.md
└── tests/
    ├── Unit/
    └── Feature/

```

---

## 🔄 Flujo de Funcionamiento

### En shop-core (sin cambios):
```
Order → CustomerOrder created event
     ↓
     → evento que Shop puede escuchar
```

### En cada storefront:
```
// 1. Instalación
composer require rubbersolutions/rubbersolutions-mailer

// 2. Publicar templates por tienda
php artisan mailer:publish --store=vitamin

// 3. Customizar en resources/emails/{STORE_CODE}/

// 4. Usar en tu código
use RubberSolutions\Mailer\Mail\Preset\OrderConfirmation;
Mail::send(new OrderConfirmation($order, $store));
```

### Flujo de resolución de templates:

```
OrderConfirmation::class
    ↓
TemplateService->resolve()
    ↓
¿Existe override en resources/emails/{STORE_CODE}/order-confirmation.blade.php?
    ↓ YES → Usar ese
    ↓ NO
    ↓
¿Existe en paquete rubbersolutions-mailer?
    ↓ YES → Usar ese (default)
    ↓ NO
    ↓
Throw: TemplateNotFound
```

---

## 🛠️ Implementación Práctica

### 1. En shop-core: Agregar eventos (mínimo)

```php
// En RubberSolutions\ShopCore\Events\OrderCreated.php

namespace RubberSolutions\ShopCore\Events;

use RubberSolutions\ShopCore\Models\Order;
use Illuminate\Foundation\Events\Dispatchable;

class OrderCreated
{
    use Dispatchable;

    public function __construct(
        public Order $order,
        public string $storeCode
    ) {}
}
```

### 2. En storefronts: Escuchar y enviar

```php
// En app/Listeners/SendOrderConfirmationEmail.php

namespace App\Listeners;

use RubberSolutions\ShopCore\Events\OrderCreated;
use RubberSolutions\Mailer\Mail\Preset\OrderConfirmation;
use Illuminate\Support\Facades\Mail;

class SendOrderConfirmationEmail
{
    public function handle(OrderCreated $event)
    {
        Mail::send(new OrderConfirmation(
            $event->order,
            store: $event->storeCode
        ));
    }
}
```

### 3. Cada tienda personaliza lo que quiere

```php
// En vitamin-swiss/.../EmailServiceProvider.php

public function register()
{
    // Vitamin Swiss usa diseño verde corporativo
    app('mailer.theme')->set('vitamin', [
        'colors' => [
            'primary' => '#0E870E',
            'accent'  => '#dbc005',
        ],
        'company_name' => 'Vitamin Swiss',
    ]);
}

// En qualitglue/.../EmailServiceProvider.php

public function register()
{
    // QualitGlue usa diseño industrial rojo
    app('mailer.theme')->set('qualit', [
        'colors' => [
            'primary' => '#dc2626',
            'accent'  => '#171717',
        ],
        'company_name' => 'Qualit Glue',
    ]);
}
```

---

## 📚 Ventajas de este enfoque

| Aspecto | Beneficio |
|--------|----------|
| **Mantenibilidad** | Cambios en emails no afectan shop-core |
| **Versionamiento** | Cada tienda puede estar en v1.2, otra en v2.0 |
| **Reusabilidad** | Mismo paquete para WordPress, Laravel CLI tools, etc. |
| **Testing** | Paquete tiene sus propios tests independientes |
| **Performance** | Puedes desactivar si no lo necesitas |
| **Seguridad** | Updates de mailer sin necesidad de update core |
| **Extensibilidad** | Plugins que extienden mailer sin tocar tiendas |

---

## 🚀 Roadmap de implementación

### Fase 1: Crear paquete base (esta semana)
- [ ] Crear repo `rubbersolutions-mailer` en GitHub
- [ ] Estructura base con ServiceProvider
- [ ] Emails predefinidos (Welcome, OrderConfirmation, etc.)
- [ ] Tests unitarios

### Fase 2: Integración en shop-core (próxima semana)
- [ ] Agregar eventos mínimos (OrderCreated, CustomerCreated)
- [ ] Documentar cómo escuchar eventos

### Fase 3: Rollout en Vitamin Swiss
- [ ] Instalar paquete
- [ ] Personalizar plantillas
- [ ] Testing en producción
- [ ] Monitoreo SES

### Fase 4: Rollout en QualitGlue
- [ ] Instalar paquete
- [ ] Personalizar plantillas (colores diferentes)
- [ ] Testing

### Fase 5: Soporte para otros storefronts
- [ ] Documentación para nuevos storefronts
- [ ] Ejemplos de customización
- [ ] Guía de troubleshooting

---

## 📝 Decisión Final Recomendada

**✅ CREAR PAQUETE INDEPENDIENTE `rubbersolutions-mailer`**

**Por qué:**
1. Separación de responsabilidades
2. Escalabilidad (otros paquetes después)
3. Personalización flexible por tienda
4. Mantenimiento simplificado
5. Versionamiento independiente

**No afecta:**
- shop-core sigue siendo el core
- Solo agregamos eventos mínimos
- Cada tienda la controla

**Resultado:**
```
Ecosystem Rubber Solutions:
├── rubbersolutions-shop-core          (Models, relaciones, lógica)
├── rubbersolutions-mailer             (Emails, notificaciones)
├── rubbersolutions-sms                (SMS, para futuro)
└── rubbersolutions-notifications      (Push, WebSockets, para futuro)
```

---

**¿Estás de acuerdo con este enfoque? Paso a crear la estructura del paquete independiente.**
