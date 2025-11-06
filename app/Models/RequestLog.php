<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RequestLog extends Model
{
    use HasFactory;

    protected $table = 'request_log';
    protected $primaryKey = 'request_id';
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'sender_name',
        'request_type',
        'request_content',
        'status',
        'sent_time',
    ];

    protected $casts = [
        'sent_time' => 'datetime',
    ];

    /**
     * Status constants
     */
    const STATUS_PENDING = 'menunggu';
    const STATUS_PROCESSING = 'diproses';
    const STATUS_COMPLETED = 'selesai';
    const STATUS_REJECTED = 'ditolak';

    /**
     * Request type constants
     */
    const TYPE_NEW_ACCOUNT = 'akun_baru';
    const TYPE_ADD_FARM = 'tambah_kandang';
    const TYPE_EDIT_ACCOUNT = 'edit_akun';
    const TYPE_OTHER = 'lainnya';

    /**
     * Get the user who made the request
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    /**
     * Scope to filter by status
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending requests
     */
    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Scope to get processing requests
     */
    public function scopeProcessing($query)
    {
        return $query->where('status', self::STATUS_PROCESSING);
    }

    /**
     * Scope to get completed requests
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope to order by newest first
     */
    public function scopeNewest($query)
    {
        return $query->orderBy('sent_time', 'desc');
    }

    /**
     * Scope to order by oldest first
     */
    public function scopeOldest($query)
    {
        return $query->orderBy('sent_time', 'asc');
    }

    /**
     * Scope to filter by request type
     */
    public function scopeByType($query, $type)
    {
        return $query->where('request_type', $type);
    }

    /**
     * Mark request as processing
     */
    public function markAsProcessing()
    {
        $this->update(['status' => self::STATUS_PROCESSING]);
    }

    /**
     * Mark request as completed
     */
    public function markAsCompleted()
    {
        $this->update(['status' => self::STATUS_COMPLETED]);
    }

    /**
     * Mark request as rejected
     */
    public function markAsRejected()
    {
        $this->update(['status' => self::STATUS_REJECTED]);
    }

    /**
     * Get status color
     */
    public function getStatusColor()
    {
        return match($this->status) {
            self::STATUS_PENDING => 'yellow',
            self::STATUS_PROCESSING => 'blue',
            self::STATUS_COMPLETED => 'green',
            self::STATUS_REJECTED => 'red',
            default => 'gray',
        };
    }

    /**
     * Get request type label
     */
    public function getRequestTypeLabel()
    {
        return match($this->request_type) {
            self::TYPE_NEW_ACCOUNT => 'Akun Baru',
            self::TYPE_ADD_FARM => 'Tambah Kandang',
            self::TYPE_EDIT_ACCOUNT => 'Edit Akun',
            self::TYPE_OTHER => 'Lainnya',
            default => $this->request_type,
        };
    }

    /**
     * Get formatted display
     */
    public function getFormattedDisplay()
    {
        return [
            'request_id' => $this->request_id,
            'sender_name' => $this->sender_name ?? 'Guest',
            'request_type' => $this->getRequestTypeLabel(),
            'request_content' => $this->request_content,
            'status' => $this->status,
            'status_color' => $this->getStatusColor(),
            'sent_time' => $this->sent_time->format('d M Y H:i'),
            'user' => $this->user ? [
                'user_id' => $this->user->user_id,
                'username' => $this->user->username,
                'role' => $this->user->role_name,
            ] : null,
        ];
    }
}
