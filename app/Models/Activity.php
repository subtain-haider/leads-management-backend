<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;

class Activity extends Model
{
    use HasFactory, LogsActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'lead_id',
        'activity_type_id',
        'details',
        'activity_date',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activity_date' => 'datetime',
    ];

    /**
     * Get activity log options for the model.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['lead_id', 'activity_type_id', 'details', 'activity_date'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get the lead associated with the activity.
     */
    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    /**
     * Get the user who created the activity.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the activity type.
     */
    public function type(): BelongsTo
    {
        return $this->belongsTo(ActivityType::class, 'activity_type_id');
    }

    /**
     * Scope a query to only include activities of a specific type.
     */
    public function scopeOfType(Builder $query, int $typeId): Builder
    {
        return $query->where('activity_type_id', $typeId);
    }

    /**
     * Scope a query to only include activities within a date range.
     */
    public function scopeOccurredBetween(Builder $query, string $startDate, string $endDate): Builder
    {
        return $query->whereBetween('activity_date', [$startDate, $endDate]);
    }

    /**
     * Scope a query to only include activities created by a specific user.
     */
    public function scopeCreatedBy(Builder $query, int $userId): Builder
    {
        return $query->where('created_by', $userId);
    }
}