{{--
    GTM-first: semua event dikirim ke dataLayer (lihat docs/TRACKING.md), jadi dataLayer selalu ada.
    GA4 & Meta Pixel langsung bersifat opsional (kosongkan kalau sudah dipasang lewat GTM).
    ID dari Pengaturan Global → Tracking (tidak di-hardcode).
    Antrean (dataLayer, gtag, fbq) dibuat langsung supaya event awal tidak hilang, tapi file
    script pihak ketiga baru dimuat setelah halaman selesai dimuat dan browser idle (brief 8.6).
    Page view per navigasi Inertia dikirim dari resources/js/lib/analytics.ts.
--}}
@php($tracking = \App\Support\Tracking::browser())
<script>window.dataLayer = window.dataLayer || [];</script>
@if ($tracking['gtmId'] || $tracking['ga4Id'] || $tracking['pixelId'])
    <script>
        (function (w, d) {
            var scripts = [];
            @if ($tracking['gtmId'])
                w.dataLayer.push({ 'gtm.start': new Date().getTime(), event: 'gtm.js' });
                scripts.push('https://www.googletagmanager.com/gtm.js?id=' + @js($tracking['gtmId']));
            @endif
            @if ($tracking['ga4Id'])
                w.gtag = function () { w.dataLayer.push(arguments); };
                w.gtag('js', new Date());
                w.gtag('config', @js($tracking['ga4Id']), { send_page_view: false });
                scripts.push('https://www.googletagmanager.com/gtag/js?id=' + @js($tracking['ga4Id']));
            @endif
            @if ($tracking['pixelId'])
                if (!w.fbq) {
                    var n = (w.fbq = function () { n.callMethod ? n.callMethod.apply(n, arguments) : n.queue.push(arguments); });
                    if (!w._fbq) w._fbq = n;
                    n.push = n; n.loaded = true; n.version = '2.0'; n.queue = [];
                }
                w.fbq('init', @js($tracking['pixelId']));
                scripts.push('https://connect.facebook.net/en_US/fbevents.js');
            @endif
            function load() {
                scripts.forEach(function (src) {
                    var s = d.createElement('script');
                    s.async = true;
                    s.src = src;
                    d.head.appendChild(s);
                });
            }
            function schedule() {
                'requestIdleCallback' in w ? w.requestIdleCallback(load, { timeout: 3000 }) : setTimeout(load, 1500);
            }
            d.readyState === 'complete' ? schedule() : w.addEventListener('load', schedule);
        })(window, document);
    </script>
@endif
