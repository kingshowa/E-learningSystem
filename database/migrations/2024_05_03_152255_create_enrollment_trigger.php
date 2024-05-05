<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create the initialize_progress trigger
        DB::unprepared('
            CREATE TRIGGER initialize_progress AFTER INSERT ON enrollments
            FOR EACH ROW
            BEGIN
                -- Check if the enrollment is for a course
                IF NEW.course_id IS NOT NULL THEN
                    -- Initialize course progress
                    INSERT INTO course_progress (user_id, course_id, overal_completion)
                    VALUES (NEW.user_id, NEW.course_id, 0);
                END IF;

                -- Check if the enrollment is for a program
                IF NEW.program_id IS NOT NULL THEN
                    -- Initialize program_progress
                    INSERT INTO program_progress (user_id, program_id, overal_completion)
                    VALUES (NEW.user_id, NEW.program_id, 0);

                    -- Initialize course_progress for each course in the program
                    INSERT INTO course_progress (user_id, course_id, program_id, overal_completion)
                    SELECT NEW.user_id, pc.course_id, pc.program_id, 0
                    FROM program_courses pc
                    WHERE pc.program_id = NEW.program_id;
                END IF;
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the trigger
        DB::unprepared('DROP TRIGGER IF EXISTS initialize_progress');
    }
};
