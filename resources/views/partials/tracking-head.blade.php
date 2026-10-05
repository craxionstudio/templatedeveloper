{{--
    Tracking organik (docs/TRACKING.md): GA4 langsung lewat gtag.js, hanya kalau Measurement ID diisi di
    Pengaturan Umum. Antrean gtag dibuat langsung supaya event awal tidak hilang; gtag.js baru dimuat
    setelah halaman selesai dimuat dan browser idle (brief 8.6). Page view per navigasi Inertia dan event
    click_whatsapp dikirim dari resources/js/lib/analytics.ts.
--}}
@if ($googleVerification = \App\Support\Tracking::googleVerification())
    <meta name="google-site-verification" content="{{ $googleVerification }}">
@endif
@if ($bingVerification = \App\Support\Tracking::bingVerification())
    <meta name="msvalidate.01" content="{{ $bingVerification }}">
@endif
@if ($ga4Id = \App\Support\Tracking::ga4Id())
    <script nonce="{{ \Illuminate\Support\Facades\Vite::cspNonce() }}">
        (function (w, d) {
            w.dataLayer = w.dataLayer || [];
            w.gtag = function () { w.dataLayer.push(arguments); };
            w.gtag('js', new Date());
            w.gtag('config', @js($ga4Id), { send_page_view: false });
            function load() {
                var s = d.createElement('script');
                s.async = true;
                s.src = 'https://www.googletagmanager.com/gtag/js?id=' + @js($ga4Id);
                d.head.appendChild(s);
            }
            function schedule() {
                'requestIdleCallback' in w ? w.requestIdleCallback(load, { timeout: 3000 }) : setTimeout(load, 1500);
            }
            d.readyState === 'complete' ? schedule() : w.addEventListener('load', schedule);
        })(window, document);
    </script>
@endif
