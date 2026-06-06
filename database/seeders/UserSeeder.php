<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Admin User
        
        // Create Doctor Users
        User::create([
            'name' => 'Dr. Ahmed Ben Ali',
            'email' => 'ahmed.benali@example.com',
            'password' => Hash::make('password'),
            'role_id' => 2, // Doctor role
            'status' => 'active',
            'phone' => '+216 23456789',
            'address' => '456 Medical Center, Sfax, Tunisia',
            'specialization' => 'Orthodontics',
            'license_number' => 'DOC001',
            'bio' => 'Experienced orthodontist with 10+ years of practice.',
        ]);

        User::create([
            'name' => 'Dr. Fatima Mansouri',
            'email' => 'fatima.mansouri@example.com',
            'password' => Hash::make('password'),
            'role_id' => 2, // Doctor role
            'status' => 'active',
            'phone' => '+216 34567890',
            'address' => '789 Dental Clinic, Sousse, Tunisia',
            'specialization' => 'Endodontics',
            'license_number' => 'DOC002',
            'bio' => 'Specialized in root canal treatments and endodontic procedures.',
        ]);

        User::create([
            'name' => 'Dr. Mohamed Trabelsi',
            'email' => 'mohamed.trabelsi@example.com',
            'password' => Hash::make('password'),
            'role_id' => 2, // Doctor role
            'status' => 'active',
            'phone' => '+216 45678901',
            'address' => '321 Dental Practice, Monastir, Tunisia',
            'specialization' => 'Periodontics',
            'license_number' => 'DOC003',
            'bio' => 'Expert in gum disease treatment and dental implants.',
        ]);

        // Create Technician Users
        User::create([
            'name' => 'Technician Sami',
            'email' => 'sami.technician@example.com',
            'password' => Hash::make('password'),
            'role_id' => 3, // Technician role
            'status' => 'active',
            'phone' => '+216 56789012',
            'address' => '654 Lab Street, Tunis, Tunisia',
            'specialization' => 'Dental Lab Technician',
            'license_number' => 'TECH001',
            'bio' => 'Skilled dental laboratory technician specializing in crowns and bridges.',
        ]);

        User::create([
            'name' => 'Technician Leila',
            'email' => 'leila.technician@example.com',
            'password' => Hash::make('password'),
            'role_id' => 3, // Technician role
            'status' => 'active',
            'phone' => '+216 67890123',
            'address' => '987 Lab Avenue, Sfax, Tunisia',
            'specialization' => 'Prosthetic Technician',
            'license_number' => 'TECH002',
            'bio' => 'Expert in creating dental prosthetics and dentures.',
        ]);

        // Create Laboratory Users
        User::create([
            'name' => 'Laboratory Alpha',
            'email' => 'alpha.lab@example.com',
            'password' => Hash::make('password'),
            'role_id' => 4, // Laboratory role
            'status' => 'active',
            'phone' => '+216 78901234',
            'address' => '147 Lab Center, Tunis, Tunisia',
            'specialization' => 'Dental Laboratory',
            'license_number' => 'LAB001',
            'bio' => 'Professional dental laboratory providing high-quality dental work.',
        ]);

        User::create([
            'name' => 'Laboratory Beta',
            'email' => 'beta.lab@example.com',
            'password' => Hash::make('password'),
            'role_id' => 4, // Laboratory role
            'status' => 'active',
            'phone' => '+216 89012345',
            'address' => '258 Lab Complex, Sousse, Tunisia',
            'specialization' => 'Advanced Dental Laboratory',
            'license_number' => 'LAB002',
            'bio' => 'Advanced dental laboratory with cutting-edge technology.',
        ]);

        // Create some inactive users for testing
        User::create([
            'name' => 'Dr. Inactive Doctor',
            'email' => 'inactive.doctor@example.com',
            'password' => Hash::make('password'),
            'role_id' => 2, // Doctor role
            'status' => 'inactive',
            'phone' => '+216 90123456',
            'address' => '369 Inactive Street, Tunis, Tunisia',
            'specialization' => 'General Dentistry',
            'license_number' => 'DOC004',
            'bio' => 'Inactive doctor account for testing purposes.',
        ]);

        $this->command->info('Sample users created successfully!');
        $this->command->info('Admin: admin@example.com / password');
        $this->command->info('Doctors: ahmed.benali@example.com, fatima.mansouri@example.com, mohamed.trabelsi@example.com / password');
        $this->command->info('Technicians: sami.technician@example.com, leila.technician@example.com / password');
        $this->command->info('Laboratories: alpha.lab@example.com, beta.lab@example.com / password');
    }
}
