<?php

namespace App\Http\Controllers;

use App\Models\PayoutRequest;
use App\Services\PayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayoutController extends Controller
{
    public function __construct(
        protected PayoutService $payoutService
    ) {}

    /**
     * Guide / Vendor Earnings & Escrow Wallet (PRD §16.2).
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $guide = $user->guideProfile;
        $vendor = $user->vendorProfile;

        if (! $guide && ! $vendor) {
            abort(403, 'Akses dompet escrow hanya untuk mitra tour guide atau vendor.');
        }

        $balance = $guide ? $this->payoutService->getGuideBalance($guide) : $this->payoutService->getVendorBalance($vendor);

        $payouts = PayoutRequest::where('user_id', $user->id)
            ->latest('id')
            ->paginate(10);

        return Inertia::render('Payout/Index', [
            'entityType' => $guide ? 'guide' : 'vendor',
            'entity' => $guide ?? $vendor,
            'balance' => $balance,
            'payouts' => $payouts,
        ]);
    }

    /**
     * Request payout withdrawal.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:50000'],
            'bank_name' => ['nullable', 'string', 'max:50'],
            'bank_account_number' => ['nullable', 'string', 'max:50'],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],
        ]);

        $this->payoutService->requestPayout(
            $request->user(),
            (float) $validated['amount'],
            $validated['bank_name'] ?? null,
            $validated['bank_account_number'] ?? null,
            $validated['bank_account_holder'] ?? null
        );

        return back()->with('success', 'Permintaan pencairan dana berhasil diajukan! Tim Finansial Turget akan memproses transfer bank dalam 1x24 jam kerja.');
    }
}
