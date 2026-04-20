<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // First Run Essential Seeders
        $this->call([
            RoleSeeder::class,
            ParcelStatusSeeder::class,
        ]);

        // Seed the current development user
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => \Illuminate\Support\Facades\Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        // Also ensure the admin@smartstore.com user from RoleSeeder is available
        // as it might be used for sender/admin processing
    }
}
