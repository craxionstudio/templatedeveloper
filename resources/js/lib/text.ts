/**
 * Ganti placeholder {key} di teks dari CMS.
 */
export function fill(
    template: string,
    values: Record<string, string | number | null | undefined>,
): string {
    return template.replace(/\{(\w+)\}/g, (match, key: string) =>
        values[key] === undefined || values[key] === null
            ? match
            : String(values[key]),
    );
}
