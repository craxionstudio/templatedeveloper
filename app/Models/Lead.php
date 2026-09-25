<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Lead extends Model
{
    protected $fillable = [
        'event_id', 'name', 'whatsapp', 'email', 'cluster_id', 'house_type_id', 'payment_plan', 'message',
        'source_page', 'source_position', 'utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term',
        'first_utm_source', 'first_utm_medium', 'first_utm_campaign',
        'fbclid', 'gclid', 'landing_page', 'referrer', 'ip_hash', 'user_agent', 'consent', 'status', 'notes', 'assigned_to',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'consent' => 'boolean',
        ];
    }

    public function cluster(): BelongsTo
    {
        return $this->belongsTo(Cluster::class)->withTrashed();
    }

    public function houseType(): BelongsTo
    {
        return $this->belongsTo(HouseType::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }
}
