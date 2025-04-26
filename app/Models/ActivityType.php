<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ActivityType extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'icon',
        'color',
        'active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'active' => 'boolean'
    ];

    /**
     * Get all activities of this type.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'activity_type_id');
    }

    /**
     * Scope a query to only include active activity types.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}