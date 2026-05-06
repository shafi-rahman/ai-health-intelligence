<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShareableReport extends Model
{
    protected $fillable = [
        'user_id',
        'tenant_id',
        'token',
        'report_data',
        'expires_at',
    ];

    protected $casts = [
        'report_data' => 'array',
        'expires_at'  => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
