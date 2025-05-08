@component('mail::message')
# Kabar Terbaru Kampanye

Halo {{ $donor['name'] ?? 'Donatur' }},

Terima kasih atas dukungan Anda untuk kampanye "**{{ $campaign->title }}**". 

Kami memiliki kabar terbaru untuk kampanye ini:

## {{ $kabarTerbaru->title }}

@component('mail::panel')
{!! $kabarTerbaru->description !!}
@endcomponent

Donasi Anda membuat perbedaan nyata. Terima kasih telah menjadi bagian dari perjalanan ini.

@component('mail::button', ['url' => url('/kampanye/' . $campaign->slug)])
Lihat Kampanye
@endcomponent

Salam,<br>
{{ config('app.name') }}
@endcomponent