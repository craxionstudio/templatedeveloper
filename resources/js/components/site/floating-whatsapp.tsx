import { usePage } from '@inertiajs/react';
import SmartLink from '@/components/site/smart-link';
import { ChatIcon } from '@/components/site/icons';
import { cn } from '@/lib/utils';

/**
 * Tombol WhatsApp melayang di mobile & tablet (< 1280px), di semua halaman.
 * Di Detail Rumah memakai nomor & template pesan cluster, dan naik di atas bar harga sticky.
 */
export default function FloatingWhatsapp() {
    const { site, cluster } = usePage<{
        cluster?: { name: string; whatsappUrl: string };
    }>().props;
    const onCluster = Boolean(cluster?.whatsappUrl);

    return (
        <SmartLink
            href={cluster?.whatsappUrl ?? site.contact.whatsappUrl}
            aria-label={site.labels.chat_whatsapp}
            data-position="floating"
            data-cluster={cluster?.name}
            className={cn(
                'fixed right-4 z-40 flex size-14 items-center justify-center rounded-full bg-[#1F8A4C] text-white shadow-[0_8px_24px_rgba(0,0,0,0.22)] transition-transform hover:scale-105 xl:hidden',
                // Di atas bar harga sticky Detail Rumah (< 768px).
                onCluster ? 'bottom-[92px] md:bottom-5' : 'bottom-5',
            )}
        >
            <ChatIcon className="size-7" />
        </SmartLink>
    );
}
