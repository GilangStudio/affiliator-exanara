<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        User::create([
            'name' => 'Superadmin',
            'email' => 'superadmin@example.com',
            'username' => 'superadmin',
            'country_code' => '+62',
            'phone' => '81234567890',
            'password' => bcrypt('superadmin'),
            'role' => 'superadmin',
            'is_active' => true,
        ]);

        //call FaqSeeder
        $this->call(FaqSeeder::class);
        //call SystemSettingSeeder
        $this->call(SystemSettingsSeeder::class);
        
    }
}
