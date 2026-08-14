<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResultCorrection extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'lab_result_id',
        'corrected_by',
        'field',
        'previous_value',
        'new_value',
        'reason',
    ];

    public function labResult(): BelongsTo
    {
        return $this->belongsTo(LabResult::class, 'lab_result_id');
    }

    public function correctedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'corrected_by');
    }
}