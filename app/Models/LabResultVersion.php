<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Versión inmutable de un resultado de laboratorio (ASII-19 etapa 2).
 *
 * Las filas de esta tabla son append-only: una corrección INSERTA
 * una fila nueva con version_number + 1; nunca se actualizan ni
 * eliminan versiones previas.
 */
class LabResultVersion extends Model
{
    /** @use HasFactory<\Database\Factories\LabResultVersionFactory> */
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'sample_id',
        'lab_test_id',
        'entered_by',
        'version_number',
        'result_type',
        'numeric_value',
        'text_value',
        'unit',
        'is_abnormal',
        'is_critical',
        'corrected_from_version',
        'correction_reason',
        'resulted_at',
    ];

    protected $casts = [
        'numeric_value' => 'float',
        'is_abnormal' => 'boolean',
        'is_critical' => 'boolean',
        'corrected_from_version' => 'integer',
        'resulted_at' => 'datetime',
    ];

    public function sample(): BelongsTo
    {
        return $this->belongsTo(Sample::class, 'sample_id');
    }

    public function labTest(): BelongsTo
    {
        return $this->belongsTo(LabTest::class, 'lab_test_id');
    }
}
