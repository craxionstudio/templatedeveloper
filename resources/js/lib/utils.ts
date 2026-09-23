import { clsx } from 'clsx';
import type { ClassValue } from 'clsx';

/**
 * Gabung className kondisional. Sengaja tanpa tailwind-merge (hemat ±7 KB gzip):
 * jangan kirim dua utility yang saling bertabrakan.
 */
export function cn(...inputs: ClassValue[]) {
    return clsx(inputs);
}
