<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'view',
            'enter',
            'upload',
            'submit',
            'verify',
            'approve',
            'override',
            'manage indicators',
            'manage users',
            'view restricted',
            'run reports',
        ];

        foreach ($permissions as $permission) {
            Permission::findOrCreate($permission, 'web');
        }

        $roles = [
            'FPMU M&E Specialist' => ['view', 'enter', 'upload', 'submit', 'approve', 'override', 'manage indicators', 'run reports'],
            'FPMU category owner' => ['view', 'enter', 'upload', 'submit', 'run reports'],
            'National Coordinator' => ['view', 'approve', 'run reports'],
            'World Bank' => ['view', 'run reports'],
            'RBDA data manager' => ['view', 'enter', 'upload', 'submit', 'verify', 'run reports'],
            'SPIU M&E Officer' => ['view', 'enter', 'upload', 'submit', 'verify', 'run reports'],
            'state category staff' => ['view', 'enter', 'upload', 'submit', 'run reports'],
            'SEA/SH Focal Person' => ['view', 'enter', 'view restricted', 'run reports'],
            'Administrator' => ['view', 'manage users', 'manage indicators'],
        ];

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::findOrCreate($roleName, 'web');
            $role->syncPermissions($rolePermissions);
        }
    }
}
