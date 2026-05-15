<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $email = 'superadmin@talksy.com';

        // Only create if doesn't exist yet
        if (!User::where('email', $email)->exists()) {
            User::create([
                'name'     => 'Super Admin',
                'email'    => $email,
                'password' => Hash::make('superadmin123'),
                'role'     => 'admin',
            ]);
        }
    }
}

// php artisan migrate:fresh --seed