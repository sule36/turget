<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Geographic hierarchy + destination content (PRD §4.2, §5, §6, §23.2).
 *
 * Translatable fields are JSON objects keyed by locale, e.g. {"id": "...", "en": "..."}.
 * `sections` holds translatable long-form blocks keyed by section name
 * (history, culture, cuisine, ...), so new sections need no schema change.
 */
return new class extends Migration
{
    /** Columns shared by every publishable content entity. */
    private function contentColumns(Blueprint $table): void
    {
        $table->json('tagline')->nullable();
        $table->json('summary')->nullable();
        $table->json('sections')->nullable();
        $table->json('facts')->nullable();
        $table->string('cover_image')->nullable();
        $table->json('cover_alt')->nullable();
        $table->string('cover_credit')->nullable();
        $table->json('sources')->nullable();
        $table->string('status', 20)->default('draft')->index();
        $table->boolean('is_featured')->default(false)->index();
        $table->unsignedInteger('sort_order')->default(0);
        $table->timestamp('published_at')->nullable();
        $table->timestamp('facts_verified_at')->nullable();
        $table->json('meta')->nullable(); // {title:{id,en}, description:{id,en}}
        $table->string('og_image')->nullable();
        $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
        $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
        $table->timestamps();
        $table->softDeletes();
    }

    public function up(): void
    {
        Schema::create('countries', function (Blueprint $table) {
            $table->id();
            $table->char('code', 2)->unique();
            $table->string('slug')->unique();
            $table->json('name');
            $this->contentColumns($table);
        });

        Schema::create('administrative_regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('administrative_regions')->nullOnDelete();
            // province | state | region | regency | city | district — configurable per country
            $table->string('type', 30);
            $table->unsignedTinyInteger('level')->default(1);
            $table->string('slug');
            $table->json('name');
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $this->contentColumns($table);

            $table->unique(['country_id', 'slug']);
            $table->index(['country_id', 'level']);
        });

        Schema::create('destination_categories', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('icon', 40)->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('destinations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('administrative_regions')->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('highlights')->nullable();   // {id:[...], en:[...]}
            $table->json('activities')->nullable();   // {id:[...], en:[...]}
            $table->text('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->json('opening_hours')->nullable(); // {id:"...", en:"..."} free text until verified schedule exists
            $table->json('ticket_info')->nullable();
            $table->unsignedSmallInteger('visit_duration_minutes')->nullable();
            $table->unsignedInteger('popularity_score')->default(0);
            $this->contentColumns($table);

            $table->index(['region_id', 'status']);
            $table->index(['latitude', 'longitude']);
        });

        Schema::create('destination_category_destination', function (Blueprint $table) {
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_category_id')->constrained()->cascadeOnDelete();
            $table->primary(['destination_id', 'destination_category_id'], 'dest_cat_primary');
        });

        Schema::create('media', function (Blueprint $table) {
            $table->id();
            $table->morphs('mediable');
            $table->string('type', 10)->default('image'); // image | video
            $table->string('path');
            $table->json('alt')->nullable();
            $table->json('caption')->nullable();
            $table->string('credit')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media');
        Schema::dropIfExists('destination_category_destination');
        Schema::dropIfExists('destinations');
        Schema::dropIfExists('destination_categories');
        Schema::dropIfExists('administrative_regions');
        Schema::dropIfExists('countries');
    }
};
