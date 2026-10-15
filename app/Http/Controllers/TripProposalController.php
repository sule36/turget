<?php

namespace App\Http\Controllers;

use App\Enums\ProposalStatus;
use App\Enums\TripInquiryStatus;
use App\Models\AuditLog;
use App\Models\TripInquiry;
use App\Models\TripProposal;
use App\Services\PaymentAndEscrowService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class TripProposalController extends Controller
{
    public function __construct(
        protected PaymentAndEscrowService $paymentService
    ) {}

    /**
     * Guide submits custom proposal / quote (PRD §12).
     */
    public function store(Request $request, TripInquiry $trip): RedirectResponse
    {
        $user = $request->user();
        $guide = $user->guideProfile;

        if (! $guide || ! $guide->badge_verified) {
            abort(403, 'Hanya pemandu wisata terverifikasi yang dapat mengajukan penawaran trip.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'cover_letter' => ['required', 'string', 'min:20', 'max:3000'],
            'itinerary' => ['required', 'array', 'min:1'],
            'price_breakdown' => ['required', 'array'],
            'total_amount' => ['required', 'numeric', 'min:50000'],
            'valid_until' => ['nullable', 'date', 'after:today'],
        ]);

        $proposal = TripProposal::create([
            'trip_inquiry_id' => $trip->id,
            'guide_profile_id' => $guide->id,
            'title' => $validated['title'],
            'cover_letter' => $validated['cover_letter'],
            'itinerary' => $validated['itinerary'],
            'price_breakdown' => $validated['price_breakdown'],
            'total_amount' => $validated['total_amount'],
            'currency' => 'IDR',
            'valid_until' => $validated['valid_until'] ?? now()->addDays(7),
            'status' => ProposalStatus::Submitted,
        ]);

        if ($trip->status === TripInquiryStatus::Open) {
            $trip->update(['status' => TripInquiryStatus::Bidding]);
        }

        AuditLog::record('trip_proposal.submitted', $proposal);

        return back()->with('success', 'Proposal penawaran Anda berhasil diajukan kepada wisatawan!');
    }

    /**
     * Traveler accepts proposal -> transitions proposal to Accepted, creates Booking in Pending Payment.
     */
    public function accept(Request $request, TripProposal $proposal): RedirectResponse
    {
        $user = $request->user();
        $inquiry = $proposal->inquiry;

        if ($inquiry->user_id !== $user->id) {
            abort(403);
        }

        $booking = $this->paymentService->createBookingFromProposal($proposal, $user);

        return redirect()->route('bookings.payment', $booking)->with('success', 'Proposal diterima! Silakan selesaikan pembayaran agar dana aman tersimpan di Escrow Turget.id.');
    }

    /**
     * Traveler declines proposal.
     */
    public function reject(Request $request, TripProposal $proposal): RedirectResponse
    {
        $user = $request->user();
        if ($proposal->inquiry->user_id !== $user->id) {
            abort(403);
        }

        $proposal->update(['status' => ProposalStatus::Rejected]);

        return back()->with('info', 'Penawaran proposal telah ditolak.');
    }
}
