<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('role')->default('user')->after('password');
            });
        }

        // Set existing Admin user role to administrator
        $admin = User::where('email', 'suciameliana19@gmail.com')->first();
        if ($admin) {
            $admin->update(['role' => 'administrator']);
            if ($admin->profile) {
                $admin->profile->update(['role' => 'administrator']);
            }
        }

        // Create the new User user
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'role')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('role');
            });
        }
    }
};
