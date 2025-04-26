<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Lead extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'personal_phone_country_id',
        'personal_phone',
        'description',
        'address',
        'business_phone_country_id',
        'business_phone',
        'home_phone_country_id',
        'home_phone',
        'nationality_id',
        'residence_country_id',
        'dob',
        'gender',
        'status_id',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'dob' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    /**
     * Get activity log options for the model.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'first_name', 
                'last_name', 
                'email', 
                'personal_phone', 
                'status_id', 
                'nationality_id', 
                'residence_country_id'
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the user who created the lead.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the user who last updated the lead.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    /**
     * Get the status of the lead.
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(LeadStatus::class, 'status_id');
    }

    /**
     * Get the nationality country of the lead.
     */
    public function nationality(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'nationality_id');
    }

    /**
     * Get the residence country of the lead.
     */
    public function residenceCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'residence_country_id');
    }

    /**
     * Get the personal phone country of the lead.
     */
    public function personalPhoneCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'personal_phone_country_id');
    }

    /**
     * Get the business phone country of the lead.
     */
    public function businessPhoneCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'business_phone_country_id');
    }

    /**
     * Get the home phone country of the lead.
     */
    public function homePhoneCountry(): BelongsTo
    {
        return $this->belongsTo(Country::class, 'home_phone_country_id');
    }

    /**
     * Get the lead's full name.
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Get the lead's full personal phone with country code.
     */
    public function getFullPersonalPhoneAttribute(): ?string
    {
        if (empty($this->personal_phone)) {
            return null;
        }
        
        return $this->personalPhoneCountry && $this->personalPhoneCountry->phone_code 
            ? "{$this->personalPhoneCountry->phone_code} {$this->personal_phone}" 
            : $this->personal_phone;
    }

    /**
     * Get the lead's full business phone with country code.
     */
    public function getFullBusinessPhoneAttribute(): ?string
    {
        if (empty($this->business_phone)) {
            return null;
        }
        
        return $this->businessPhoneCountry && $this->businessPhoneCountry->phone_code 
            ? "{$this->businessPhoneCountry->phone_code} {$this->business_phone}" 
            : $this->business_phone;
    }

    /**
     * Get the lead's full home phone with country code.
     */
    public function getFullHomePhoneAttribute(): ?string
    {
        if (empty($this->home_phone)) {
            return null;
        }
        
        return $this->homePhoneCountry && $this->homePhoneCountry->phone_code 
            ? "{$this->homePhoneCountry->phone_code} {$this->home_phone}" 
            : $this->home_phone;
    }

    /**
     * Get all the lead's sources.
     */
    public function sources(): BelongsToMany
    {
        return $this->belongsToMany(LeadSource::class, 'lead_lead_source')
            ->withPivot('associated_at')
            ->withTimestamps();
    }

    /**
     * Get all the lead's tags.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class)
            ->withTimestamps();
    }

    /**
     * Get all notes for the lead.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class);
    }

    /**
     * Get all activities for the lead.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class);
    }

    /**
     * Scope a query to only include leads with a specific status.
     */
    public function scopeWithStatus(Builder $query, int $statusId): Builder
    {
        return $query->where('status_id', $statusId);
    }

    /**
     * Scope a query to only include leads created in a given date range.
     */
    public function scopeCreatedBetween(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Scope a query to search leads by name, email or phone.
     * 
     * This is a basic search scope. For millions of records, consider moving to
     * a dedicated search service like Elasticsearch or Meilisearch.
     */
    public function scopeSearch(Builder $query, string $searchTerm): Builder
    {
        return $query->where(function ($query) use ($searchTerm) {
            $query->where('first_name', 'like', "%{$searchTerm}%")
                ->orWhere('last_name', 'like', "%{$searchTerm}%")
                ->orWhere('email', 'like', "%{$searchTerm}%")
                ->orWhere('personal_phone', 'like', "%{$searchTerm}%");
        });
    }

    /**
     * Scope a query to include leads with specific tags.
     */
    public function scopeHasTags(Builder $query, array $tagIds): Builder
    {
        return $query->whereHas('tags', function ($query) use ($tagIds) {
            $query->whereIn('tags.id', $tagIds);
        });
    }

    /**
     * Scope a query to include leads with specific sources.
     */
    public function scopeFromSources(Builder $query, array $sourceIds): Builder
    {
        return $query->whereHas('sources', function ($query) use ($sourceIds) {
            $query->whereIn('lead_sources.id', $sourceIds);
        });
    }

    /**
     * Scope a query to only include leads with a given nationality.
     */
    public function scopeWithNationality(Builder $query, int $countryId): Builder
    {
        return $query->where('nationality_id', $countryId);
    }

    /**
     * Scope a query to only include leads with a given country of residence.
     */
    public function scopeResidingIn(Builder $query, int $countryId): Builder
    {
        return $query->where('residence_country_id', $countryId);
    }

    /**
     * Scope a query to only include leads created by a specific user.
     */
    public function scopeCreatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }

    /**
     * Scope a query to only include leads with a specific gender.
     */
    public function scopeWithGender(Builder $query, string $gender): Builder
    {
        return $query->where('gender', $gender);
    }
}