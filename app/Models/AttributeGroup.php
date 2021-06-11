<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AttributeGroup extends Model
{
    use HasFactory;

    public function attributes(): HasMany
    {
        return $this->hasMany(Attribute::class, 'group_id');
    }
}
