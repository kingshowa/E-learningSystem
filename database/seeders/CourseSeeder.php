<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Course;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
    [
        'name' => 'Data Structures and Algorithms',
        'code' => 'CS201',
        'description' => 'Study of fundamental data structures and algorithms used in computer science.',
        'level' => 'Intermediate',
        'price' => 129.99,
        'photo' => 'data_algo.jpg',
    ],
    [
        'name' => 'Software Engineering',
        'code' => 'CS301',
        'description' => 'Principles and practices of software development, including design, testing, and project management.',
        'level' => 'Advanced',
        'price' => 149.99,
        'photo' => 'software_eng.jpg',
    ],
    [
        'name' => 'Database Management Systems',
        'code' => 'CS401',
        'description' => 'Introduction to database management systems and SQL programming.',
        'level' => 'Intermediate',
        'price' => 119.99,
        'photo' => 'dbms.jpg',
    ],
    [
        'name' => 'Operating Systems',
        'code' => 'CS501',
        'description' => 'Study of operating system concepts, processes, memory management, and file systems.',
        'level' => 'Intermediate',
        'price' => 139.99,
        'photo' => 'os.jpg',
    ],
    [
        'name' => 'Computer Networks',
        'code' => 'CS601',
        'description' => 'Introduction to computer network architectures, protocols, and technologies.',
        'level' => 'Intermediate',
        'price' => 129.99,
        'photo' => 'networks.jpg',
    ],
    [
        'name' => 'Web Development',
        'code' => 'CS701',
        'description' => 'Fundamentals of web development, including HTML, CSS, JavaScript, and server-side scripting.',
        'level' => 'Intermediate',
        'price' => 139.99,
        'photo' => 'web_dev.jpg',
    ],
    [
        'name' => 'Artificial Intelligence',
        'code' => 'CS801',
        'description' => 'Introduction to artificial intelligence, including search algorithms, machine learning, and expert systems.',
        'level' => 'Advanced',
        'price' => 159.99,
        'photo' => 'ai.jpg',
    ],
    [
        'name' => 'Cybersecurity',
        'code' => 'CS901',
        'description' => 'Principles of cybersecurity, including cryptography, network security, and risk management.',
        'level' => 'Advanced',
        'price' => 159.99,
        'photo' => 'cybersecurity.jpg',
    ],
    [
        'name' => 'Mobile App Development',
        'code' => 'CS1001',
        'description' => 'Development of mobile applications for iOS and Android platforms using native and cross-platform frameworks.',
        'level' => 'Advanced',
        'price' => 169.99,
        'photo' => 'mobile_dev.jpg',
    ],
];


        // Loop through the custom data and insert into the database
        foreach ($courses as $courseData) {
            $courseData['photo'] = 'public/images/7Jbiq1s2qnpRxXflUFYlvPcb2ITwtHu5OOUslyKF.jpg';
            $courseData['creator'] = 1;
            $courseData['assigned_to'] = 2;
            Course::create($courseData);
        }
    }
}
