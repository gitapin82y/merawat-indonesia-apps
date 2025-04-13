<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="shortcut icon" sizes="16x16 32x32 48x48" href="{{asset('assets/img/merawat-indonesia-logo.png')}}" />
  <title>@yield('title')</title>

  @include('includes.public.style')
  @stack('after-style')

   
   <!-- Adsense & Tracking Pixels -->
   @php $adsense = \App\Models\Adsense::first(); @endphp
   
   @if($adsense && $adsense->google_analytics_tag)
   <!-- Google Analytics Tag -->
   <script async src="https://www.googletagmanager.com/gtag/js?id={{ $adsense->google_analytics_tag }}"></script>
   <script>
       window.dataLayer = window.dataLayer || [];
       function gtag(){dataLayer.push(arguments);}
       gtag('js', new Date());
       gtag('config', '{{ $adsense->google_analytics_tag }}');
   </script>
   @endif
   
   @if($adsense && $adsense->google_ads_id)
   <!-- Google Ads Tag -->
   <script async src="https://www.googletagmanager.com/gtag/js?id={{ $adsense->google_ads_id }}"></script>
   <script>
       window.dataLayer = window.dataLayer || [];
       function gtag(){dataLayer.push(arguments);}
       gtag('js', new Date());
       gtag('config', '{{ $adsense->google_ads_id }}');
       
       @if($adsense->google_ads_label)
       gtag('event', 'conversion', {'send_to': '{{ $adsense->google_ads_id }}/{{ $adsense->google_ads_label }}'});
       @endif
   </script>
   @endif
   
   @if($adsense && $adsense->facebook_pixel)
   <!-- Facebook Pixel Code -->
   <script>
       !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
       n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
       n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
       t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
       document,'script','https://connect.facebook.net/en_US/fbevents.js');
       fbq('init', '{{ $adsense->facebook_pixel }}');
       fbq('track', 'PageView');
   </script>
   <noscript>
       <img height="1" width="1" style="display:none" 
            src="https://www.facebook.com/tr?id={{ $adsense->facebook_pixel }}&ev=PageView&noscript=1"/>
   </noscript>
   @endif
   
   @if($adsense && $adsense->tiktok_pixel)
   <!-- TikTok Pixel Code -->
   <script>
       !function (w, d, t) {
         w.TiktokAnalyticsObject=t;var ttq=w[t]=w[t]||[];ttq.methods=["page","track","identify","instances","debug","on","off","once","ready","alias","group","enableCookie","disableCookie"],ttq.setAndDefer=function(t,e){t[e]=function(){t.push([e].concat(Array.prototype.slice.call(arguments,0)))}};for(var i=0;i<ttq.methods.length;i++)ttq.setAndDefer(ttq,ttq.methods[i]);ttq.instance=function(t){for(var e=ttq._i[t]||[],n=0;n<ttq.methods.length;n++)ttq.setAndDefer(e,ttq.methods[n]);return e},ttq.load=function(e,n){var i="https://analytics.tiktok.com/i18n/pixel/events.js";ttq._i=ttq._i||{},ttq._i[e]=[],ttq._i[e]._u=i,ttq._t=ttq._t||{},ttq._t[e]=+new Date,ttq._o=ttq._o||{},ttq._o[e]=n||{};var o=document.createElement("script");o.type="text/javascript",o.async=!0,o.src=i+"?sdkid="+e+"&lib="+t;var a=document.getElementsByTagName("script")[0];a.parentNode.insertBefore(o,a)};
         ttq.load('{{ $adsense->tiktok_pixel }}');
         ttq.page();
       }(window, document, 'ttq');
   </script>
   @endif
</head>

<body>

  <section class="col-12 justify-content-center d-flex">
    <div class="col-12 bg-white col-md-6">
  <!-- start body -->
  @yield('content')
  <!-- end body -->
    </div>
  </section>

  @include('includes.public.script')
  @include('sweetalert::alert')
  @stack('after-script')
</body>

</html>