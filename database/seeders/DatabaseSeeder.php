<?php

namespace Database\Seeders;

use App\Models\Contact;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $admin = User::factory()->create([
            'name' => 'Administrador Contactos',
            'email' => 'admin@contact.test',
        ]);

        Contact::factory(10)->for($admin)->create();

        User::factory(3)
            ->has(Contact::factory(5))
            ->create();
    }
}
