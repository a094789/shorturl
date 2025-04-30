<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // 創建您的帳號（管理員）
        User::create([
            'name' => 'wcyu',
            'email' => 'a094789@gmail.com',
            'password' => Hash::make('Wei891211@'),
            'is_admin' => true
        ]);

        // 創建測試用戶
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'is_admin' => false
        ]);
    }
}
