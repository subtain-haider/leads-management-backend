<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Country extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'phone_code',
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
     * Get all leads that have this country as nationality.
     */
    public function nationalityLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'nationality_id');
    }

    /**
     * Get all leads that have this country as residence.
     */
    public function residenceLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'residence_country_id');
    }

    /**
     * Get all leads that have this country as personal phone country.
     */
    public function personalPhoneLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'personal_phone_country_id');
    }

    /**
     * Get all leads that have this country as business phone country.
     */
    public function businessPhoneLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'business_phone_country_id');
    }

    /**
     * Get all leads that have this country as home phone country.
     */
    public function homePhoneLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'home_phone_country_id');
    }

    /**
     * Scope a query to only include active countries.
     */
    public function scopeActive($query)
    {
        return $query->where('active', true);
    }
}