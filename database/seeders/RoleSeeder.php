<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'Admin',
            'Médico',
            'Enfermera',
            'TecnicoLab',
            'Recepcionista',
        ];

        foreach ($roles as $role) {
            Role::query()->firstOrCreate(
                ['name' => $role, 'guard_name' => 'api']
            );
        }
    }
}
