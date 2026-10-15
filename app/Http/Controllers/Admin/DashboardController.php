<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\AdministrativeRegion;
use App\Models\Article;
use App\Models\AuditLog;
use App\Models\ContentRevision;
use App\Models\Country;
use App\Models\Destination;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = [
            'destinations_total' => Destination::count(),
            'destinations_published' => Destination::published()->count(),
            'destinations_in_review' => Destination::where('status', ContentStatus::InReview)->count(),
            'regions_count' => AdministrativeRegion::count(),
            'articles_count' => Article::count(),
            'articles_published' => Article::published()->count(),
            'guides_total' => \App\Models\GuideProfile::count(),
            'guides_verified' => \App\Models\GuideProfile::verified()->count(),
            'guides_pending' => \App\Models\GuideProfile::where('verification_status', \App\Enums\GuideVerificationStatus::Submitted)->count(),
            'packages_total' => \App\Models\TourPackage::count(),
        ];

        // Content items pending editorial review
        $pendingReviews = Destination::where('status', ContentStatus::InReview)
            ->with(['region', 'editor'])
            ->latest('updated_at')
            ->take(5)
            ->get()
            ->map(fn ($d) => [
                'id' => $d->id,
                'type' => 'destination',
                'name' => $d->tr('name'),
                'status' => $d->status->value,
                'editor' => $d->editor?->name ?? 'System',
                'updated_at' => $d->updated_at->diffForHumans(),
            ]);

        // Recent revisions
        $recentRevisions = ContentRevision::with('user')
            ->latest('id')
            ->take(8)
            ->get();

        // Recent audit logs
        $recentAuditLogs = AuditLog::with('user')
            ->latest('id')
            ->take(8)
            ->get();

        return Inertia::render('Admin/Dashboard', [
            'stats' => $stats,
            'pendingReviews' => $pendingReviews,
            'recentRevisions' => $recentRevisions,
            'recentAuditLogs' => $recentAuditLogs,
        ]);
    }
}
