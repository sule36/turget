<?php

namespace App\Http\Controllers\Admin;

use App\Enums\EscrowStatus;
use App\Http\Controllers\Controller;
use App\Models\EscrowLedger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class EscrowLedgerController extends Controller
{
    /**
     * Admin Escrow Ledger & Commission Oversight (PRD §15.2, §16.1, §20.1).
     */
    public function index(Request $request): Response
    {
        $query = EscrowLedger::with(['booking.traveler', 'booking.guide', 'booking.vendor', 'transaction']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('beneficiary') && $request->beneficiary !== 'all') {
            $query->where('beneficiary_type', $request->beneficiary);
        }

        $ledgers = $query->latest('id')->paginate(15)->withQueryString();

        $stats = [
            'total_held' => (float) EscrowLedger::where('status', EscrowStatus::Held)->sum('net_amount'),
            'total_released' => (float) EscrowLedger::where('status', EscrowStatus::Released)->sum('net_amount'),
            'total_platform_revenue' => (float) EscrowLedger::where('beneficiary_type', 'platform')->sum('net_amount'),
            'total_refunded' => (float) EscrowLedger::where('status', EscrowStatus::Refunded)->sum('net_amount'),
        ];

        return Inertia::render('Admin/Escrow/Index', [
            'ledgers' => $ledgers,
            'stats' => $stats,
            'filters' => [
                'status' => $request->get('status', 'all'),
                'beneficiary' => $request->get('beneficiary', 'all'),
            ],
        ]);
    }

    /**
     * Force release escrow funds manually by Admin.
     */
    public function forceRelease(EscrowLedger $ledger): RedirectResponse
    {
        $ledger->update([
            'status' => EscrowStatus::Released,
            'released_at' => now(),
            'notes' => $ledger->notes.' (Dilepas manual oleh Admin)',
        ]);

        return back()->with('success', 'Dana escrow berhasil dilepas ke saldo penerima.');
    }
}
