{{-- resources/views/emails/internal/simple.blade.php --}}
# {{ config('app.name') }} - Notificación Interna

{!! $content !!}

---

**Generado automáticamente por:** Vitamin Swiss B2B System  
**Timestamp:** {{ now()->format('d/m/Y H:i:s') }}  
**Entorno:** {{ config('app.env') }}
