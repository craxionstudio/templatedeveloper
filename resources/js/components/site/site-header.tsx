import { usePage } from '@inertiajs/react';
import { useCallback, useRef, useState } from 'react';
import BrandLogo from '@/components/site/brand-logo';
import HotlineLink from '@/components/site/hotline-link';
import { ChatIcon, MenuIcon } from '@/components/site/icons';
import MobileDrawer from '@/components/site/mobile-drawer';
import SmartLink from '@/components/site/smart-link';
import { isActivePath } from '@/lib/url';
import { cn } from '@/lib/utils';

export default function SiteHeader() {
    const { site } = usePage().props;
    const currentUrl = usePage().url;
    const [drawerOpen, setDrawerOpen] = useState(false);
    const menuButtonRef = useRef<HTMLButtonElement>(null);

    const closeDrawer = useCallback(() => {
        setDrawerOpen(false);
        menuButtonRef.current?.focus();
    }, []);

    return (
        <header className="border-b border-line bg-ground">
            <div className="container-site flex h-16 items-center justify-between gap-6 pr-3 md:pr-10 xl:h-[88px] xl:gap-14 xl:pr-20">
                <BrandLogo
                    name={site.brand.name}
                    tagline={site.brand.tagline}
                    homeLabel={site.labels.home}
                />

                {/* Desktop ≥ 1280 */}
                <nav
                    aria-label={site.labels.main_menu}
                    className="hidden grow gap-9 text-[15px] font-medium xl:flex"
                >
                    {site.navigation.map((item) => {
                        const active = isActivePath(currentUrl, item.url);

                        return (
                            <SmartLink
                                key={item.url}
                                href={item.url}
                                newTab={item.new_tab}
                                aria-current={active ? 'page' : undefined}
                                className={cn(
                                    'border-b-2 py-2 text-ink no-underline transition-colors',
                                    active
                                        ? 'border-terracotta font-bold'
                                        : 'border-transparent hover:border-line',
                                )}
                            >
                                {item.label}
                            </SmartLink>
                        );
                    })}
                </nav>

                <div className="hidden items-center gap-8 xl:flex">
                    {site.header.showHotline ? (
                        <HotlineLink
                            hotline={site.contact.hotline}
                            href={site.contact.hotlineUrl}
                            label={site.labels.call_hotline}
                        />
                    ) : null}

                    <SmartLink
                        href={site.header.ctaUrl}
                        className="inline-flex h-12 items-center justify-center gap-2.5 rounded-full bg-terracotta px-[26px] text-base font-semibold whitespace-nowrap text-white no-underline transition-colors hover:bg-terracotta-hover"
                    >
                        <ChatIcon className="size-5" />
                        {site.header.ctaLabel}
                    </SmartLink>
                </div>

                {/* Mobile & tablet < 1280 */}
                <div className="flex items-center gap-1 xl:hidden">
                    {site.mobile.showWhatsappIcon ? (
                        <SmartLink
                            href={site.contact.whatsappUrl}
                            aria-label={site.labels.chat_whatsapp}
                            className="flex size-11 items-center justify-center rounded-full text-terracotta transition-colors hover:text-terracotta-hover"
                        >
                            <ChatIcon className="size-[22px]" />
                        </SmartLink>
                    ) : null}

                    <button
                        ref={menuButtonRef}
                        type="button"
                        aria-label={site.labels.open_menu}
                        aria-expanded={drawerOpen}
                        aria-controls="menu-mobile"
                        onClick={() => setDrawerOpen(true)}
                        className="flex size-11 items-center justify-center rounded-full text-ink"
                    >
                        <MenuIcon className="size-6" />
                    </button>
                </div>
            </div>

            <MobileDrawer
                id="menu-mobile"
                open={drawerOpen}
                onClose={closeDrawer}
            />
        </header>
    );
}
