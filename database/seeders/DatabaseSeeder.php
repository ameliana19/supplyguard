<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Membuat akun admin
        $admin = User::firstOrCreate(
            ['email' => 'suciameliana19@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('12345'),
                'role' => 'administrator',
            ]
        );
        if ($admin->profile) {
            $admin->profile->update(['role' => 'administrator']);
        } else {
            $admin->profile()->create([
                'full_name' => 'Admin',
                'role' => 'administrator'
            ]);
        }

        // Membuat akun user
        $user = User::firstOrCreate(
            ['email' => 'user@gmail.com'],
            [
                'name' => 'Pengguna',
                'password' => Hash::make('12345'),
                'role' => 'user',
            ]
        );
        if ($user->profile) {
            $user->profile->update(['role' => 'user']);
        } else {
            $user->profile()->create([
                'full_name' => 'Pengguna',
                'role' => 'user'
            ]);
        }

        // Menjalankan DemoDataSeeder
        $this->call(DemoDataSeeder::class);
    }
}