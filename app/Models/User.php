<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\CausesActivity;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, LogsActivity, CausesActivity;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Get activity log options for the model.
     *
     * @return LogOptions
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /**
     * Get all leads created by this user.
     */
    public function createdLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'created_by');
    }

    /**
     * Get all leads updated by this user.
     */
    public function updatedLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'updated_by');
    }

    /**
     * Get all notes created by this user.
     */
    public function notes(): HasMany
    {
        return $this->hasMany(Note::class, 'created_by');
    }

    /**
     * Get all activities created by this user.
     */
    public function activities(): HasMany
    {
        return $this->hasMany(Activity::class, 'created_by');
    }
}