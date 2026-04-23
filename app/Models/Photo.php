<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = ['establishment_id', 'filename', 'sort_order'];

    public function establishment(): BelongsTo
    {
        return $this->belongsTo(Establishment::class);
    }

    protected function url(): Attribute
    {
        return Attribute::get(fn () => rtrim(config('filesystems.disks.r2.url'), '/')
            . "/etablissements/{$this->establishment_id}/{$this->filename}");
    }
}
