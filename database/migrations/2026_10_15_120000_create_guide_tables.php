<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Guide Profiles
        Schema::create('guide_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('display_name');
            $table->string('phone')->nullable(); // Private KYC / Internal
            $table->json('tagline')->nullable();
            $table->json('bio')->nullable();
            $table->json('headline')->nullable();
            $table->json('languages')->nullable(); // ['id', 'en', 'ja', ...]
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('administrative_regions')->cascadeOnDelete();
            $table->json('service_areas')->nullable(); // list of region IDs
            $table->json('specializations')->nullable(); // ['cultural', 'trekking', 'photography', ...]
            $table->unsignedInteger('experience_years')->default(1);
            $table->string('avatar_url')->nullable();
            $table->string('cover_image_url')->nullable();
            $table->json('gallery')->nullable();
            $table->string('video_intro_url')->nullable();
            $table->decimal('price_per_day', 12, 2)->default(0);
            $table->string('currency', 3)->default('IDR');
            $table->json('cancellation_policy')->nullable();
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->string('verification_status', 32)->default('draft')->index();
            $table->text('verification_note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('badge_verified')->default(false)->index();
            $table->decimal('rating_avg', 3, 2)->default(5.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('trips_completed_count')->default(0);
            $table->unsignedTinyInteger('response_rate_percent')->default(100);
            $table->unsignedSmallInteger('response_time_minutes')->default(15);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Guide KYC & Verification Documents
        Schema::create('guide_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_profile_id')->constrained()->cascadeOnDelete();
            $table->string('document_type', 32); // 'id_card', 'passport', 'guide_license', 'certificate'
            $table->string('document_number')->nullable();
            $table->string('document_file_path');
            $table->date('expiry_date')->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->text('admin_notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 3. Guide Expertise Destinations Pivot
        Schema::create('guide_destinations', function (Blueprint $table) {
            $table->foreignId('guide_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->constrained()->cascadeOnDelete();
            $table->primary(['guide_profile_id', 'destination_id']);
        });

        // 4. Tour Packages / Listings
        Schema::create('tour_packages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->json('title');
            $table->json('tagline')->nullable();
            $table->json('description');
            $table->string('tour_type', 32)->default('cultural')->index();
            $table->string('price_type', 32)->default('per_person')->index();
            $table->decimal('base_price', 12, 2);
            $table->string('currency', 3)->default('IDR');
            $table->unsignedSmallInteger('duration_hours')->default(8);
            $table->unsignedSmallInteger('duration_days')->default(1);
            $table->unsignedSmallInteger('min_participants')->default(1);
            $table->unsignedSmallInteger('max_participants')->default(10);
            $table->string('meeting_point')->nullable();
            $table->string('end_point')->nullable();
            $table->json('itinerary')->nullable();
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->json('requirements')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->string('status', 32)->default('draft')->index();
            $table->text('moderation_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Guide Availability Calendar
        Schema::create('guide_availabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('guide_profile_id')->constrained()->cascadeOnDelete();
            $table->date('date')->index();
            $table->boolean('is_available')->default(true);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['guide_profile_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guide_availabilities');
        Schema::dropIfExists('tour_packages');
        Schema::dropIfExists('guide_destinations');
        Schema::dropIfExists('guide_verifications');
        Schema::dropIfExists('guide_profiles');
    }
};
