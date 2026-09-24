import { router } from '@inertiajs/react';
import { useState } from 'react';
import type { FormEvent } from 'react';
import { Icon } from '@/components/site/icons';
import { cn } from '@/lib/utils';

type Option = { value: string; label: string };

export type FiltersData = {
    active: Record<string, string>;
    sort: string;
    visible: string[];
    labels: Record<string, string>;
    options: Record<string, Option[]>;
    sortOptions: Option[];
    text: {
        all: string;
        allKawasan: string;
        apply: string;
        mobile: string;
        sort: string;
    };
};

const select =
    'h-12 w-full rounded-xl border-[1.5px] border-[#CFC7B6] bg-white px-3 text-[15px] text-ink focus:border-ink focus:outline-none';

/**
 * Filter tampilan Cluster: form GET biasa (tetap jalan tanpa JS), hasil tetap di /properti?….
 */
export function FilterBar({
    filters,
    action,
}: {
    filters: FiltersData;
    action: string;
}) {
    const [open, setOpen] = useState(false);
    const [values, setValues] = useState<Record<string, string>>({
        ...filters.active,
    });

    const submit = (event: FormEvent) => {
        event.preventDefault();
        const params = Object.fromEntries(
            Object.entries({ ...values, urut: filters.sort }).filter(
                ([, v]) => v,
            ),
        );

        router.get(action, params, { preserveScroll: true });
        setOpen(false);
    };

    const fields = filters.visible.map((key) => (
        <div key={key} className="flex flex-1 flex-col gap-1.5">
            <label
                htmlFor={`filter-${key}`}
                className="text-[13px] font-semibold text-caption"
            >
                {filters.labels[key]}
            </label>
            <select
                id={`filter-${key}`}
                name={key}
                value={values[key] ?? ''}
                onChange={(e) =>
                    setValues({ ...values, [key]: e.target.value })
                }
                className={select}
            >
                <option value="">
                    {key === 'kawasan'
                        ? filters.text.allKawasan
                        : filters.text.all}
                </option>
                {filters.options[key]?.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
        </div>
    ));

    const activeChips = filters.visible.map((key) => {
        const option = filters.options[key]?.find(
            (o) => o.value === filters.active[key],
        );

        return (
            <button
                key={key}
                type="button"
                onClick={() => setOpen(true)}
                className={cn(
                    'inline-flex h-11 shrink-0 items-center gap-1.5 rounded-full border-[1.5px] px-4 text-[15px]',
                    option
                        ? 'border-ink bg-ink text-white'
                        : 'border-line bg-white text-ink',
                )}
            >
                {option ? option.label : filters.labels[key]}
                <Icon name="chevronDown" className="size-4" />
            </button>
        );
    });

    return (
        <div className="flex flex-col gap-3">
            {/* Mobile: tombol Filter + chip, panel filter terbuka di bawahnya. */}
            <div className="-mx-5 flex [scrollbar-width:none] gap-2 overflow-x-auto px-5 md:hidden">
                <button
                    type="button"
                    aria-expanded={open}
                    aria-controls="filter-panel"
                    onClick={() => setOpen(!open)}
                    className="inline-flex h-11 shrink-0 items-center gap-2 rounded-full bg-ink px-5 text-[15px] font-semibold text-white"
                >
                    <Icon name="filter" className="size-4" />
                    {filters.text.mobile}
                </button>
                {activeChips}
            </div>

            <form
                id="filter-panel"
                action={action}
                method="get"
                onSubmit={submit}
                className={cn(
                    'flex-col gap-4 rounded-card bg-white p-5 md:flex md:flex-row md:flex-wrap md:items-end md:p-6 xl:flex-nowrap',
                    open ? 'flex' : 'hidden',
                )}
            >
                {fields}
                <input type="hidden" name="urut" value={filters.sort} />
                <button
                    type="submit"
                    className="inline-flex h-12 shrink-0 items-center justify-center gap-2 rounded-xl bg-ink px-7 text-[15px] font-semibold text-white"
                >
                    <Icon name="search" className="size-[18px]" />
                    {filters.text.apply}
                </button>
            </form>
        </div>
    );
}

/**
 * Pilihan urutan; berubah → langsung memuat ulang dengan filter yang sama.
 */
export function SortSelect({
    filters,
    action,
}: {
    filters: FiltersData;
    action: string;
}) {
    return (
        <form
            action={action}
            method="get"
            className="flex items-center gap-2.5"
        >
            {Object.entries(filters.active).map(([key, value]) => (
                <input key={key} type="hidden" name={key} value={value} />
            ))}
            <label
                htmlFor="sort"
                className="hidden text-sm text-caption md:block"
            >
                {filters.text.sort}
            </label>
            <select
                id="sort"
                name="urut"
                defaultValue={filters.sort}
                onChange={(e) =>
                    router.get(
                        action,
                        { ...filters.active, urut: e.target.value },
                        { preserveScroll: true },
                    )
                }
                className="h-11 rounded-xl border-[1.5px] border-[#CFC7B6] bg-white px-3 text-[15px]"
            >
                {filters.sortOptions.map((option) => (
                    <option key={option.value} value={option.value}>
                        {option.label}
                    </option>
                ))}
            </select>
            <noscript>
                <button type="submit">{filters.text.apply}</button>
            </noscript>
        </form>
    );
}
