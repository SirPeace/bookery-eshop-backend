<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductCategory extends Model
{
    use HasFactory;

    public function parent_category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class);
    }
}
