<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SoapNote extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'medical_record_id',
        'doctor_id',
        'admission_id',
        'subjective',
        'objective',
        'assessment',
        'plan',
        'electronic_sign',
        'signed_at',
    ];

    protected $casts = [
        'signed_at' => 'datetime',
    ];

    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }

    public function admission()
    {
        return $this->belongsTo(Admission::class);
    }
}

