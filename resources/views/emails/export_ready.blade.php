@component('mail::message')
# Your Product Export is Ready

Hi {{ $user->name }},

Your product export in **{{ $format }}** format is ready for download!

**Export Details:**
- **Format:** {{ $format }}
- **Products Exported:** {{ $productCount }}
- **Generated At:** {{ now()->format('Y-m-d H:i:s') }}

@component('mail::button', ['url' => $downloadUrl])
Download Export File
@endcomponent

The download link will be available for 7 days.

Thanks,<br>
{{ config('app.name') }}
@endcomponent
