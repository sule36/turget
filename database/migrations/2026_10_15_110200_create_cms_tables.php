<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Editorial CMS, workflow audit trail, SEO redirects and audit logs (PRD §6, §16, §20, §23.10).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('article_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_category_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('excerpt')->nullable();
            $table->json('body')->nullable(); // markdown per locale
            $table->unsignedSmallInteger('reading_minutes')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('cover_alt')->nullable();
            $table->string('cover_credit')->nullable();
            $table->json('sources')->nullable();
            $table->boolean('is_sponsored')->default(false);
            $table->string('status', 20)->default('draft')->index();
            $table->boolean('is_featured')->default(false)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamp('published_at')->nullable()->index();
            $table->timestamp('facts_verified_at')->nullable();
            $table->json('meta')->nullable();
            $table->string('og_image')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // Articles linked to countries / regions / destinations (internal linking).
        Schema::create('articleables', function (Blueprint $table) {
            $table->foreignId('article_id')->constrained()->cascadeOnDelete();
            $table->morphs('articleable');
            $table->primary(['article_id', 'articleable_type', 'articleable_id'], 'articleables_primary');
        });

        // Every workflow transition and content edit, with a snapshot for audit/rollback.
        Schema::create('content_revisions', function (Blueprint $table) {
            $table->id();
            $table->morphs('revisionable');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 30); // created|updated|submitted|approved|changes_requested|published|unpublished|archived|restored
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20)->nullable();
            $table->text('note')->nullable();
            $table->json('changes')->nullable();
            $table->json('snapshot')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        Schema::create('slug_redirects', function (Blueprint $table) {
            $table->id();
            $table->string('from_path')->unique();
            $table->string('to_path');
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->unsignedInteger('hits')->default(0);
            $table->timestamps();
        });

        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 60)->index();
            $table->nullableMorphs('auditable');
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
        Schema::dropIfExists('slug_redirects');
        Schema::dropIfExists('content_revisions');
        Schema::dropIfExists('articleables');
        Schema::dropIfExists('articles');
        Schema::dropIfExists('article_categories');
    }
};
