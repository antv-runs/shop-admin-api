@component('mail::message')
# No Products Found

Hi {{ $user->name }},

We couldn't find any products matching your selected filters.

**Export Format:** {{ strtoupper($format) }}

Please try adjusting your filters and export again.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
