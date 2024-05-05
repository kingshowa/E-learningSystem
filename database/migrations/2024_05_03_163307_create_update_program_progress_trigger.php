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
        // update_program_progress trigger
        DB::unprepared('
            CREATE TRIGGER update_program_progress AFTER UPDATE ON course_progress
            FOR EACH ROW
            BEGIN
                -- Update program progress for each program associated with the updated course
                UPDATE program_progress
                SET overal_completion = (
                    SELECT AVG(overal_completion)
                    FROM course_progress
                    WHERE course_id IN (
                        SELECT course_id
                        FROM program_courses
                        WHERE program_id = (
                            SELECT program_id
                            FROM program_courses
                            WHERE course_id = NEW.course_id
                        )
                    )
                )
                WHERE program_id IN (
                    SELECT program_id
                    FROM program_courses
                    WHERE course_id = NEW.course_id
                );
            END
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS update_program_progress');
    }
};
