/**
 * Nama cluster tanpa halaman sendiri sebagai chip (tanpa link, foto, atau status).
 * Gaya chip sama dengan nama cluster di kartu kawasan.
 */
export default function ClusterNameChips({ names }: { names: string[] }) {
    return (
        <ul className="flex flex-wrap gap-1.5">
            {names.map((name) => (
                <li
                    key={name}
                    className="rounded-lg border border-[#E4DDCF] bg-ground px-2.5 py-[5px] text-[13px] font-medium"
                >
                    {name}
                </li>
            ))}
        </ul>
    );
}
