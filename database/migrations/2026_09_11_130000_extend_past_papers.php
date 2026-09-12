<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
return new class extends Migration {
    public function up(): void { Schema::table('past_paper_questions', fn (Blueprint $table) => $table->text('qc_reason')->nullable()->after('qc_status')); }
    public function down(): void { Schema::table('past_paper_questions', fn (Blueprint $table) => $table->dropColumn('qc_reason')); }
};
