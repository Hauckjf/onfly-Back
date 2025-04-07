<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use App\Models\User;

class DatabaseSeeder extends Seeder
{

    public function run(): void
    {

        Role::firstOrCreate(['name' => 'user']);
        Role::firstOrCreate(['name' => 'admin']);

        $admin = User::create([
            'name' => 'Admin',
            'email' => 'admin@onfly.com',
            'password' => bcrypt('AdmOnfly123!@#'),
        ]);

        $admin->assignRole('admin');
    }
}
