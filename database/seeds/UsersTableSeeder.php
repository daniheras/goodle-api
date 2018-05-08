<?php

use Illuminate\Database\Seeder;
use App\User;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        // Create 100 users
        for ($i = 0; $i < 100; $i++) {
            User::create([
                'username' => $faker->unique()->userName,
                'email' => $faker->unique()->email,
                'password' => $faker->password
                ]);
        }
    }
}
