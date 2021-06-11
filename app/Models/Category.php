<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Category extends Model
{
    use HasFactory;

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function attribute_groups(): BelongsToMany
    {
        return $this->belongsToMany(AttributeGroup::class);
    }
}
