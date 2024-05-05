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
         // Create the update_course_progress trigger
        DB::unprepared('
    CREATE TRIGGER update_course_progress AFTER INSERT ON module_progress
    FOR EACH ROW
    BEGIN
        DECLARE total_modules INT;
        DECLARE completed_modules INT;
        DECLARE overall_completion DECIMAL(5,2);

        -- Get the total number of modules in the course
        SELECT COUNT(*) INTO total_modules FROM course_modules WHERE course_id = NEW.course_id;

        -- Get the number of completed modules in the course
        SELECT COUNT(*) INTO completed_modules FROM module_progress mp
        INNER JOIN course_modules cm ON mp.module_id = cm.module_id
        WHERE mp.user_id = NEW.user_id AND cm.course_id = NEW.course_id AND mp.is_completed = true;

        -- Calculate the overall completion percentage
        IF total_modules > 0 THEN
            SET overall_completion = (completed_modules / total_modules) * 100;
        ELSE
            SET overall_completion = 0;
        END IF;

        -- Update the course_progress table
        UPDATE course_progress SET overal_completion = overall_completion WHERE user_id = NEW.user_id AND course_id = NEW.course_id;
    END
');

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the trigger
        DB::unprepared('DROP TRIGGER IF EXISTS update_course_progress');
    }
};
