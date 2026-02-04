<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApiCallCronJob extends Model
{
    protected $fillable = [
        'endpoint',
        'method',
        'status_code',
        'error_message',
        'ip_address',
        'duration_ms'
    ];
    protected $casts = [
        'status_code' => 'integer',
        'duration_ms' => 'integer',
    ];
    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
    public function scopeSuccessful($query) {
        return $query->whereBetween('status_code', [200, 299]);
    }
    public function scopeFailed($query) {
        return $query->whereNotBetween('status_code', [200, 299]);
    }
    public function scopeLastMinutes($query, $minutes) {
        return $query->where('created_at', '>=', now()->subMinutes($minutes));
    }
}
