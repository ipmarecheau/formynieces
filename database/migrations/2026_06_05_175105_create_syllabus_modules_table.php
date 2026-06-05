<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('syllabus_modules', function (Blueprint $table) {
            $table->id();
            $table->enum('subject', ['Math', 'English Editing', 'English Comprehension']);
            $table->string('topic');
            $table->enum('sea_section', ['Section I', 'Section II', 'Section III']);
            $table->unsignedInteger('sequence_order');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('syllabus_modules');
    }
};