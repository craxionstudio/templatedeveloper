import { router, usePage } from '@inertiajs/react';
import { useCallback, useEffect, useRef, useState } from 'react';
import { Icon } from '@/components/site/icons';
import Picture from '@/components/site/picture';
import { cn } from '@/lib/utils';
import type { ImageData } from '@/types/content';

export type GalleryData = {
    items: { url: string | null; alt: string; caption: string | null }[];
    placeholders: string[];
    videoUrl: string | null;
    tourUrl: string | null;
};

type Photo = { image: ImageData; caption: string | null };

/**
 * Galeri Detail Rumah: grid 1+4 (desktop), carousel scroll-snap (mobile), lightbox.
 * Denah dari tipe terpilih ikut jadi item terakhir.
 */
export default function Gallery({
    gallery,
    floorplan,
    backUrl,
}: {
    gallery: GalleryData;
    floorplan: ImageData | null;
    backUrl: string;
}) {
    const { labels } = usePage().props.site;
    const [lightbox, setLightbox] = useState<number | null>(null);
    const [slide, setSlide] = useState(0);
    const track = useRef<HTMLDivElement>(null);

    const photos: Photo[] =
        gallery.items.length > 0
            ? gallery.items.map((item) => ({
                  image: { url: item.url, alt: item.alt },
                  caption: item.caption,
              }))
            : gallery.placeholders.map((alt) => ({
                  image: { url: null, alt },
                  caption: null,
              }));

    const all: Photo[] = floorplan
        ? [...photos, { image: floorplan, caption: null }]
        : photos;
    const floorplanIndex = floorplan ? all.length - 1 : null;

    const share = () => {
        if (navigator.share) {
            void navigator.share({
                url: window.location.href,
                title: document.title,
            });
        } else {
            void navigator.clipboard?.writeText(window.location.href);
        }
    };

    const tabs = (
        <div className="flex [scrollbar-width:none] gap-2 overflow-x-auto">
            <button
                type="button"
                onClick={() => setLightbox(0)}
                className="inline-flex h-11 shrink-0 items-center rounded-full bg-ink px-5 text-[15px] font-semibold text-white"
            >
                {labels.gallery_photo}
            </button>
            {gallery.videoUrl ? (
                <a
                    href={gallery.videoUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex h-11 shrink-0 items-center rounded-full border border-line bg-white px-5 text-[15px] font-semibold text-ink no-underline"
                >
                    {labels.gallery_video}
                </a>
            ) : null}
            {gallery.tourUrl ? (
                <a
                    href={gallery.tourUrl}
                    target="_blank"
                    rel="noopener noreferrer"
                    className="inline-flex h-11 shrink-0 items-center rounded-full border border-line bg-white px-5 text-[15px] font-semibold text-ink no-underline"
                >
                    {labels.gallery_tour}
                </a>
            ) : null}
            {floorplanIndex !== null ? (
                <button
                    type="button"
                    onClick={() => setLightbox(floorplanIndex)}
                    className="inline-flex h-11 shrink-0 items-center rounded-full border border-line bg-white px-5 text-[15px] font-semibold text-ink"
                >
                    {labels.gallery_floorplan}
                </button>
            ) : null}
        </div>
    );

    return (
        <>
            {/* Mobile */}
            <div className="md:hidden">
                <div className="relative -mx-5">
                    <div
                        ref={track}
                        onScroll={(e) =>
                            setSlide(
                                Math.round(
                                    e.currentTarget.scrollLeft /
                                        e.currentTarget.clientWidth,
                                ),
                            )
                        }
                        className="flex snap-x snap-mandatory [scrollbar-width:none] overflow-x-auto [&::-webkit-scrollbar]:hidden"
                    >
                        {photos.map((photo, index) => (
                            <button
                                key={index}
                                type="button"
                                onClick={() => setLightbox(index)}
                                className="w-full shrink-0 snap-start"
                            >
                                <Picture
                                    image={photo.image}
                                    priority={index === 0}
                                    className="h-[300px]"
                                />
                            </button>
                        ))}
                    </div>
                    <button
                        type="button"
                        aria-label={labels.back}
                        onClick={() =>
                            window.history.length > 1
                                ? window.history.back()
                                : router.visit(backUrl)
                        }
                        className="absolute top-3.5 left-3.5 flex size-11 items-center justify-center rounded-full bg-white text-ink"
                    >
                        <Icon name="chevronLeft" className="size-5" />
                    </button>
                    <button
                        type="button"
                        aria-label={labels.share}
                        onClick={share}
                        className="absolute top-3.5 right-3.5 flex size-11 items-center justify-center rounded-full bg-white text-ink"
                    >
                        <Icon name="share" className="size-5" />
                    </button>
                    <span className="absolute right-3.5 bottom-3.5 rounded-full bg-ink/80 px-3 py-1 text-[13px] font-semibold text-white">
                        {slide + 1} / {photos.length}
                    </span>
                </div>
                <div className="mt-3">{tabs}</div>
            </div>

            {/* Tablet & desktop */}
            <div className="relative hidden gap-3 overflow-hidden rounded-card md:grid md:h-[420px] md:grid-cols-4 md:grid-rows-2 xl:h-[520px]">
                {photos.slice(0, 5).map((photo, index) => (
                    <button
                        key={index}
                        type="button"
                        onClick={() => setLightbox(index)}
                        className={cn(
                            'overflow-hidden',
                            index === 0 && 'col-span-2 row-span-2',
                        )}
                    >
                        <Picture
                            image={photo.image}
                            priority={index === 0}
                            className="size-full transition-transform duration-300 hover:scale-[1.02]"
                        />
                    </button>
                ))}
                <div className="absolute bottom-5 left-5 [&_a]:shadow-sm [&_button]:shadow-sm">
                    {tabs}
                </div>
                <button
                    type="button"
                    onClick={() => setLightbox(0)}
                    className="absolute right-5 bottom-5 inline-flex h-11 items-center gap-2 rounded-full bg-white px-5 text-[15px] font-semibold shadow-sm"
                >
                    <Icon name="grid" className="size-4" />
                    {labels.gallery_all.replace('{count}', String(all.length))}
                </button>
            </div>

            {lightbox !== null ? (
                <Lightbox
                    photos={all}
                    start={lightbox}
                    onClose={() => setLightbox(null)}
                />
            ) : null}
        </>
    );
}

function Lightbox({
    photos,
    start,
    onClose,
}: {
    photos: Photo[];
    start: number;
    onClose: () => void;
}) {
    const { labels } = usePage().props.site;
    const [index, setIndex] = useState(start);
    const closeRef = useRef<HTMLButtonElement>(null);
    const go = useCallback(
        (step: number) =>
            setIndex((i) => (i + step + photos.length) % photos.length),
        [photos.length],
    );

    useEffect(() => {
        closeRef.current?.focus();
        const previous = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        const onKey = (e: KeyboardEvent) => {
            if (e.key === 'Escape') onClose();
            if (e.key === 'ArrowRight') go(1);
            if (e.key === 'ArrowLeft') go(-1);
        };
        document.addEventListener('keydown', onKey);

        return () => {
            document.body.style.overflow = previous;
            document.removeEventListener('keydown', onKey);
        };
    }, [go, onClose]);

    const photo = photos[index];

    return (
        <div
            role="dialog"
            aria-modal="true"
            aria-label={labels.gallery_photo}
            className="fixed inset-0 z-[60] flex flex-col bg-ink/95 p-4 text-white md:p-8"
        >
            <div className="flex items-center justify-between">
                <span className="text-sm">
                    {index + 1} / {photos.length}
                </span>
                <button
                    ref={closeRef}
                    type="button"
                    aria-label={labels.close}
                    onClick={onClose}
                    className="flex size-11 items-center justify-center rounded-full bg-white/10"
                >
                    <Icon name="close" className="size-6" />
                </button>
            </div>
            <div className="flex flex-1 items-center justify-center gap-3 py-4">
                <button
                    type="button"
                    aria-label={labels.previous_photo}
                    onClick={() => go(-1)}
                    className="hidden size-12 shrink-0 items-center justify-center rounded-full bg-white/10 md:flex"
                >
                    <Icon name="chevronLeft" className="size-6" />
                </button>
                <figure className="flex h-full max-h-[80vh] w-full max-w-5xl flex-col">
                    {photo.image.url ? (
                        <img
                            src={photo.image.url}
                            alt={photo.image.alt}
                            className="min-h-0 flex-1 object-contain"
                        />
                    ) : (
                        <Picture
                            image={photo.image}
                            className="min-h-0 flex-1 rounded-card"
                        />
                    )}
                    {photo.caption ? (
                        <figcaption className="mt-3 text-center text-sm text-mist">
                            {photo.caption}
                        </figcaption>
                    ) : null}
                </figure>
                <button
                    type="button"
                    aria-label={labels.next_photo}
                    onClick={() => go(1)}
                    className="hidden size-12 shrink-0 items-center justify-center rounded-full bg-white/10 md:flex"
                >
                    <Icon name="chevronRight" className="size-6" />
                </button>
            </div>
            <div className="flex justify-center gap-3 md:hidden">
                <button
                    type="button"
                    aria-label={labels.previous_photo}
                    onClick={() => go(-1)}
                    className="flex size-12 items-center justify-center rounded-full bg-white/10"
                >
                    <Icon name="chevronLeft" className="size-6" />
                </button>
                <button
                    type="button"
                    aria-label={labels.next_photo}
                    onClick={() => go(1)}
                    className="flex size-12 items-center justify-center rounded-full bg-white/10"
                >
                    <Icon name="chevronRight" className="size-6" />
                </button>
            </div>
        </div>
    );
}
