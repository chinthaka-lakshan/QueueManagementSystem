<?php

namespace Database\Seeders;

use App\Models\ClinicSession;
use App\Models\Patient;
use App\Models\QueueToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Doctor
        $doctor = User::create([
            'name' => 'Dr. Samantha Perera',
            'email' => 'doctor@clinic.lk',
            'password' => Hash::make('password'),
            'role' => 'doctor',
            'room_number' => 'Room 01',
            'specialization' => 'Consultant Physician & Cardiologist',
            'phone' => '0771234567',
            'is_active' => true,
        ]);

        // 2. Cashier
        $cashier = User::create([
            'name' => 'Kumari Silva (Reception / Cashier)',
            'email' => 'cashier@clinic.lk',
            'password' => Hash::make('password'),
            'role' => 'cashier',
            'room_number' => 'Counter 01',
            'phone' => '0719876543',
            'is_active' => true,
        ]);

        // 3. Admin
        User::create([
            'name' => 'Clinic Admin',
            'email' => 'admin@clinic.lk',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_active' => true,
        ]);

        // 4. Today's Clinic Session for Doctor
        $session = ClinicSession::create([
            'doctor_id' => $doctor->id,
            'session_date' => Carbon::today(),
            'title' => 'Evening OPD Consultation',
            'room_number' => 'Room 01',
            'status' => 'active',
            'max_tokens' => 50,
            'started_at' => Carbon::now()->subHours(1),
        ]);

        // 5. Sample Patients
        $patientsData = [
            ['name' => 'Nimal Jayasinghe', 'phone' => '0778899111', 'nic' => '851234567V', 'age' => 42, 'gender' => 'Male'],
            ['name' => 'Sanduni Fernando', 'phone' => '0712233444', 'nic' => '925678123V', 'age' => 31, 'gender' => 'Female'],
            ['name' => 'Kamal Wijesinghe', 'phone' => '0765544332', 'nic' => '781928374V', 'age' => 55, 'gender' => 'Male'],
            ['name' => 'Anoma Rathnayake', 'phone' => '0701122334', 'nic' => '896541230V', 'age' => 36, 'gender' => 'Female'],
            ['name' => 'Kavindu Bandara', 'phone' => '0759988776', 'nic' => '992345678V', 'age' => 24, 'gender' => 'Male'],
            ['name' => 'Sunil Dharmadasa', 'phone' => '0724455667', 'nic' => '651239874V', 'age' => 62, 'gender' => 'Male'],
        ];

        $tokensStatuses = [
            ['token' => 1, 'type' => 'walk_in', 'status' => 'completed'],
            ['token' => 2, 'type' => 'online',  'status' => 'completed'],
            ['token' => 3, 'type' => 'walk_in', 'status' => 'serving', 'called_at' => Carbon::now()->subMinutes(3)],
            ['token' => 4, 'type' => 'online',  'status' => 'waiting'],
            ['token' => 5, 'type' => 'walk_in', 'status' => 'waiting'],
            ['token' => 6, 'type' => 'walk_in', 'status' => 'waiting'],
        ];

        foreach ($patientsData as $index => $pData) {
            $patient = Patient::create($pData);
            $tokenInfo = $tokensStatuses[$index];

            QueueToken::create([
                'session_id' => $session->id,
                'doctor_id' => $doctor->id,
                'patient_id' => $patient->id,
                'token_number' => $tokenInfo['token'],
                'type' => $tokenInfo['type'],
                'status' => $tokenInfo['status'],
                'called_at' => $tokenInfo['called_at'] ?? ($tokenInfo['status'] === 'completed' ? Carbon::now()->subMinutes(20 - ($index * 10)) : null),
                'served_at' => $tokenInfo['status'] === 'serving' ? Carbon::now()->subMinutes(3) : ($tokenInfo['status'] === 'completed' ? Carbon::now()->subMinutes(18 - ($index * 10)) : null),
                'completed_at' => $tokenInfo['status'] === 'completed' ? Carbon::now()->subMinutes(10 - ($index * 10)) : null,
            ]);
        }
    }
}
