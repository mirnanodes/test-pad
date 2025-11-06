<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'users';
    protected $primaryKey = 'user_id';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'role_id',
        'username',
        'email',
        'password',
        'name',
        'phone_number',
        'profile_pic',
        'status',
        'date_joined',
        'last_login',
    ];

    /**
     * The attributes that should be hidden.
     */
    protected $hidden = [
        'password',
    ];

    /**
     * Get the attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'date_joined' => 'date',
            'last_login' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Disable timestamps
     */
    public $timestamps = false;

    /**
     * Get the role of the user
     */
    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id', 'id');
    }

    /**
     * Get farms owned by this user (for Owner role)
     */
    public function ownedFarms()
    {
        return $this->hasMany(Farm::class, 'owner_id', 'user_id');
    }

    /**
     * Get farm assigned to peternak (for Peternak role)
     */
    public function assignedFarm()
    {
        return $this->hasOne(Farm::class, 'peternak_id', 'user_id');
    }

    /**
     * Get manual data entries by this user
     */
    public function manualDataEntries()
    {
        return $this->hasMany(ManualData::class, 'user_id_input', 'user_id');
    }

    /**
     * Get request logs by this user
     */
    public function requestLogs()
    {
        return $this->hasMany(RequestLog::class, 'user_id', 'user_id');
    }

    /**
     * Get sent notifications
     */
    public function sentNotifications()
    {
        return $this->hasMany(NotificationLog::class, 'sender_user_id', 'user_id');
    }

    /**
     * Get received notifications
     */
    public function receivedNotifications()
    {
        return $this->hasMany(NotificationLog::class, 'recipient_user_id', 'user_id');
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->role && $this->role->name === 'Admin';
    }

    /**
     * Check if user is owner
     */
    public function isOwner(): bool
    {
        return $this->role && $this->role->name === 'Owner';
    }

    /**
     * Check if user is peternak
     */
    public function isPeternak(): bool
    {
        return $this->role && $this->role->name === 'Peternak';
    }

    /**
     * Update last login timestamp
     */
    public function updateLastLogin()
    {
        $this->update(['last_login' => now()]);
    }

    /**
     * Scope to filter by role name
     */
    public function scopeByRoleName($query, $roleName)
    {
        return $query->whereHas('role', function ($q) use ($roleName) {
            $q->where('name', $roleName);
        });
    }

    /**
     * Scope to filter active users
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope to exclude admins
     */
    public function scopeExcludeAdmins($query)
    {
        return $query->whereHas('role', function ($q) {
            $q->where('name', '!=', 'Admin');
        });
    }

    /**
     * Get role name
     */
    public function getRoleNameAttribute()
    {
        return $this->role ? $this->role->name : null;
    }
}
