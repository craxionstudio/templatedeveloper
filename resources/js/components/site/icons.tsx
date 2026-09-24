import type { ReactNode, SVGProps } from 'react';

type IconProps = Omit<SVGProps<SVGSVGElement>, 'name'>;

/**
 * Ikon stroke inline, path diambil dari referensi desain (docs/design).
 */
function Svg({
    children,
    strokeWidth = 1.8,
    ...props
}: IconProps & { children: ReactNode }) {
    return (
        <svg
            viewBox="0 0 24 24"
            fill="none"
            stroke="currentColor"
            strokeWidth={strokeWidth}
            strokeLinecap="round"
            strokeLinejoin="round"
            aria-hidden="true"
            {...props}
        >
            {children}
        </svg>
    );
}

const paths = {
    chat: <path d="M21 12a9 9 0 0 1-13.5 7.8L3 21l1.2-4.5A9 9 0 1 1 21 12z" />,
    phone: (
        <path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z" />
    ),
    search: (
        <>
            <circle cx="11" cy="11" r="7" />
            <path d="M20 20l-4-4" />
        </>
    ),
    arrowRight: <path d="M5 12h14M13 6l6 6-6 6" />,
    mail: <path d="M3 6h18v12H3zM3 7l9 6 9-6" />,
    check: <path d="M5 12l4 4L19 6" />,
    chevronLeft: <path d="M15 5l-7 7 7 7" />,
    chevronRight: <path d="M9 5l7 7-7 7" />,
    chevronDown: <path d="M6 9l6 6 6-6" />,
    pin: (
        <>
            <path d="M12 21s-7-6-7-11a7 7 0 0 1 14 0c0 5-7 11-7 11z" />
            <circle cx="12" cy="10" r="2.5" />
        </>
    ),
    grid: <path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z" />,
    land: <path d="M4 4h16v16H4zM4 9h5M15 20v-5" />,
    building: <path d="M3 11l9-7 9 7v9H3zM9 20v-6h6v6" />,
    bed: (
        <path d="M3 18v-7a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2v7M3 14h18M7 9V6h4v3" />
    ),
    bath: (
        <path d="M4 12h16v3a4 4 0 0 1-4 4H8a4 4 0 0 1-4-4v-3zM6 12V5a2 2 0 0 1 4 0" />
    ),
    stairs: <path d="M3 20h5v-5h5v-5h5V5h3" />,
    car: <path d="M5 16l1.5-5h11L19 16M4 16h16v3H4zM7 19v2M17 19v2" />,
    calendar: <path d="M4 6h16v15H4zM4 10h16M8 3v4M16 3v4" />,
    clock: (
        <>
            <circle cx="12" cy="12" r="9" />
            <path d="M12 7v5l3 2" />
        </>
    ),
    download: <path d="M12 3v12m0 0l-5-5m5 5l5-5M4 21h16" />,
    share: (
        <>
            <circle cx="18" cy="5" r="3" />
            <circle cx="6" cy="12" r="3" />
            <circle cx="18" cy="19" r="3" />
            <path d="M8.6 10.5l6.8-4M8.6 13.5l6.8 4" />
        </>
    ),
    gift: (
        <path d="M4 10h16v11H4zM2 7h20v3H2zM12 7v14M12 7c-2-4-6-4-6-1.5S10 7 12 7zm0 0c2-4 6-4 6-1.5S14 7 12 7z" />
    ),
    shieldCheck: <path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3z" />,
    close: <path d="M6 6l12 12M18 6L6 18" />,
    filter: <path d="M4 4h7v7H4zM13 4h7v7h-7zM4 13h7v7H4zM13 13h7v7h-7z" />,
    play: <path d="M8 5v14l11-7z" />,
    rotate: <path d="M21 12a9 9 0 1 1-3-6.7M21 4v5h-5" />,
} as const;

export type IconName = keyof typeof paths;

export function Icon({ name, ...props }: IconProps & { name: IconName }) {
    return <Svg {...props}>{paths[name]}</Svg>;
}

/**
 * Ikon yang dipilih admin (App\Support\IconOptions) → path desain.
 */
const contentIcons: Record<string, ReactNode> = {
    sprout: (
        <path d="M12 22V12M12 12c-4 0-7-3-7-7 4 0 7 3 7 7zm0 0c4 0 7-3 7-7-4 0-7 3-7 7z" />
    ),
    trees: (
        <path d="M12 22V12M12 12c-4 0-7-3-7-7 4 0 7 3 7 7zm0 0c4 0 7-3 7-7-4 0-7 3-7 7z" />
    ),
    waves: (
        <path d="M2 12c2 0 2-1.5 4-1.5S8 12 10 12s2-1.5 4-1.5 2 1.5 4 1.5 2-1.5 4-1.5M2 17c2 0 2-1.5 4-1.5S8 17 10 17s2-1.5 4-1.5 2 1.5 4 1.5 2-1.5 4-1.5" />
    ),
    house: <path d="M3 21h18M5 21V10l7-5 7 5v11M10 21v-6h4v6" />,
    shield: <path d="M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6l8-3z" />,
    route: <path d="M8 3L4 21M16 3l4 18M12 5v2M12 11v2M12 17v2" />,
    'train-front': (
        <path d="M6 3h12a2 2 0 0 1 2 2v9a3 3 0 0 1-3 3H7a3 3 0 0 1-3-3V5a2 2 0 0 1 2-2zM4 10h16M8 21l2-4M16 21l-2-4" />
    ),
    bus: (
        <path d="M5 4h14a1 1 0 0 1 1 1v11a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V5a1 1 0 0 1 1-1zM4 11h16M7 17v3M17 17v3M7.5 14h.01M16.5 14h.01" />
    ),
    'building-2': (
        <path d="M4 21V5l8-2v18M12 7l8 2v12M8 8h1M8 12h1M8 16h1M15 12h1M15 16h1M2 21h20" />
    ),
    store: (
        <path d="M4 21V5l8-2v18M12 7l8 2v12M8 8h1M8 12h1M8 16h1M15 12h1M15 16h1M2 21h20" />
    ),
    'shopping-cart': (
        <>
            <path d="M3 4h2l2.5 11h11L21 7H6.5" />
            <circle cx="9" cy="20" r="1" />
            <circle cx="18" cy="20" r="1" />
        </>
    ),
    'graduation-cap': (
        <>
            <path d="M2 9l10-5 10 5-10 5z" />
            <path d="M6 11v5c3 2 9 2 12 0v-5" />
        </>
    ),
    stethoscope: <path d="M4 21V7h16v14M9 21v-4h6v4M12 10v4M10 12h4M2 21h20" />,
    dumbbell: (
        <>
            <circle cx="12" cy="12" r="9" />
            <path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18" />
        </>
    ),
    church: <path d="M4 21V8l8-5 8 5v13M9 21v-5a3 3 0 0 1 6 0v5M12 3v3" />,
    tag: (
        <>
            <path d="M3 12V3h9l9 9-9 9z" />
            <circle cx="7.5" cy="7.5" r="1" />
        </>
    ),
    gift: (
        <path d="M4 10h16v11H4zM2 7h20v3H2zM12 7v14M12 7c-2-4-6-4-6-1.5S10 7 12 7zm0 0c2-4 6-4 6-1.5S14 7 12 7z" />
    ),
    zap: <path d="M13 2L4 14h7l-1 8 9-12h-7z" />,
    check: <path d="M5 12l4 4L19 6" />,
    'map-pin': (
        <>
            <path d="M12 21s-7-6-7-11a7 7 0 0 1 14 0c0 5-7 11-7 11z" />
            <circle cx="12" cy="10" r="2.5" />
        </>
    ),
};

export function ContentIcon({
    name,
    ...props
}: IconProps & { name?: string | null }) {
    return (
        <Svg {...props}>{contentIcons[name ?? ''] ?? contentIcons.check}</Svg>
    );
}

/* Alias lama (header/drawer Milestone 1). */
export const ChatIcon = (p: IconProps) => <Icon name="chat" {...p} />;
export const PhoneIcon = (p: IconProps) => <Icon name="phone" {...p} />;
export const SearchIcon = (p: IconProps) => <Icon name="search" {...p} />;
export const CloseIcon = (p: IconProps) => (
    <Icon name="close" strokeWidth={2} {...p} />
);
export const MenuIcon = (p: IconProps) => (
    <Svg strokeWidth={2} {...p}>
        <path d="M4 7h16M4 12h16M4 17h16" />
    </Svg>
);
