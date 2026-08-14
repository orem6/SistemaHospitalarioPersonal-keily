<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabResult extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'lab_order_item_id',
        'sample_id',
        'entered_by',
        'validated_by',
        'numeric_value',
        'text_value',
        'is_critical',
        'is_abnormal',
        'resulted_at',
        'validated_at',
        'sent_to_emr',
        'sent_to_emr_at',
        'published_at',
        'published_by',
    ];

    protected $casts = [
        'numeric_value' => 'decimal:4',
        'is_critical' => 'boolean',
        'is_abnormal' => 'boolean',
        'sent_to_emr' => 'boolean',
        'resulted_at' => 'datetime',
        'validated_at' => 'datetime',
        'sent_to_emr_at' => 'datetime',
        'published_at' => 'datetime',
    ];

    public function enteredBy()
    {
        return $this->belongsTo(User::class, 'entered_by');
    }

    public function validatedBy()
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function publishedBy()
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function labOrderItem(): BelongsTo
    {
        return $this->belongsTo(LabOrderItem::class, 'lab_order_item_id');
    }

    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class, 'sample_id');
    }

    public function corrections(): HasMany
    {
        return $this->hasMany(LabResultCorrection::class, 'lab_result_id');
    }

    public function isPublished(): bool
    {
        return $this->published_at !== null;
    }
}
