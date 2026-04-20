<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KnowledgeSource extends Model
{
    protected $fillable = [
        'tenant_id',
        'name',
        'file_path',
        'mime_type',
        'file_size',
        'status',
        'source_type',
        'dify_dataset_id',
        'dify_document_id',
        'dify_batch_id',
        'indexing_status',
        'error_message',
        'meta',
    ];

    protected $casts = [
        'meta' => 'array',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
