<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::firstOrCreate([
            'email' => 'admin@turget.id',
        ], [
            'name' => 'Super Administrator Turget',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles(['super_admin']);

        $editor = User::firstOrCreate([
            'email' => 'editor@turget.id',
        ], [
            'name' => 'Budi Santoso (Content Admin)',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $editor->syncRoles(['content_admin']);

        $approver = User::firstOrCreate([
            'email' => 'approver@turget.id',
        ], [
            'name' => 'Siti Rahma (Editorial Lead)',
            'password' => Hash::make('password'),
            'locale' => 'id',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $approver->syncRoles(['content_approver']);

        $traveler = User::firstOrCreate([
            'email' => 'traveler@turget.id',
        ], [
            'name' => 'Sarah Johnson (Traveler)',
            'password' => Hash::make('password'),
            'locale' => 'en',
            'status' => 'active',
            'email_verified_at' => now(),
        ]);
        $traveler->syncRoles(['customer']);
    }
}
