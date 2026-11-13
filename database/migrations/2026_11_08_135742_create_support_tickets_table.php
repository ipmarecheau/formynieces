<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The in-app help channel: any user, on any screen, can type a message or leave a
     * voice note the moment they hit friction. Each ticket captures who, where and what,
     * so complaints surface in the admin panel early and can be fixed almost instantly.
     */
    public function up(): void
    {
        Schema::create('support_tickets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('role')->nullable();                 // snapshot: student | guardian | guest
            $table->foreignId('student_id')->nullable()->constrained('users')->nullOnDelete(); // child in context
            $table->string('kind')->default('issue');           // issue | question | idea
            $table->text('message')->nullable();                // typed message (null when voice-only)
            $table->string('audio_path')->nullable();           // stored voice note (private disk)
            $table->text('transcript')->nullable();             // filled on demand from the admin panel
            $table->string('url')->nullable();                  // page they were on
            $table->string('user_agent', 512)->nullable();
            $table->json('context')->nullable();                // route, viewport, extra breadcrumbs
            $table->string('status')->default('open')->index(); // open | triaged | resolved
            $table->text('admin_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('support_tickets');
    }
};
