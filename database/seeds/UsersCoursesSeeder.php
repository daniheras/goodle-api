<?php

use Illuminate\Database\Seeder;
use App\Course_User;
use App\User;
use App\Course;

class UsersCoursesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = \Faker\Factory::create();

        // For each course add random users to it
        $courses = Course::all()->pluck('id')->toArray();
        $users = User::all()->pluck('id')->toArray();

        foreach($courses as $course) {
            foreach($users as $user) {

                if (random_int(0, 10) < 4) {
                    $faker = \Faker\Factory::create();
                    CourseUser::create([
                        'course_id' => $course,
                        'user_id' => $user
                    ]);
                }


            };
        };

    }
}
