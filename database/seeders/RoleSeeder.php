<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
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


        // Создаем или обновляем пользователя
        $user = User::firstOrCreate(
            ['email' => 'sikko4890@gmail.com'],
            [
                'name' => 'Admin',
                'surname' => 'User',
                'password' => Hash::make('sikko4890@gmail.com'),
                'email_verified_at' => now(),
            ]
        );

        // Назначаем роль
        $role = Role::findByName('user');
        $user->assignRole('user');

        // Удаляем старые токены и создаем новый
        $user->tokens()->delete();
        $permissions = $user->getPermissionsViaRoles()->pluck('name')->toArray();

        $token = $user->createToken(
            name: "Seeder Token for {$user->email}",
            abilities: $permissions
        )->plainTextToken;

        $this->command->info("User created:");
        $this->command->line("Email: sikko4890@gmail.com");
        $this->command->line("Password: sikko4890@gmail.com");
        $this->command->line("Token: $token");
    }
}
