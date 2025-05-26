<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $basicRole = Role::create(['name' => 'user']);

        $basicPermission = Permission::create(['name' => 'use-Api', 'guard_name' => 'web']);

        $basicRole->givePermissionTo($basicPermission);
    }
}
