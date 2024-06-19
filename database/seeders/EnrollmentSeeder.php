<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Enrollment;
use Illuminate\Support\Carbon;
use Faker\Factory as Faker;

class EnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();
        $enrollments = [];
        $users=[3,5,6,8,9,10,14,15,16,19];

        for ($i = 0; $i < 10; $i++) {
            $enrollments[] = [
                'program_id' => 1,
                'user_id' => $users[$i],
                'created_at' => $faker->dateTimeBetween('-6 months', 'now'),
                'updated_at' => now()
            ];
        }

        foreach ($enrollments as $enrollment) {
            Enrollment::create($enrollment);
        }
    }
}
