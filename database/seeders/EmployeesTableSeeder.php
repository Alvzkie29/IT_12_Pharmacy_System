<?php

namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EmployeesTableSeeder extends Seeder
{
    public function run()
    {
        DB::table('employees')->insert([
            [
                'firstName'   => 'John',
                'middleName'  => 'A.',
                'lastName'    => 'Doe',
                'username'    => 'johndoe',
                'password'    => Hash::make('password123'),
                'role'        => 'Owner',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'firstName'   => 'Jane',
                'middleName'  => null,
                'lastName'    => 'Smith',
                'username'    => 'janesmith',
                'password'    => Hash::make('password123'),
                'role'        => 'Pharmacist',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
            [
                'firstName'   => 'Mike',
                'middleName'  => 'B.',
                'lastName'    => 'Johnson',
                'username'    => 'mikejohnson',
                'password'    => Hash::make('password123'),
                'role'        => 'Staff',
                'created_at'  => now(),
                'updated_at'  => now(),
            ],
        ]);
    
    }
}
