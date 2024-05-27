<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('users')->insert([
            [
                'name' => 'Nguyễn Thế Hướng',
                'email' => 'huongnt.c18@gmail.com',
                'password' => Hash::make('H123456@'),
                'department_id' => '1',
                'role_id' => 1,
            ],
        ]);
    }
}
