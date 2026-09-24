/**
 * Bentuk data layout global yang dikirim dari App\Support\SiteLayout.
 */
export type NavLink = {
    label: string;
    url: string;
    new_tab?: boolean;
};

export type FooterColumn = {
    title: string;
    links: NavLink[];
};

export type SiteLayoutData = {
    brand: {
        name: string;
        tagline: string;
        company: string;
    };
    contact: {
        hotline: string;
        hotlineUrl: string | null;
        whatsappUrl: string;
        phone: string;
        email: string;
        officeAddress: string;
        openingHours: string;
    };
    header: {
        showHotline: boolean;
        ctaLabel: string;
        ctaUrl: string;
    };
    mobile: {
        showWhatsappIcon: boolean;
    };
    navigation: NavLink[];
    footer: {
        description: string;
        columns: FooterColumn[];
        socialTitle: string;
        social: NavLink[];
        officeTitle: string;
        copyright: string;
        disclaimer: string;
    };
    labels: Record<string, string> & {
        skip_to_content: string;
        main_menu: string;
        open_menu: string;
        close_menu: string;
        chat_whatsapp: string;
        call_hotline: string;
        home: string;
    };
};

export type PageMeta = {
    title: string;
    description?: string | null;
    noindex?: boolean;
};
