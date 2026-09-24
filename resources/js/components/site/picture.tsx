import { cn } from '@/lib/utils';
import type { ImageData } from '@/types/content';

/**
 * Gambar konten. Selama foto belum diunggah, tampil placeholder bergaris dengan label
 * alt text (pola rezabsd: placeholder saat foto kosong).
 */
export default function Picture({
    image,
    className,
    tone = 'light',
    priority = false,
    sizes,
    labelCorner = false,
    label,
    children,
}: {
    image: ImageData;
    className?: string;
    tone?: 'light' | 'dark';
    priority?: boolean;
    sizes?: string;
    /** Label placeholder di pojok kanan atas (hero), bukan di tengah. */
    labelCorner?: boolean;
    /** Label placeholder pendek untuk gambar kecil (alt text lengkap tetap untuk pembaca layar). */
    label?: string;
    children?: React.ReactNode;
}) {
    if (image.url) {
        return (
            <div className={cn('relative overflow-hidden bg-sand', className)}>
                <img
                    src={image.url}
                    alt={image.alt}
                    sizes={sizes}
                    loading={priority ? 'eager' : 'lazy'}
                    fetchPriority={priority ? 'high' : undefined}
                    decoding="async"
                    className="absolute inset-0 size-full object-cover"
                />
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
