<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Vendor Profiles
        Schema::create('vendor_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('slug')->unique();
            $table->string('business_name');
            $table->string('business_type', 32)->default('individual'); // individual, cv, pt, koperasi
            $table->string('owner_name');
            $table->string('phone');
            $table->string('email');
            $table->text('address');
            $table->decimal('latitude', 10, 7)->index();
            $table->decimal('longitude', 10, 7)->index();
            $table->foreignId('country_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('administrative_regions')->cascadeOnDelete();
            $table->json('service_areas')->nullable(); // administrative_region ids
            $table->string('category', 32)->index(); // VendorCategory enum
            $table->json('description')->nullable(); // bilingual {id, en}
            $table->string('cover_image_url')->nullable();
            $table->string('avatar_url')->nullable();
            $table->json('gallery')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('bank_account_holder')->nullable();
            $table->json('cancellation_policy')->nullable();
            $table->json('opening_hours')->nullable();
            $table->string('verification_status', 32)->default('draft')->index();
            $table->text('verification_note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('badge_verified')->default(false)->index();
            $table->decimal('rating_avg', 3, 2)->default(5.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->unsignedInteger('bookings_completed_count')->default(0);
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Vendor KYC & Compliance Verifications
        Schema::create('vendor_verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained()->cascadeOnDelete();
            $table->string('nib')->nullable();
            $table->string('nib_file_path')->nullable();
            $table->string('siup')->nullable();
            $table->string('siup_file_path')->nullable();
            $table->string('npwp')->nullable();
            $table->string('npwp_file_path')->nullable();
            $table->string('ktp_owner')->nullable();
            $table->string('ktp_file_path')->nullable();
            $table->string('status', 32)->default('pending')->index();
            $table->text('notes')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 3. Vendor Catalog Products / Services
        Schema::create('vendor_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vendor_profile_id')->constrained()->cascadeOnDelete();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('slug')->unique();
            $table->json('name');
            $table->json('description')->nullable();
            $table->string('category', 32)->index(); // VendorCategory enum
            $table->decimal('price', 12, 2);
            $table->string('price_unit', 32)->default('per_day'); // per_night, per_day, per_person, per_session, per_unit
            $table->string('currency', 3)->default('IDR');
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->unsignedSmallInteger('stock_quantity')->default(1);
            $table->string('booking_model', 32)->default('instant'); // instant, request_to_book, manual
            $table->json('inclusions')->nullable();
            $table->json('exclusions')->nullable();
            $table->json('terms')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('gallery')->nullable();
            $table->decimal('latitude', 10, 7)->nullable()->index();
            $table->decimal('longitude', 10, 7)->nullable()->index();
            $table->text('address')->nullable();
            $table->string('status', 32)->default('draft')->index(); // ListingStatus enum
            $table->text('moderation_note')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('rating_avg', 3, 2)->default(5.00);
            $table->unsignedInteger('reviews_count')->default(0);
            $table->boolean('is_featured')->default(false)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        // 4. Tour Package & Vendor Add-on Pivot (PRD §8.2)
        Schema::create('package_vendor_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tour_package_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_product_id')->constrained()->cascadeOnDelete();
            $table->decimal('custom_price', 12, 2)->nullable();
            $table->boolean('is_required')->default(false);
            $table->json('notes')->nullable();
            $table->timestamps();

            $table->unique(['tour_package_id', 'vendor_product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('package_vendor_addons');
        Schema::dropIfExists('vendor_products');
        Schema::dropIfExists('vendor_verifications');
        Schema::dropIfExists('vendor_profiles');
    }
};
