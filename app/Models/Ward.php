<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ward extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'name',
        'floor',
        'building',
    ];

    public function beds()
    {
        return $this->hasMany(Bed::class);
    }
}

