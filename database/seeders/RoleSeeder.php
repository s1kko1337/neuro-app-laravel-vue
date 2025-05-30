<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
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

        // Загружаем данные из файла
        $filePath = '/ollama/surveys.json';
        $interests = [];

        if (Storage::disk('local')->exists($filePath)) {
            $json = Storage::disk('local')->get($filePath);
            $interests = json_decode($json, true);
        }

// Если файл не загружен, используем демо-данные
        if (empty($interests)) {
            $interests = [
                "Программирование на Python",
                "Игра на гитаре",
                "Стриминг в Twitch",
                "Фотография",
                "Кулинария",
                "Рисование",
                "Танцы",
                "Иностранные языки",
                "Путешествия",
                "Фитнес"
            ];
        }

// Создаем случайные группы
        $groups = [];
        $count = min(10, count($interests)); // Максимум 10 групп

        for ($i = 0; $i < $count; $i++) {
            $randomIndex = array_rand($interests);
            $name = $interests[$randomIndex];

            // Удаляем выбранный элемент, чтобы избежать дублирования
            unset($interests[$randomIndex]);

            // Получаем 15 случайных элементов для параметров
            $randomParameters = array_rand($interests, min(10, count($interests)));
            $parameters = [];
            foreach ($randomParameters as $index) {
                $parameters[] = $interests[$index];
            }

            $groups[] = [
                'name' => $name,
                'parameters' => implode(', ', $parameters), // Преобразуем массив в строку
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        DB::table('survey_groups')->insert($groups);

    }
}
