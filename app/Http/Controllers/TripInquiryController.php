<?php

namespace App\Http\Controllers;

use App\Enums\TripInquiryStatus;
use App\Models\AdministrativeRegion;
use App\Models\Destination;
use App\Models\TripInquiry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TripInquiryController extends Controller
{
    /**
     * Show interactive trip builder (PRD §11).
     */
    public function create(): Response
    {
        $regions = AdministrativeRegion::topLevel()->with('children')->get();
        $destinations = Destination::published()->select('id', 'region_id', 'name', 'slug')->get();

        return Inertia::render('Trips/Create', [
            'regions' => $regions,
            'destinations' => $destinations,
            'travelStyles' => [
                ['value' => 'cultural', 'label_id' => 'Budaya & Sejarah', 'label_en' => 'Culture & Heritage', 'icon' => '🏛️'],
                ['value' => 'adventure', 'label_id' => 'Alam & Petualangan', 'label_en' => 'Nature & Adventure', 'icon' => '🧗'],
                ['value' => 'culinary', 'label_id' => 'Kuliner Otentik', 'label_en' => 'Local Food & Culinary', 'icon' => '🍜'],
                ['value' => 'relaxed', 'label_id' => 'Santai & Keluarga', 'label_en' => 'Relaxed & Family', 'icon' => '🏖️'],
                ['value' => 'photography', 'label_id' => 'Fotografi & Konten', 'label_en' => 'Photography & Spots', 'icon' => '📸'],
                ['value' => 'luxury', 'label_id' => 'Mewah & Eksklusif', 'label_en' => 'Luxury & Exclusive', 'icon' => '💎'],
            ],
        ]);
    }

    /**
     * Store customized trip request and open for guide bidding.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'region_id' => ['required', 'exists:administrative_regions,id'],
            'destination_id' => ['nullable', 'exists:destinations,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:30'],
            'adults_count' => ['required', 'integer', 'min:1', 'max:50'],
            'children_count' => ['nullable', 'integer', 'min:0', 'max:20'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'gte:budget_min'],
            'travel_style' => ['required', 'string'],
            'preferences' => ['nullable', 'array'],
            'special_requests' => ['nullable', 'string', 'max:2000'],
        ]);

        $inquiry = TripInquiry::create([
            'user_id' => $request->user()->id,
            'region_id' => $validated['region_id'],
            'destination_id' => $validated['destination_id'] ?? null,
            'title' => $validated['title'],
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'duration_days' => $validated['duration_days'],
            'adults_count' => $validated['adults_count'],
            'children_count' => $validated['children_count'] ?? 0,
            'budget_min' => $validated['budget_min'] ?? null,
            'budget_max' => $validated['budget_max'] ?? null,
            'currency' => 'IDR',
            'travel_style' => $validated['travel_style'],
            'preferences' => $validated['preferences'] ?? [],
            'special_requests' => $validated['special_requests'] ?? null,
            'status' => TripInquiryStatus::Open,
        ]);

        return redirect()->route('trips.show', $inquiry)->with('success', 'Rencana trip Anda berhasil diajukan! Tour guide lokal terverifikasi akan segera mengirimkan proposal penawaran.');
    }

    /**
     * Traveler inspects their trip inquiry and reviews guide proposals (PRD §11, §12).
     */
    public function show(Request $request, TripInquiry $trip): Response
    {
        $trip->load([
            'user:id,name',
            'region.parent',
            'destination:id,name,slug',
            'proposals' => function ($q) {
                $q->with(['guide.user:id,name', 'guide.region'])
                    ->latest('id');
            },
        ]);

        $isOwner = $request->user() && $request->user()->id === $trip->user_id;

        return Inertia::render('Trips/Show', [
            'trip' => $trip,
            'isOwner' => $isOwner,
        ]);
    }

    /**
     * Traveler's trip planning history.
     */
    public function myTrips(Request $request): Response
    {
        $trips = TripInquiry::where('user_id', $request->user()->id)
            ->with(['region', 'destination:id,name', 'proposals'])
            ->withCount('proposals')
            ->latest('id')
            ->paginate(10);

        return Inertia::render('Trips/MyTrips', [
            'trips' => $trips,
        ]);
    }
}
