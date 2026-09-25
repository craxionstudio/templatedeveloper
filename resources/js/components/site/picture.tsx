import { Head } from '@inertiajs/react';
import { cn } from '@/lib/utils';
import type { ImageData } from '@/types/content';

/**
 * Nilai `sizes` per tata letak (breakpoint desain: mobile < 768, desktop ≥ 1280, container 1280 px).
 */
export const IMAGE_SIZES = {
    /** Selebar layar (lightbox, galeri mobile). */
    full: '100vw',
    /** Selebar container (hero, cover artikel). */
    container: '(min-width: 1440px) 1280px, 100vw',
    /** Separuh container di desktop. */
    half: '(min-width: 1280px) 640px, 100vw',
    /** Kartu grid 3 kolom (2 kolom di tablet, carousel 82% di mobile). */
    card3: '(min-width: 1280px) 410px, (min-width: 768px) 50vw, 82vw',
    /** Seperempat container / foto kecil di grid. */
    quarter: '(min-width: 1280px) 320px, 50vw',
    /** Foto galeri Detail Rumah (grid besar-kecil). */
    gallery: '(min-width: 1280px) 50vw, 100vw',
    /** Thumbnail di kartu baris. */
    thumb: '(min-width: 768px) 146px, 104px',
    /** Avatar bulat. */
    avatar: '64px',
} as const;

/**
 * Gambar konten (brief 8.6): <picture> dengan varian AVIF/WebP responsif (srcset + sizes),
 * width/height asli, lazy load, dan untuk gambar LCP (`priority`): eager + fetchpriority="high"
 * + preload di <head>. Selama foto belum diunggah, tampil placeholder bergaris dengan label
 * alt text (pola rezabsd).
 */
export default function Picture({
    image,
    className,
    tone = 'light',
    priority = false,
    sizes = '100vw',
    labelCorner = false,
    label,
    children,
}: {
    image: ImageData;
    className?: string;
    tone?: 'light' | 'dark';
    priority?: boolean;
    /** Lebar tampil gambar untuk memilih varian srcset, mis. "(min-width: 1280px) 50vw, 100vw". */
    sizes?: string;
    /** Label placeholder di pojok kanan atas (hero), bukan di tengah. */
    labelCorner?: boolean;
    /** Label placeholder pendek untuk gambar kecil (alt text lengkap tetap untuk pembaca layar). */
    label?: string;
    children?: React.ReactNode;
}) {
    if (image.url) {
        const sources = image.sources ?? [];
        const preload = sources[0];

        return (
            <div className={cn('relative overflow-hidden bg-sand', className)}>
                {priority ? (
                    <Head>
                        {/* Preload varian terkecil yang didukung (AVIF) untuk LCP. */}
                        <link
                            rel="preload"
                            as="image"
                            href={image.url}
                            fetchPriority="high"
                            {...(preload
                                ? {
                                      imagesrcset: preload.srcset,
                                      imagesizes: sizes,
                                      type: preload.type,
                                  }
                                : {})}
                        />
                    </Head>
                ) : null}
                <picture>
                    {sources.map((source) => (
                        <source
                            key={source.type}
                            type={source.type}
                            srcSet={source.srcset}
                            sizes={sizes}
                        />
                    ))}
                    <img
                        src={image.url}
                        alt={image.alt}
                        width={image.width ?? undefined}
                        height={image.height ?? undefined}
                        sizes={sources.length > 0 ? sizes : undefined}
                        loading={priority ? 'eager' : 'lazy'}
                        fetchPriority={priority ? 'high' : undefined}
                        decoding={priority ? 'sync' : 'async'}
                        className="absolute inset-0 size-full object-cover"
                    />
                </picture>
                {children}
            </div>
        );
    }

    return (
        <div
            role="img"
            aria-label={image.alt}
            className={cn(
                'relative flex overflow-hidden text-[13px] font-semibold tracking-[0.08em] uppercase',
                labelCorner
                    ? 'items-start justify-end p-6 text-right xl:p-8'
                    : 'items-center justify-center p-4 text-center',
                tone === 'dark'
                    ? 'placeholder-stripes-dark text-mist'
                    : 'placeholder-stripes text-[#4F4A40]',
                className,
            )}
        >
            <span aria-hidden="true" className="line-clamp-3">
                {label ?? image.alt}
            </span>
            {children}
        </div>
    );
}
