<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    // Document categories
    const CATEGORY_GENERAL        = 'general';
    const CATEGORY_MEDICAL_REPORT = 'medical_report';
    const CATEGORY_PRESCRIPTION   = 'prescription';

    protected $fillable = [
        'tenant_id',
        'title',
        'type',
        'category',
        'source',
        'status',
        'chunk_count',
        'analysis_result',
        'error',
    ];

    protected $casts = [
        'analysis_result' => 'array',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function chunks()
    {
        return $this->hasMany(DocumentChunk::class);
    }
}
