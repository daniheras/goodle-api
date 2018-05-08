<?php

use Illuminate\Database\Seeder;
use App\Course;

class CoursesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        // Create 5 courses
        for ($i = 0; $i < 5; $i++) {
            Course::create([
                'name' => $faker->colorName,
                'category' => 'Programación',
                'description' => $faker->catchPhrase,
                'picture' => 'https://placeimg.com/640/480/any'
            ]);
        }
    }
}
