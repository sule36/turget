<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Services\PayoutService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PayoutModerationController extends Controller
{
    public function __construct(
        protected PayoutService $payoutService
    ) {}

    /**
     * Admin Payout Requests Queue (PRD §16.2).
     */
    public function index(Request $request): Response
    {
        $query = PayoutRequest::with(['user', 'guide', 'vendor', 'processor']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $payouts = $query->latest('id')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Payouts/Index', [
            'payouts' => $payouts,
            'filters' => [
                'status' => $request->get('status', 'all'),
            ],
        ]);
    }

    public function approve(Request $request, PayoutRequest $payout): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:500'],
            'proof_file_path' => ['nullable', 'string'],
        ]);

        $this->payoutService->approvePayout(
            $payout,
            $request->user(),
            $validated['proof_file_path'] ?? null,
            $validated['notes'] ?? null
        );

        return back()->with('success', 'Permintaan pencairan dana telah disetujui & dicatat.');
    }

    public function reject(Request $request, PayoutRequest $payout): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:500'],
        ]);

        $this->payoutService->rejectPayout($payout, $request->user(), $validated['reason']);

        return back()->with('success', 'Permintaan pencairan dana ditolak.');
    }
}
