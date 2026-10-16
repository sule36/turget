<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'participants_count' => 'integer',
            'subtotal' => 'decimal:2',
            'platform_fee' => 'decimal:2',
            'discount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'status' => BookingStatus::class,
            'cancellation_policy' => 'array',
            'payment_deadline' => 'datetime',
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function traveler(): BelongsTo
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    public function guide(): BelongsTo
    {
        return $this->belongsTo(GuideProfile::class, 'guide_profile_id');
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(VendorProfile::class, 'vendor_profile_id');
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class, 'tour_package_id');
    }

    public function vendorProduct(): BelongsTo
    {
        return $this->belongsTo(VendorProduct::class, 'vendor_product_id');
    }

    public function tripProposal(): BelongsTo
    {
        return $this->belongsTo(TripProposal::class, 'trip_proposal_id');
    }

    public function addons(): HasMany
    {
        return $this->hasMany(BookingAddon::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function escrowLedgers(): HasMany
    {
        return $this->hasMany(EscrowLedger::class);
    }

    public function refundRequests(): HasMany
    {
        return $this->hasMany(RefundRequest::class);
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->whereIn('status', [BookingStatus::PaidEscrow, BookingStatus::Confirmed, BookingStatus::InProgress, BookingStatus::Completed]);
    }
}
