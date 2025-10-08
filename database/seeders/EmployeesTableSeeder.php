<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeesTableSeeder extends Seeder
{
    public function run(): void
    {
        $employees = [
            [
                'firstName' => 'John',
                'middleName' => 'L.',
                'lastName' => 'Cruz',
                'username' => 'johndoe',
                'password' => Hash::make('password123'),
                'role' => 'Owner',
            ],
            [
                'firstName' => 'Maria',
                'middleName' => 'D.',
                'lastName' => 'Rosa',
                'username' => 'maria.rosa',
                'password' => Hash::make('password123'),
                'role' => 'Pharmacist',
            ],
            [
                'firstName' => 'Ramon',
                'middleName' => null,
                'lastName' => 'Santos',
                'username' => 'ramon.santos',
                'password' => Hash::make('password123'),
                'role' => 'Staff',
            ],
            [
                'firstName' => 'Anna',
                'middleName' => 'T.',
                'lastName' => 'Lopez',
                'username' => 'anna.lopez',
                'password' => Hash::make('password123'),
                'role' => 'Staff',
            ],
        ];

        foreach ($employees as &$emp) {
            $emp['created_at'] = now();
            $emp['updated_at'] = now();
        }

        DB::table('employees')->insert($employees);
    }
}
