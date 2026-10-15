<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RefundRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RefundModerationController extends Controller
{
    /**
     * Admin Refund Disputes & Requests Queue (PRD §17).
     */
    public function index(Request $request): Response
    {
        $query = RefundRequest::with(['booking.traveler', 'traveler', 'processor']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $refunds = $query->latest('id')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Refunds/Index', [
            'refunds' => $refunds,
            'filters' => [
                'status' => $request->get('status', 'all'),
            ],
        ]);
    }

    public function process(Request $request, RefundRequest $refund): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'in:approved,rejected'],
            'approved_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        $refund->update([
            'status' => $validated['status'],
            'approved_amount' => $validated['approved_amount'] ?? $refund->approved_amount,
            'admin_notes' => $validated['notes'],
            'processed_by' => $request->user()->id,
            'processed_at' => now(),
        ]);

        return back()->with('success', 'Status pengajuan refund berhasil diperbarui.');
    }
}
