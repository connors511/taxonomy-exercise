<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Node extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'metadata' => 'array',
        'is_manager' => 'boolean',
    ];

    protected static function booted()
    {
        static::saving(function (self $node) {
            $node->height = ($node->parent?->height ?? -1) + 1;
        });

        static::saved(function (self $node) {
            if (! $node->wasChanged('height')) {
                return;
            }

            $node->children->each(function (self $child) use ($node) {
                $child->height = $node->height + 1;
                $child->save();
            });
        });

        static::deleted(function (self $node) {
            $node->children->each(function (self $child) use ($node) {
                $child->parent_id = $node->parent_id;
                $child->save();
            });
        });
    }

    public function parent(): HasOne
    {
        return $this->hasOne(self::class, 'id', 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
