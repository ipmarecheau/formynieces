<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Child metadata — optional beyond the essentials captured at setup.
            $table->unsignedSmallInteger('birth_year')->nullable()->after('target_sea_year');
            $table->string('current_school')->nullable()->after('birth_year');
        });

        Schema::create('co_parents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guardian_id')->constrained('users')->cascadeOnDelete();
            $table->string('name');
            $table->string('email');
            $table->string('relationship')->nullable();
            $table->string('status')->default('invited'); // invited | accepted
            $table->timestamp('invited_at')->nullable();
            $table->timestamps();

            $table->unique(['guardian_id', 'email']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('co_parents');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['birth_year', 'current_school']);
        });
    }
};
