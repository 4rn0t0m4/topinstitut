<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'establishment_id', 'type', 'email', 'name', 'phone', 'content',
        'requested_date', 'requested_time', 'requested_service', 'handled_at',
    ];

    protected function casts(): array
    {
        return [
            'requested_date' => 'date',
            'handled_at' => 'datetime',
        ];
    }

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }
}
