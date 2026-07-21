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
        \Illuminate\Support\Facades\DB::table('users')->where('email', 'suciameliana19@gmail.com')->update(['role' => 'administrator']);
        
        $admin = \Illuminate\Support\Facades\DB::table('users')->where('email', 'suciameliana19@gmail.com')->first();
        if ($admin) {
            \Illuminate\Support\Facades\DB::table('profiles')->where('user_id', $admin->id)->update(['role' => 'administrator']);
        }

        // Create the new User user
        $user = \Illuminate\Support\Facades\DB::table('users')->where('email', 'user@gmail.com')->first();
        
        if (!$user) {
            $userId = \Illuminate\Support\Facades\DB::table('users')->insertGetId([
                'name' => 'Pengguna',
                'email' => 'user@gmail.com',
                'password' => Hash::make('12345'),
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            \Illuminate\Support\Facades\DB::table('profiles')->insert([
                'user_id' => $userId,
                'full_name' => 'Pengguna',
                'role' => 'user',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } else {
            \Illuminate\Support\Facades\DB::table('users')->where('id', $user->id)->update(['role' => 'user']);
            
            $profile = \Illuminate\Support\Facades\DB::table('profiles')->where('user_id', $user->id)->first();
            if ($profile) {
                \Illuminate\Support\Facades\DB::table('profiles')->where('user_id', $user->id)->update(['role' => 'user']);
            } else {
                \Illuminate\Support\Facades\DB::table('profiles')->insert([
                    'user_id' => $user->id,
                    'full_name' => $user->name,
                    'role' => 'user',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
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
