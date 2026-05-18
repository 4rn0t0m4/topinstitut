@php
    $client = config('services.adsense.client');
    $slot = config('services.adsense.slot_infeed');
    $layout = config('services.adsense.slot_infeed_layout');
@endphp

@if($client && $slot)
    <ins class="adsbygoogle"
         style="display:block"
         data-ad-format="fluid"
         data-ad-layout-key="{{ $layout }}"
         data-ad-client="{{ $client }}"
         data-ad-slot="{{ $slot }}"></ins>
    <script>(adsbygoogle = window.adsbygoogle || []).push({});</script>
@endif
