/**
 * Bentuk data dari presenter PHP (app/Presenters).
 */
export type ImageSource = { type: string; srcset: string };

/**
 * Gambar dari App\Presenters\Image. `sources` = varian AVIF/WebP responsif yang sudah dibuat.
 */
export type ImageData = {
    url: string | null;
    alt: string;
    width?: number | null;
    height?: number | null;
    sources?: ImageSource[];
};

export type LinkData = { label: string; url: string };

export type Crumb = { label: string; url: string | null };

export type ClusterCardData = {
    id: number;
    name: string;
    url: string;
    buildingType: string | null;
    /** Maks 2: badge admin dulu (mis. "Baru"), lalu "Promo" otomatis dari Bank Benefit. */
    badges: string[];
    kawasan: { name: string; url: string } | null;
    typesCount: number;
    types: string[];
    /** Maksimal 3 benefit (teks tampil), sisanya dihitung di benefitsMore. */
    benefits: string[];
    benefitsMore: number;
    landArea: string | null;
    bedrooms: string | null;
    price: string | null;
    installment: string | null;
    image: ImageData;
};

export type KawasanCardData = {
    id: number;
    name: string;
    url: string;
    summary: string | null;
    clustersCount: number;
    clusters: string[];
    priceFrom: string | null;
    image: ImageData;
};

export type ArticleCardData = {
    id: number;
    title: string;
    url: string;
    excerpt: string | null;
    category: { name: string; url: string } | null;
    date: string | null;
    dateIso: string | null;
    readingMinutes: number;
    image: ImageData;
};

export type FacilityCardData = {
    id: number;
    name: string;
    description: string | null;
    icon: string | null;
    category: { name: string; slug: string; icon: string | null } | null;
    kawasan: string;
    image: ImageData;
};

export type CtaData = {
    eyebrow: string;
    title: string;
    description: string;
    whatsappLabel: string;
    whatsappUrl: string;
    /** Tombol jadwal survey: WhatsApp dengan template "survey". */
    surveyLabel: string;
    surveyUrl: string;
    /** Nama cluster (Detail Rumah) untuk parameter event click_whatsapp. */
    cluster: string | null;
} | null;

export type Stat = { value: string; label: string };

export type Pagination = {
    current: number;
    last: number;
    prev: string | null;
    next: string | null;
    pages: { page: number; url: string }[];
};
