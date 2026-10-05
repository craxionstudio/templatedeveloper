<?php

namespace App\Models;

use App\Models\Concerns\FillsSlugAutomatically;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FacilityCategory extends Model
{
    use FillsSlugAutomatically;

    protected $fillable = ['name', 'slug', 'icon', 'sort_order'];

    public function facilities(): HasMany
    {
        return $this->hasMany(Facility::class);
    }
}
