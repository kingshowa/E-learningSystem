<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Module;

class ModuleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $modules = [
            // Modules for Introduction to Computer Science (CS101)
            [
                'name' => 'Introduction to Programming',
                'code' => 'CS101M1',
                'description' => 'Basic concepts of programming and problem-solving techniques.',
                'duration' => 4, // Duration in weeks
            ],
            [
                'name' => 'Data Types and Control Structures',
                'code' => 'CS101M2',
                'description' => 'Study of data types, variables, and control structures in programming languages.',
                'duration' => 5,
            ],
            [
                'name' => 'Functions and Modular Programming',
                'code' => 'CS101M3',
                'description' => 'Understanding functions and modular programming concepts for code reuse and organization.',
                'duration' => 6,
            ],
            // Modules for Data Structures and Algorithms (CS201)
            [
                'name' => 'Arrays and Linked Lists',
                'code' => 'CS201M1',
                'description' => 'Fundamentals of arrays, linked lists, and their implementations.',
                'duration' => 5,
            ],
            [
                'name' => 'Stacks and Queues',
                'code' => 'CS201M2',
                'description' => 'Study of stack and queue data structures and their applications.',
                'duration' => 4,
            ],
            [
                'name' => 'Sorting and Searching Algorithms',
                'code' => 'CS201M3',
                'description' => 'Common sorting and searching algorithms and their analysis.',
                'duration' => 6,
            ],
            // Modules for Software Engineering (CS301)
            [
                'name' => 'Software Development Life Cycle',
                'code' => 'CS301M1',
                'description' => 'Overview of the software development life cycle and its phases.',
                'duration' => 6,
            ],
            [
                'name' => 'Requirements Engineering',
                'code' => 'CS301M2',
                'description' => 'Gathering, analyzing, and documenting software requirements.',
                'duration' => 4,
            ],
            [
                'name' => 'Design Patterns',
                'code' => 'CS301M3',
                'description' => 'Common design patterns and their application in software development.',
                'duration' => 5,
            ],
            [
                'name' => 'Object-Oriented Programming Basics',
                'code' => 'CS101M4',
                'description' => 'Introduction to the principles of object-oriented programming.',
                'duration' => 5,
            ],
            [
                'name' => 'Introduction to Algorithms',
                'code' => 'CS101M5',
                'description' => 'Basic algorithms and algorithmic thinking.',
                'duration' => 6,
            ],
            // Additional modules for Data Structures and Algorithms (CS201)
            [
                'name' => 'Trees and Graphs',
                'code' => 'CS201M4',
                'description' => 'Study of tree and graph data structures and their applications.',
                'duration' => 5,
            ],
            [
                'name' => 'Dynamic Programming',
                'code' => 'CS201M5',
                'description' => 'Introduction to dynamic programming techniques.',
                'duration' => 6,
            ],
            // Additional modules for Software Engineering (CS301)
            [
                'name' => 'Software Testing Fundamentals',
                'code' => 'CS301M4',
                'description' => 'Introduction to software testing techniques and methodologies.',
                'duration' => 4,
            ],
            [
                'name' => 'Agile Software Development',
                'code' => 'CS301M5',
                'description' => 'Overview of agile software development methodologies.',
                'duration' => 5,
            ],
        ];
        
        // Loop through the custom data and insert into the database
        foreach ($modules as $moduleData) {
            $moduleData['creator'] = 1;
            Module::create($moduleData);
        }
    }
}
