/**
 * Tracking organik GA4 langsung lewat gtag.js (docs/TRACKING.md). Tanpa GA4 Measurement ID di
 * Pengaturan Umum, `window.gtag` tidak ada dan semua fungsi di sini tidak melakukan apa pun.
 *
 * Konversi utama: `click_whatsapp` (tandai sebagai Key event di GA4).
 */
type Params = Record<string, string | number | boolean | null | undefined>;

export type AnalyticsEvent =
    | 'click_whatsapp'
    | 'click_phone'
    | 'download_brochure'
    | 'download_pricelist'
    | 'view_listing'
    | 'select_house_type';

function clean(params: Params): Record<string, string | number | boolean> {
    return Object.fromEntries(
        Object.entries(params).filter(
            ([, value]) =>
                value !== null && value !== undefined && value !== '',
        ),
    ) as Record<string, string | number | boolean>;
}

/**
 * @param beacon true = kirim lewat navigator.sendBeacon (transport beacon) supaya event tetap
 *   terkirim walau halaman langsung pindah ke WhatsApp.
 */
export function track(
    event: AnalyticsEvent,
    params: Params = {},
    beacon = false,
): void {
    if (typeof window === 'undefined' || !window.gtag) {
        return;
    }

    window.gtag('event', event, {
        ...clean(params),
        ...(beacon ? { transport_type: 'beacon' } : {}),
    });
}

let lastPageView: string | null = null;

/**
 * Page view untuk setiap kunjungan Inertia (gtag dikonfigurasi dengan send_page_view: false).
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

    // Judul dari props halaman baru (event `navigate` muncul sebelum <Head> mengganti <title>).
    window.setTimeout(() => {
        window.gtag?.('event', 'page_view', {
            page_location: window.location.href,
            page_path: window.location.pathname + window.location.search,
            page_title: title ?? document.title,
        });
    }, 0);
}

/**
 * Klik link WhatsApp / telepon / unduhan di mana pun (delegasi satu listener).
 * Atribut link: data-cluster (nama cluster), data-position (posisi tombol), data-track (unduhan).
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
            const halaman = window.location.pathname;

            if (
                link.dataset.track === 'download_brochure' ||
                link.dataset.track === 'download_pricelist'
            ) {
                track(link.dataset.track, {
                    cluster: link.dataset.cluster,
                    halaman,
                    file_url: href,
                });
            } else if (/^https:\/\/(wa\.me|api\.whatsapp\.com)\//.test(href)) {
                track(
                    'click_whatsapp',
                    {
                        cluster: link.dataset.cluster,
                        posisi_tombol: link.dataset.position ?? 'lainnya',
                        halaman,
                    },
                    true,
                );
            } else if (href.startsWith('tel:')) {
                track('click_phone', {
                    posisi_tombol: link.dataset.position,
                    halaman,
                });
            }
        },
        { capture: true },
    );
}
