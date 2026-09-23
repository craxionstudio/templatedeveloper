import type { SiteLayoutData } from '@/types/site';

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            site: SiteLayoutData;
            [key: string]: unknown;
        };
    }
}
