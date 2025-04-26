<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LeadStatus extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'color',
        'order',
        'active'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'order' => 'integer',
        'active' => 'boolean'
    ];

    /**
     * Get all leads with this status.
     */
    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'status_id');
    }

    /**
     * Scope a query to only include active statuses.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Scope a query to order by position.
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }
}