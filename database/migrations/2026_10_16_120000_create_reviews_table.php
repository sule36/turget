<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('traveler_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('guide_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tour_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_product_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedTinyInteger('rating'); // 1-5 stars
            $table->text('comment')->nullable();
            $table->boolean('is_published')->default(true)->index();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};
