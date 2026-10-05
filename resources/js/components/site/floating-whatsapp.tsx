import { usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import SmartLink from '@/components/site/smart-link';
import { ChatIcon } from '@/components/site/icons';
import { cn } from '@/lib/utils';

/** Area tombol versi desktop (ikon + teks) dari pojok kanan bawah layar, plus jarak aman. */
const DESKTOP_AREA = { width: 240 + 16, height: 56 + 32 + 16 };

/**
 * Tombol WhatsApp melayang di semua halaman, pojok kanan bawah.
 * Mobile & tablet (< 1280px): ikon saja (56px). Desktop: ikon + "Chat via WhatsApp".
 * Di Detail Rumah memakai nomor & template pesan cluster, dan di mobile naik di atas bar harga sticky.
 *
 * Supaya tidak menutupi tombol lain: di desktop, kalau area tombol menabrak elemen bertanda
 * `data-floating-avoid` (mis. kartu marketing sticky Detail Rumah), tombol mengecil jadi ikon
 * di margin kanan halaman. Footer diberi ruang bawah untuk tombol ini (site-footer.tsx).
 */
export default function FloatingWhatsapp() {
    const { site, cluster } = usePage<{
        cluster?: { name: string; whatsappUrl: string };
    }>().props;
    const url = usePage().url;
    const onCluster = Boolean(cluster?.whatsappUrl);
    const href = cluster?.whatsappUrl ?? site.contact.whatsappUrl;
    const compact = useCompactOnDesktop(url);

    return (
        <SmartLink
            href={href}
            newTab={href.startsWith('https://')}
            aria-label="Chat via WhatsApp"
            data-position="floating"
            data-cluster={cluster?.name}
            className={cn(
                'fixed right-4 z-40 flex size-14 items-center justify-center gap-2.5 rounded-full bg-[#1F8A4C] text-white no-underline shadow-[0_8px_24px_rgba(0,0,0,0.22)] transition-transform hover:scale-105 xl:bottom-8',
                compact ? 'xl:right-3' : 'xl:right-8 xl:w-auto xl:px-6',
                // Di atas bar harga sticky Detail Rumah (< 768px).
                onCluster ? 'bottom-[92px] md:bottom-5' : 'bottom-5',
            )}
        >
            <ChatIcon className="size-7 xl:size-6" />
            <span
                className={cn(
                    'hidden text-base font-semibold whitespace-nowrap',
                    !compact && 'xl:inline',
                )}
            >
                Chat via WhatsApp
            </span>
        </SmartLink>
    );
}

function useCompactOnDesktop(url: string): boolean {
    const [compact, setCompact] = useState(false);

    useEffect(() => {
        let frame = 0;

        const check = () => {
            frame = 0;
            const width = window.innerWidth;
            const height = window.innerHeight;
            const hit = Array.from(
                document.querySelectorAll('[data-floating-avoid]'),
            ).some((element) => {
                const rect = element.getBoundingClientRect();

                return (
                    rect.width > 0 &&
                    rect.right > width - DESKTOP_AREA.width &&
                    rect.bottom > height - DESKTOP_AREA.height &&
                    rect.top < height
                );
            });

            setCompact(hit);
        };

        const schedule = () => {
            if (!frame) {
                frame = requestAnimationFrame(check);
            }
        };

        check();
        window.addEventListener('scroll', schedule, { passive: true });
        window.addEventListener('resize', schedule);

        return () => {
            cancelAnimationFrame(frame);
            window.removeEventListener('scroll', schedule);
            window.removeEventListener('resize', schedule);
        };
    }, [url]);

    return compact;
}
