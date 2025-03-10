<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Species extends Model
{
    protected $fillable = ['name'];

    public function treatments(): HasMany
    {
        return $this->hasMany(Treatment::class);
    }
}
