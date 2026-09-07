<?php

namespace App\Console\Commands;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

#[Signature('app:create-admin')]
#[Description('Creates the default admin user and restaurant record for a fresh installation.')]
class CreateAdmin extends Command
{
    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Create default restaurant record if none exists
        if (!Restaurant::exists()) {
            Restaurant::create([
                'name' => 'My Restaurant',
                'is_active' => true,
            ]);
            $this->info('Default restaurant record created.');
        }

        // Create default admin user if none exists
        if (User::count() === 0) {
            User::create([
                'name' => 'Admin',
                'email' => 'admin@drestro.com',
                'password' => Hash::make('admin123'),
                'email_verified_at' => now(),
            ]);
            $this->info('Default admin user created.');
            $this->info('  Email:    admin@drestro.com');
            $this->info('  Password: admin123');
            $this->warn('  ⚠️  Please change this password after first login!');
        } else {
            $this->info('Admin user already exists, skipping.');
        }
    }
}
