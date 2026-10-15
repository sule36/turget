<?php

namespace App\Http\Controllers\Guide;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeRegion;
use App\Models\TripInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TripInquiryBoardController extends Controller
{
    /**
     * Guide Bidding & Opportunity Board (PRD §12).
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $guide = $user->guideProfile;

        if (! $guide) {
            return redirect()->route('guide.onboard');
        }

        $query = TripInquiry::open()
            ->with(['region', 'destination:id,name', 'user:id,name'])
            ->withCount('proposals');

        if ($request->filled('region')) {
            $query->where('region_id', (int) $request->region);
        }

        if ($request->filled('style')) {
            $query->where('travel_style', $request->style);
        }

        $inquiries = $query->latest('id')->paginate(12)->withQueryString();

        return Inertia::render('Guide/Inquiries/Index', [
            'guide' => $guide,
            'inquiries' => $inquiries,
            'regions' => AdministrativeRegion::topLevel()->with('children')->get(),
            'filters' => [
                'region' => $request->get('region', ''),
                'style' => $request->get('style', ''),
            ],
        ]);
    }

    /**
     * Guide views traveler trip inquiry details and submits proposal.
     */
    public function show(Request $request, TripInquiry $trip): Response|RedirectResponse
    {
        $user = $request->user();
        $guide = $user->guideProfile;

        if (! $guide) {
            return redirect()->route('guide.onboard');
        }

        $trip->load(['region.parent', 'destination', 'user:id,name']);

        // Check if guide already submitted proposal
        $existingProposal = $trip->proposals()->where('guide_profile_id', $guide->id)->first();

        return Inertia::render('Guide/Inquiries/Show', [
            'guide' => $guide,
            'trip' => $trip,
            'existingProposal' => $existingProposal,
        ]);
    }
}
