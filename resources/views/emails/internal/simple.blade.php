@component('mail::message')
# {{ config('mail.from.name', 'Rubber Solutions') }} - Notificación Interna

{!! $content !!}

---

**Generado automáticamente** · {{ now()->format('d/m/Y H:i:s') }} · {{ config('app.env') }}
@endcomponent
