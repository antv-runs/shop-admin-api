@component('mail::message')
# Product Export Failed

Hi {{ $user->name }},

Unfortunately, your product export failed to complete.

**Error Details:**
{{ $error }}

Please try again or contact support if the problem persists.

Regards,<br>
{{ config('app.name') }}
@endcomponent
