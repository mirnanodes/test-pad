<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationLog extends Model
{
    use HasFactory;

    protected $table = 'notification_log';
    protected $primaryKey = 'notif_id';
    public $timestamps = false;

    protected $fillable = [
        'sender_user_id',
        'recipient_user_id',
        'farm_id',
        'notification_type',
        'message_content',
        'sent_at',
        'status',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    /**
     * Get the sender user
     */
    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_user_id', 'user_id');
    }

    /**
     * Get the recipient user
     */
    public function recipient()
    {
        return $this->belongsTo(User::class, 'recipient_user_id', 'user_id');
    }

    /**
     * Get the related farm
     */
    public function farm()
    {
        return $this->belongsTo(Farm::class, 'farm_id', 'farm_id');
    }

    /**
     * Scope to filter by recipient
     */
    public function scopeForRecipient($query, $userId)
    {
        return $query->where('recipient_user_id', $userId);
    }

    /**
     * Scope to filter by farm
     */
    public function scopeForFarm($query, $farmId)
    {
        return $query->where('farm_id', $farmId);
    }

    /**
     * Scope to get recent notifications
     */
    public function scopeRecent($query, $days = 7)
    {
        return $query->where('sent_at', '>=', now()->subDays($days));
    }

    /**
     * Scope to order by newest first
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('sent_at', 'desc');
    }
}
