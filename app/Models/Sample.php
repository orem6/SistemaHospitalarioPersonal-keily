<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sample extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'lab_order_id',
        'received_by',
        'barcode',
        'sample_type',
        'collected_at',
        'received_at',
        'status',
        'acceptance_status',
        'rejection_reason',
        'lab_test_id',
        'notes',
    ];

    protected $casts = [
        'collected_at' => 'datetime',
        'received_at' => 'datetime',
    ];

    public function labOrder(): BelongsTo
    {
        return $this->belongsTo(LabOrder::class, 'lab_order_id');
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    /** Prueba específica que materializa la muestra (etapa 2). */
    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
    }

    /** Versiones inmutables del resultado (etapa 2). */
    public function resultVersions(): HasMany
    {
        return $this->hasMany(LabResultVersion::class, 'sample_id');
    }
}