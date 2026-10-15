<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Traveler Custom Trip Inquiries (PRD §11)
        Schema::create('trip_inquiries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('region_id')->constrained('administrative_regions')->cascadeOnDelete();
            $table->foreignId('destination_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('duration_days')->default(1);
            $table->unsignedSmallInteger('adults_count')->default(1);
            $table->unsignedSmallInteger('children_count')->default(0);
            $table->decimal('budget_min', 12, 2)->nullable();
            $table->decimal('budget_max', 12, 2)->nullable();
            $table->string('currency', 3)->default('IDR');
            $table->string('travel_style', 32)->default('cultural'); // cultural, adventure, culinary, relaxed, photography, luxury
            $table->json('preferences')->nullable(); // interests, transport_needed, stay_needed
            $table->text('special_requests')->nullable();
            $table->string('status', 32)->default('open')->index(); // open, bidding, accepted, booked, completed, cancelled
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Guide Proposals & Bids (PRD §12)
        Schema::create('trip_proposals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_inquiry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guide_profile_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('cover_letter');
            $table->json('itinerary'); // day-by-day plan with activities & timing
            $table->json('price_breakdown'); // guide_fee, transport, tickets, stay, etc.
            $table->decimal('total_amount', 12, 2);
            $table->string('currency', 3)->default('IDR');
            $table->date('valid_until')->nullable();
            $table->string('status', 32)->default('submitted')->index(); // submitted, shortlisted, accepted, rejected, expired
            $table->timestamps();
            $table->softDeletes();
        });

        // 3. In-App Messaging Conversations (PRD §13)
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_one_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('user_two_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('trip_inquiry_id')->nullable()->constrained()->nullOnDelete();
            $table->timestamp('last_message_at')->nullable()->index();
            $table->timestamps();

            $table->index(['user_one_id', 'user_two_id']);
        });

        // 4. Messages with Anti-Off-Platform Protection (PRD §13.2)
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->cascadeOnDelete();
            $table->foreignId('sender_id')->constrained('users')->cascadeOnDelete();
            $table->text('original_body'); // raw text
            $table->text('clean_body'); // filtered / masked text
            $table->boolean('is_flagged')->default(false)->index();
            $table->json('flagged_reasons')->nullable(); // ['phone_detected', 'whatsapp_link', 'email_detected', 'account_detected']
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });

        // 5. Bookings (PRD §14)
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_code', 32)->unique()->index();
            $table->foreignId('traveler_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('guide_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('tour_package_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_product_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('trip_proposal_id')->nullable()->constrained()->nullOnDelete();
            $table->string('booking_type', 32)->default('package'); // package, vendor_product, custom_trip
            $table->date('start_date');
            $table->date('end_date');
            $table->unsignedSmallInteger('participants_count')->default(1);
            $table->decimal('subtotal', 12, 2);
            $table->decimal('platform_fee', 12, 2)->default(0); // 10% Turget commission
            $table->decimal('discount', 12, 2)->default(0);
            $table->decimal('total_amount', 12, 2);
            $table->string('currency', 3)->default('IDR');
            $table->string('status', 32)->default('pending_payment')->index(); // BookingStatus enum
            $table->json('cancellation_policy')->nullable();
            $table->text('special_notes')->nullable();
            $table->timestamp('payment_deadline')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // 6. Booking Addons (Linking Vendor Products to a Booking)
        Schema::create('booking_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('vendor_product_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('total_price', 12, 2);
            $table->timestamps();
        });

        // 7. Transactions & Invoices (PRD §15)
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->string('transaction_code', 32)->unique()->index();
            $table->string('payment_gateway', 32)->default('sandbox'); // midtrans, xendit, sandbox
            $table->string('payment_method', 32)->default('qris'); // qris, bca_va, credit_card, mandiri_va
            $table->string('gateway_reference')->nullable()->index();
            $table->decimal('amount', 12, 2);
            $table->decimal('platform_fee', 12, 2)->default(0);
            $table->string('status', 32)->default('pending')->index(); // PaymentStatus enum
            $table->timestamp('paid_at')->nullable();
            $table->json('payload')->nullable();
            $table->timestamps();
        });

        // 8. Escrow Ledger (PRD §15.2, §16.1)
        Schema::create('escrow_ledgers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained()->cascadeOnDelete();
            $table->string('beneficiary_type', 32); // guide, vendor, platform
            $table->unsignedBigInteger('beneficiary_id')->nullable(); // guide_profile_id or vendor_profile_id
            $table->decimal('amount', 12, 2);
            $table->decimal('fee_deducted', 12, 2)->default(0);
            $table->decimal('net_amount', 12, 2);
            $table->string('status', 32)->default('held')->index(); // EscrowStatus: held, released, refunded, disputed
            $table->string('notes')->nullable();
            $table->timestamp('released_at')->nullable();
            $table->timestamps();
        });

        // 9. Payout Requests (PRD §16.2)
        Schema::create('payout_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('guide_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('vendor_profile_id')->nullable()->constrained()->nullOnDelete();
            $table->decimal('amount', 12, 2);
            $table->string('currency', 3)->default('IDR');
            $table->string('bank_name');
            $table->string('bank_account_number');
            $table->string('bank_account_holder');
            $table->string('status', 32)->default('pending')->index(); // PayoutStatus: pending, processing, completed, rejected
            $table->text('admin_notes')->nullable();
            $table->string('proof_file_path')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });

        // 10. Traveler Refund & Cancellation Disputes (PRD §17)
        Schema::create('refund_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('booking_id')->constrained()->cascadeOnDelete();
            $table->foreignId('traveler_id')->constrained('users')->cascadeOnDelete();
            $table->text('reason');
            $table->decimal('requested_amount', 12, 2);
            $table->decimal('approved_amount', 12, 2)->nullable();
            $table->decimal('deduction_fee', 12, 2)->default(0);
            $table->string('status', 32)->default('pending')->index(); // pending, approved, rejected
            $table->text('admin_notes')->nullable();
            $table->foreignId('processed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refund_requests');
        Schema::dropIfExists('payout_requests');
        Schema::dropIfExists('escrow_ledgers');
        Schema::dropIfExists('transactions');
        Schema::dropIfExists('booking_addons');
        Schema::dropIfExists('bookings');
        Schema::dropIfExists('messages');
        Schema::dropIfExists('conversations');
        Schema::dropIfExists('trip_proposals');
        Schema::dropIfExists('trip_inquiries');
    }
};
