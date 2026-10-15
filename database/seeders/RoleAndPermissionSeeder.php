<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            'content.view',
            'content.create',
            'content.edit',
            'content.review',
            'content.publish',
            'content.delete',
            'guide.verify',
            'vendor.verify',
            'booking.manage',
            'finance.manage',
            'settings.manage',
        ];

        foreach ($permissions as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        // Roles
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->givePermissionTo(Permission::all());

        $contentAdmin = Role::firstOrCreate(['name' => 'content_admin', 'guard_name' => 'web']);
        $contentAdmin->givePermissionTo([
            'content.view', 'content.create', 'content.edit',
        ]);

        $contentApprover = Role::firstOrCreate(['name' => 'content_approver', 'guard_name' => 'web']);
        $contentApprover->givePermissionTo([
            'content.view', 'content.edit', 'content.review', 'content.publish',
        ]);

        Role::firstOrCreate(['name' => 'customer', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'tour_guide', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'vendor', 'guard_name' => 'web']);
    }
}
