<?php

namespace App\Http\Controllers\Admin;

use App\Enums\GuideVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\GuideProfile;
use App\Services\GuideVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuideVerificationController extends Controller
{
    public function __construct(
        protected GuideVerificationService $verificationService
    ) {}

    /**
     * Admin Tour Guide Verification Queue (PRD §20.1 #3).
     */
    public function index(Request $request): Response
    {
        $query = GuideProfile::with(['user', 'region', 'country', 'verifications']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('verification_status', $request->status);
        }

        if ($request->filled('q')) {
            $term = '%'.mb_strtolower($request->q).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw("LOWER(display_name) LIKE ?", [$term])
                    ->orWhereHas('user', function ($uq) use ($term) {
                        $uq->where('email', 'like', $term);
                    });
            });
        }

        $guides = $query->latest('updated_at')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Guides/Index', [
            'guides' => $guides,
            'filters' => [
                'status' => $request->get('status', 'all'),
                'q' => $request->get('q', ''),
            ],
            'statuses' => array_map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(app()->getLocale()),
            ], GuideVerificationStatus::cases()),
        ]);
    }

    /**
     * Inspect Guide KYC & Application (PRD §7.1).
     */
    public function show(GuideProfile $guide): Response
    {
        $guide->load([
            'user',
            'region.parent',
            'country',
            'verifications.reviewer',
            'destinations',
            'packages',
            'verifier',
        ]);

        return Inertia::render('Admin/Guides/Show', [
            'guide' => $guide,
            'user' => $guide->user,
            'verifications' => $guide->verifications,
            'destinations' => $guide->destinations,
            'packages' => $guide->packages,
        ]);
    }

    /**
     * Execute Verification Action.
     */
    public function action(Request $request, GuideProfile $guide): RedirectResponse
    {
        $validated = $request->validate([
            'action' => ['required', 'string', 'in:approve,start_review,request_revision,reject,suspend,reactivate'],
            'note' => ['nullable', 'string', 'max:1000'],
        ]);

        $admin = $request->user();
        $action = $validated['action'];
        $note = $validated['note'] ?? null;

        switch ($action) {
            case 'start_review':
                $this->verificationService->startReview($guide, $admin);
                $msg = 'Status verifikasi pandu wisata diubah ke Sedang Ditinjau.';
                break;
            case 'approve':
                $this->verificationService->approve($guide, $admin, $note);
                $msg = 'Pandu wisata berhasil diverifikasi dan lencana verified aktif!';
                break;
            case 'request_revision':
                if (empty($note)) {
                    return back()->withErrors(['note' => 'Catatan revisi wajib diisi jika meminta revisi ke tour guide.']);
                }
                $this->verificationService->requestRevision($guide, $admin, $note);
                $msg = 'Permintaan revisi berhasil dikirimkan ke tour guide.';
                break;
            case 'reject':
                if (empty($note)) {
                    return back()->withErrors(['note' => 'Alasan penolakan wajib diisi.']);
                }
                $this->verificationService->reject($guide, $admin, $note);
                $msg = 'Pendaftaran tour guide ditolak.';
                break;
            case 'suspend':
                if (empty($note)) {
                    return back()->withErrors(['note' => 'Alasan penangguhan wajib diisi.']);
                }
                $this->verificationService->suspend($guide, $admin, $note);
                $msg = 'Akun tour guide berhasil ditangguhkan.';
                break;
            case 'reactivate':
                $this->verificationService->reactivate($guide, $admin);
                $msg = 'Akun tour guide berhasil diaktifkan kembali.';
                break;
            default:
                abort(400);
        }

        return back()->with('success', $msg);
    }
}
