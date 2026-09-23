import { usePage } from '@inertiajs/react';
import { useEffect, useRef } from 'react';
import BrandLogo from '@/components/site/brand-logo';
import { ChatIcon, CloseIcon } from '@/components/site/icons';
import HotlineLink from '@/components/site/hotline-link';
import SmartLink from '@/components/site/smart-link';
import { isActivePath } from '@/lib/url';
import { cn } from '@/lib/utils';

type MobileDrawerProps = {
    id: string;
    open: boolean;
    onClose: () => void;
};

/**
 * Drawer menu mobile/tablet (< 1280px).
 *
 * Selalu ada di HTML SSR (link menu tetap bisa di-crawl); saat tertutup
 * disembunyikan dengan `inert` + transform sehingga tidak bisa difokus.
 */
export default function MobileDrawer({ id, open, onClose }: MobileDrawerProps) {
    const { site } = usePage().props;
    const currentUrl = usePage().url;
    const closeButtonRef = useRef<HTMLButtonElement>(null);

    useEffect(() => {
        if (!open) {
            return;
        }

        const focusFrame = requestAnimationFrame(() =>
            closeButtonRef.current?.focus(),
        );

        const previousOverflow = document.body.style.overflow;
        document.body.style.overflow = 'hidden';

        const onKeyDown = (event: KeyboardEvent) => {
            if (event.key === 'Escape') {
                onClose();
            }
        };

        const closeOnDesktop = window.matchMedia('(min-width: 1280px)');
        const onBreakpoint = (event: MediaQueryListEvent) => {
            if (event.matches) {
                onClose();
            }
        };

        document.addEventListener('keydown', onKeyDown);
        closeOnDesktop.addEventListener('change', onBreakpoint);

        return () => {
            cancelAnimationFrame(focusFrame);
            document.body.style.overflow = previousOverflow;
            document.removeEventListener('keydown', onKeyDown);
            closeOnDesktop.removeEventListener('change', onBreakpoint);
        };
    }, [open, onClose]);

    return (
        <div className="xl:hidden">
            <div
                aria-hidden="true"
                onClick={onClose}
                className={cn(
                    'fixed inset-0 z-40 bg-ink/50 motion-safe:transition-opacity motion-safe:duration-200',
                    open ? 'opacity-100' : 'pointer-events-none opacity-0',
                )}
            />

            <div
                id={id}
                role="dialog"
                aria-modal={open ? true : undefined}
                aria-label={site.labels.main_menu}
                inert={!open}
                className={cn(
                    'fixed inset-y-0 right-0 z-50 flex w-full max-w-[360px] flex-col bg-ground shadow-2xl motion-safe:duration-300 motion-safe:ease-out',
                    // Buka: langsung visible (supaya bisa difokus), transform dianimasikan.
                    // Tutup: visibility ikut ditransisikan supaya hilang setelah slide selesai.
                    open
                        ? 'visible translate-x-0 motion-safe:transition-transform'
                        : 'invisible translate-x-full motion-safe:transition-[transform,visibility]',
                )}
            >
                <div className="flex h-16 shrink-0 items-center justify-between border-b border-line pr-3 pl-5">
                    <BrandLogo
                        name={site.brand.name}
                        homeLabel={site.labels.home}
                    />
                    <button
                        ref={closeButtonRef}
                        type="button"
                        aria-label={site.labels.close_menu}
                        onClick={onClose}
                        className="flex size-11 items-center justify-center rounded-full text-ink"
                    >
                        <CloseIcon className="size-6" />
                    </button>
                </div>

                <nav
                    aria-label={site.labels.main_menu}
                    className="flex-1 overflow-y-auto px-5 py-4"
                >
                    <ul className="flex flex-col">
                        {site.navigation.map((item) => {
                            const active = isActivePath(currentUrl, item.url);

                            return (
                                <li
                                    key={item.url}
                                    className="border-b border-line last:border-b-0"
                                >
                                    <SmartLink
                                        href={item.url}
                                        newTab={item.new_tab}
                                        onClick={onClose}
                                        aria-current={
                                            active ? 'page' : undefined
                                        }
                                        className={cn(
                                            'flex min-h-14 items-center font-display text-[22px] no-underline',
                                            active
                                                ? 'font-semibold text-terracotta'
                                                : 'font-medium text-ink',
                                        )}
                                    >
                                        {item.label}
                                    </SmartLink>
                                </li>
                            );
                        })}
                    </ul>
                </nav>

                <div className="flex shrink-0 flex-col gap-3 border-t border-line px-5 pt-5 pb-[max(1.25rem,env(safe-area-inset-bottom))]">
                    {site.header.showHotline ? (
                        <HotlineLink
                            hotline={site.contact.hotline}
                            href={site.contact.hotlineUrl}
                            label={site.labels.call_hotline}
                            className="min-h-11"
                        />
                    ) : null}
                    <SmartLink
                        href={site.header.ctaUrl}
                        onClick={onClose}
                        className="flex h-[52px] w-full items-center justify-center gap-2.5 rounded-full bg-terracotta px-[22px] text-base font-semibold text-white no-underline transition-colors hover:bg-terracotta-hover"
                    >
                        <ChatIcon className="size-5" />
                        {site.header.ctaLabel}
                    </SmartLink>
                </div>
            </div>
        </div>
    );
}
