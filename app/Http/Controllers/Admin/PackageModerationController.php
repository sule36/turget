<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PackageModerationController extends Controller
{
    /**
     * Admin Package Moderation Queue (PRD §7.5).
     */
    public function index(Request $request): Response
    {
        $query = TourPackage::with(['guide.region', 'destination']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $packages = $query->latest('updated_at')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Packages/Index', [
            'packages' => $packages,
            'filters' => [
                'status' => $request->get('status', 'all'),
            ],
            'statuses' => array_map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(app()->getLocale()),
            ], ListingStatus::cases()),
        ]);
    }

    /**
     * Moderate Package (approve / reject).
     */
    public function action(Request $request, TourPackage $package): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:approve,reject,pause,unpause'],
            'note' => ['nullable', 'string', 'max:500'],
        ]);

        $admin = $request->user();
        $action = $validated['action'];
        $note = $validated['note'] ?? null;

        switch ($action) {
            case 'approve':
                $package->update([
                    'status' => ListingStatus::Published,
                    'moderation_note' => $note,
                    'approved_at' => now(),
                    'approved_by' => $admin->id,
                ]);
                $msg = 'Paket wisata disetujui dan kini aktif di marketplace publik!';
                break;
            case 'reject':
                $package->update([
                    'status' => ListingStatus::Rejected,
                    'moderation_note' => $note,
                ]);
                $msg = 'Paket wisata ditolak dengan catatan moderasi.';
                break;
            case 'pause':
                $package->update([
                    'status' => ListingStatus::Paused,
                ]);
                $msg = 'Paket wisata dijeda sementara.';
                break;
            case 'unpause':
                $package->update([
                    'status' => ListingStatus::Published,
                ]);
                $msg = 'Paket wisata diaktifkan kembali.';
                break;
            default:
                abort(400);
        }

        AuditLog::record('package.moderation.'.$action, $package);

        return back()->with('success', $msg);
    }
}
