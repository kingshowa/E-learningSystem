<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code')->nullable();
            $table->longText('description')->nullable();
            $table->string('level')->nullable();
            $table->double('price')->nullable();
            $table->string('photo')->nullable();
            $table->foreignId('creator')->nullable();
            $table->foreignId('assigned_to')->nullable();
            $table->boolean('completed')->default(false)->nullable();
            $table->boolean('enabled')->default(false)->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
