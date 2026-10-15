<?php

namespace App\Http\Controllers\Admin;

use App\Enums\VendorVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\VendorProfile;
use App\Services\VendorVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorVerificationController extends Controller
{
    public function __construct(
        protected VendorVerificationService $verificationService
    ) {}

    /**
     * Admin Vendor Verification Queue (PRD §8.2, §20.1).
     */
    public function index(Request $request): Response
    {
        $query = VendorProfile::with(['user', 'region', 'country', 'verifications']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('verification_status', $request->status);
        }

        if ($request->filled('q')) {
            $term = '%'.mb_strtolower($request->q).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw("LOWER(business_name) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(owner_name) LIKE ?", [$term])
                    ->orWhereHas('user', function ($uq) use ($term) {
                        $uq->where('email', 'like', $term);
                    });
            });
        }

        $vendors = $query->latest('updated_at')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Vendors/Index', [
            'vendors' => $vendors,
            'filters' => [
                'status' => $request->get('status', 'all'),
                'q' => $request->get('q', ''),
            ],
            'statuses' => array_map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(app()->getLocale()),
            ], VendorVerificationStatus::cases()),
        ]);
    }

    /**
     * Inspect Vendor KYC & Legal Documents.
     */
    public function show(VendorProfile $vendor): Response
    {
        $vendor->load(['user', 'region.parent', 'country', 'verifications' => fn ($q) => $q->latest(), 'products']);

        $auditLogs = AuditLog::where('auditable_type', VendorProfile::class)
            ->where('auditable_id', $vendor->id)
            ->with('user')
            ->latest('id')
            ->take(15)
            ->get();

        return Inertia::render('Admin/Vendors/Show', [
            'vendor' => $vendor,
            'auditLogs' => $auditLogs,
        ]);
    }

    public function startReview(Request $request, VendorProfile $vendor): RedirectResponse
    {
        $this->verificationService->startReview($vendor, $request->user());

        return back()->with('success', 'Status vendor diubah menjadi Sedang Diverifikasi.');
    }

    public function approve(Request $request, VendorProfile $vendor): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $this->verificationService->approve($vendor, $request->user(), $validated['notes'] ?? null);

        return back()->with('success', "Vendor {$vendor->business_name} berhasil diverifikasi!");
    }

    public function requestRevision(Request $request, VendorProfile $vendor): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $this->verificationService->requestRevision($vendor, $request->user(), $validated['notes']);

        return back()->with('success', 'Permintaan revisi dokumen telah dikirimkan ke vendor.');
    }

    public function reject(Request $request, VendorProfile $vendor): RedirectResponse
    {
        $validated = $request->validate([
            'notes' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $this->verificationService->reject($vendor, $request->user(), $validated['notes']);

        return back()->with('success', 'Pengajuan kemitraan vendor telah ditolak.');
    }

    public function suspend(Request $request, VendorProfile $vendor): RedirectResponse
    {
        $validated = $request->validate([
            'reason' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        $this->verificationService->suspend($vendor, $request->user(), $validated['reason']);

        return back()->with('success', 'Kemitraan vendor berhasil ditangguhkan.');
    }

    public function reactivate(Request $request, VendorProfile $vendor): RedirectResponse
    {
        $this->verificationService->reactivate($vendor, $request->user());

        return back()->with('success', 'Kemitraan vendor telah diaktifkan kembali.');
    }
}
