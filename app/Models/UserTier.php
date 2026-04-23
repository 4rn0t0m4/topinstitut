<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserTier extends Model
{
    protected $fillable = ['name', 'min_reviews', 'max_reviews'];
}
