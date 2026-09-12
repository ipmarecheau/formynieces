<?php

use AppModels\SyllabusModule;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('past_papers', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('subject');
            $table->string('provenance')->default('real');
            $table->string('source_ref')->nullable()->unique();
            $table->boolean('is_published')->default(false);
            $table->timestamps();
        });

        Schema::create('past_paper_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('past_paper_id')->constrained()->cascadeOnDelete();
            $table->foreignId('syllabus_module_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('seed_question_id')->nullable()->constrained('past_paper_questions')->nullOnDelete();
            $table->unsignedInteger('number');
            $table->string('item_type')->default('mcq');
            $table->text('prompt');
            $table->json('options')->nullable();
            $table->text('correct_answer')->nullable();
            $table->json('mark_scheme')->nullable();
            $table->unsignedInteger('marks')->default(1);
            $table->string('objective')->nullable();
            $table->unsignedTinyInteger('difficulty')->default(1);
            $table->string('provenance')->default('real');
            $table->string('qc_status')->default('approved');
            $table->boolean('is_withdrawn')->default(false);
            $table->timestamps();
            $table->index(['past_paper_id', 'number']);
            $table->index(['qc_status', 'is_withdrawn']);
        });

        Schema::create('paper_sittings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('past_paper_id')->constrained()->cascadeOnDelete();
            $table->string('paper_code')->unique();
            $table->string('status')->default('issued');
            $table->string('subject');
            $table->string('length')->default('short');
            $table->json('question_ids');
            $table->date('issued_at');
            $table->dateTime('graded_at')->nullable();
            $table->unsignedInteger('score')->nullable();
            $table->unsignedInteger('total_marks')->nullable();
            $table->timestamps();
            $table->index(['student_id', 'issued_at']);
        });

        Schema::create('paper_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_sitting_id')->constrained()->cascadeOnDelete();
            $table->json('image_paths');
            $table->string('digitisation_status')->default('pending');
            $table->timestamps();
        });

        Schema::create('paper_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paper_sitting_id')->constrained()->cascadeOnDelete();
            $table->foreignId('past_paper_question_id')->constrained()->cascadeOnDelete();
            $table->text('read_answer')->nullable();
            $table->text('working_note')->nullable();
            $table->boolean('is_correct')->nullable();
            $table->unsignedInteger('marks_awarded')->default(0);
            $table->decimal('confidence', 3, 2)->default(1);
            $table->text('misconception')->nullable();
            $table->timestamps();
            $table->unique(['paper_sitting_id', 'past_paper_question_id'], 'paper_answer_once');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paper_answers');
        Schema::dropIfExists('paper_submissions');
        Schema::dropIfExists('paper_sittings');
        Schema::dropIfExists('past_paper_questions');
        Schema::dropIfExists('past_papers');
    }
};
