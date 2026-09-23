/**
 * Link eksternal (http, wa.me, tel:, mailto:) dirender sebagai <a> biasa,
 * link internal lewat <Link> Inertia.
 */
export function isExternalUrl(url: string): boolean {
    return /^(https?:)?\/\//.test(url) || /^(tel|mailto):/.test(url);
}

/**
 * Menu aktif: Beranda hanya cocok persis, menu lain cocok dengan sub-path.
 */
export function isActivePath(currentUrl: string, href: string): boolean {
    const path = currentUrl.split(/[?#]/)[0] || '/';

    if (href === '/') {
        return path === '/';
    }

    return path === href || path.startsWith(`${href}/`);
}
