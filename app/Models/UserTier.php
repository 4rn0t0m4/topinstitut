<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTier extends Model
{
    protected $fillable = ['nom', 'min_avis', 'max_avis'];
}
