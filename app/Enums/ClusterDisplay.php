<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

/**
 * Cara cluster tampil di website (kolom clusters.tampil_sebagai). Label hanya dipakai di admin.
 */
enum ClusterDisplay: string implements HasColor, HasLabel
{
    /** Punya halaman detail, kartu di /properti, filter, sitemap. */
    case Halaman = 'halaman';

    /** Hanya nama di /properti/cluster-lainnya dan section "Cluster lain di kawasan ini". */
    case Daftar = 'daftar';

    public function getLabel(): string
    {
        return match ($this) {
            self::Halaman => 'Halaman lengkap',
            self::Daftar => 'Daftar Cluster Lainnya saja',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Halaman => 'success',
            self::Daftar => 'gray',
        };
    }
}
