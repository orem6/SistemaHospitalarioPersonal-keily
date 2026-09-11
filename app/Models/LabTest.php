<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabTest extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'category',
        'unit',
        'reference_min',
        'reference_max',
        'critical_min',
        'critical_max',
        'turnaround_min',
        'active',
    ];

    protected $casts = [
        'reference_min' => 'decimal:4',
        'reference_max' => 'decimal:4',
        'critical_min' => 'decimal:4',
        'critical_max' => 'decimal:4',
        'turnaround_min' => 'integer',
        'active' => 'boolean',
    ];

    public function orderItems(): HasMany
    {
        return $this->hasMany(LabOrderItem::class, 'lab_test_id');
    }
}