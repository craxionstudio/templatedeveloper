<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

/**
 * Benefit yang dicentang di satu cluster. teks_tampil kosong = nama benefit.
 */
class BenefitCluster extends Pivot
{
    protected $table = 'benefit_cluster';

    public $incrementing = true;

    protected $fillable = ['benefit_id', 'cluster_id', 'teks_tampil', 'urutan'];

    protected function casts(): array
    {
        return ['urutan' => 'integer'];
    }

    public function benefit(): BelongsTo
    {
        return $this->belongsTo(Benefit::class);
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class);
    }

    /**
     * Teks di website: teks_tampil, atau nama benefit kalau kosong.
     */
    public function label(): string
    {
        return filled($this->teks_tampil) ? trim($this->teks_tampil) : $this->benefit->name;
    }
}
