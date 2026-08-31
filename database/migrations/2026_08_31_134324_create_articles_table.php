<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * The public blog / resources library (BLOG). Articles are authored as
     * version-controlled markdown files in database/data/blog/*.md and imported
     * by ArticleSeeder (upsert by slug), mirroring the lessons data-file pattern.
     */
    public function up(): void
    {
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('meta_description', 320);
            $table->text('excerpt');
            $table->string('category');
            $table->json('keywords')->nullable();
            $table->string('author')->default('SmoothSeas');
            $table->string('cover')->nullable();
            $table->unsignedSmallInteger('read_minutes')->default(6);
            $table->longText('body_html');
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->index('category');
            $table->index('published_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
