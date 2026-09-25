/**
 * Event analytics (brief 8.8), GTM-first: SEMUA event di-push ke dataLayer dengan nama &
 * parameter di docs/TRACKING.md. GA4 (gtag) dan Meta Pixel (fbq) langsung hanya dikirim kalau
 * ID-nya diisi di admin — kosongkan kalau GA4/Pixel sudah dipasang lewat GTM.
 */
type Params = Record<string, string | number | boolean | null | undefined>;

export type AnalyticsEvent =
    | 'generate_lead'
    | 'click_whatsapp'
    | 'click_phone'
    | 'download_brochure'
    | 'download_pricelist'
    | 'view_listing'
    | 'select_house_type';

export type PixelEvent = 'PageView' | 'Lead' | 'Contact' | 'ViewContent';

function clean(params: Params): Record<string, string | number | boolean> {
    return Object.fromEntries(
        Object.entries(params).filter(
            ([, value]) =>
                value !== null && value !== undefined && value !== '',
        ),
    ) as Record<string, string | number | boolean>;
}

export function track(event: AnalyticsEvent, params: Params = {}): void {
    if (typeof window === 'undefined') {
        return;
    }

    const data = clean(params);

    window.dataLayer = window.dataLayer ?? [];
    window.dataLayer.push({ event, ...data });

    // GA4 langsung (opsional, tanpa GTM).
    window.gtag?.('event', event, data);
}

/**
 * eventId dipakai Meta untuk deduplikasi dengan Conversions API (server).
 */
export function pixel(
    event: PixelEvent,
    params: Params = {},
    eventId?: string,
): void {
    if (typeof window === 'undefined' || !window.fbq) {
        return;
    }

    window.fbq(
        'track',
        event,
        clean(params),
        eventId ? { eventID: eventId } : undefined,
    );
}

let firstPageView = true;
let lastPageView: string | null = null;

/**
 * Page view untuk setiap kunjungan Inertia (termasuk halaman pertama untuk GA4 langsung & Pixel).
 * GTM sudah menghitung halaman pertama sendiri (gtm.js), jadi hanya navigasi berikutnya yang
 * dikirim sebagai `virtual_page_view`.
 */
export function pageView(title?: string | null): void {
    if (typeof window === 'undefined') {
        return;
    }

    // Event `navigate` juga muncul untuk URL yang sama (reload parsial, atau URL yang dirapikan
    // server dengan urutan query berbeda): jangan dihitung dua kali.
    const params = new URLSearchParams(window.location.search);
    params.sort();
    const key = `${window.location.pathname}?${params.toString()}`;

    if (lastPageView === key) {
        return;
    }

    lastPageView = key;
    const isFirst = firstPageView;
    firstPageView = false;

    // Judul dari props halaman baru (event `navigate` muncul sebelum <Head> mengganti <title>).
    window.setTimeout(() => {
        const page = {
            page_location: window.location.href,
            page_path: window.location.pathname + window.location.search,
            page_title: title ?? document.title,
        };

        if (!isFirst) {
            window.dataLayer = window.dataLayer ?? [];
            window.dataLayer.push({ event: 'virtual_page_view', ...page });
        }

        window.gtag?.('event', 'page_view', page);

        window.fbq?.('track', 'PageView');
    }, 0);
}

/**
 * Klik link WhatsApp / telepon / unduhan di mana pun (delegasi satu listener).
 * Unduhan ditandai dengan data-track="download_brochure" | "download_pricelist".
 */
export function listenForClicks(): void {
    if (typeof document === 'undefined') {
        return;
    }

    document.addEventListener(
        'click',
        (event) => {
            const link = (event.target as Element | null)?.closest?.('a');

            if (!link) {
                return;
            }

            const href = link.getAttribute('href') ?? '';
            const context = {
                page_path: window.location.pathname,
                link_position: link.dataset.position,
                cluster: link.dataset.cluster,
                link_url: href,
            };

            if (
                link.dataset.track === 'download_brochure' ||
                link.dataset.track === 'download_pricelist'
            ) {
                track(link.dataset.track, { ...context, file_url: href });
            } else if (/^https:\/\/(wa\.me|api\.whatsapp\.com)\//.test(href)) {
                track('click_whatsapp', context);
                pixel('Contact', {
                    content_name: link.dataset.cluster,
                    method: 'whatsapp',
                });
            } else if (href.startsWith('tel:')) {
                track('click_phone', context);
                pixel('Contact', { method: 'phone' });
            }
        },
        { capture: true },
    );
}
