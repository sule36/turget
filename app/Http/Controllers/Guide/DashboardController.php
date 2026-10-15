<?php

namespace App\Http\Controllers\Guide;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeRegion;
use App\Models\Country;
use App\Models\Destination;
use App\Models\GuideProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Guide Portal Overview (PRD §18.1).
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $guide = $user->guideProfile;

        if (! $guide) {
            return redirect()->route('guide.onboard');
        }

        $guide->load([
            'region',
            'country',
            'verifications',
            'packages' => function ($q) {
                $q->latest()->take(5);
            },
        ]);

        return Inertia::render('Guide/Dashboard', [
            'guide' => $guide,
            'stats' => [
                'total_packages' => $guide->packages()->count(),
                'active_packages' => $guide->packages()->published()->count(),
                'trips_completed' => $guide->trips_completed_count,
                'rating_avg' => $guide->rating_avg,
                'reviews_count' => $guide->reviews_count,
            ],
            'packages' => $guide->packages,
            'verifications' => $guide->verifications,
        ]);
    }

    /**
     * Edit Guide Profile.
     */
    public function editProfile(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $guide = $user->guideProfile;

        if (! $guide) {
            return redirect()->route('guide.onboard');
        }

        $guide->load(['destinations']);

        return Inertia::render('Guide/Profile', [
            'guide' => array_merge($guide->toArray(), [
                'destination_ids' => $guide->destinations->pluck('id')->toArray(),
            ]),
            'countries' => Country::select('id', 'name')->get(),
            'regions' => AdministrativeRegion::with('parent:id,name')->select('id', 'country_id', 'parent_id', 'name', 'type')->get(),
            'destinations' => Destination::select('id', 'region_id', 'name', 'slug')->get(),
        ]);
    }

    /**
     * Update Guide Profile.
     */
    public function updateProfile(Request $request): RedirectResponse
    {
        $user = $request->user();
        $guide = $user->guideProfile;

        if (! $guide) {
            return redirect()->route('guide.onboard');
        }

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'country_id' => ['required', 'exists:countries,id'],
            'region_id' => ['required', 'exists:administrative_regions,id'],
            'service_areas' => ['nullable', 'array'],
            'service_areas.*' => ['exists:administrative_regions,id'],
            'languages' => ['required', 'array', 'min:1'],
            'specializations' => ['required', 'array', 'min:1'],
            'experience_years' => ['required', 'integer', 'min:0', 'max:60'],
            'price_per_day' => ['required', 'numeric', 'min:0'],
            'tagline.id' => ['required', 'string', 'max:255'],
            'tagline.en' => ['nullable', 'string', 'max:255'],
            'bio.id' => ['required', 'string'],
            'bio.en' => ['nullable', 'string'],
            'avatar_url' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'string'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_holder' => ['required', 'string', 'max:100'],
            'destination_ids' => ['nullable', 'array'],
            'destination_ids.*' => ['exists:destinations,id'],
        ]);

        $guide->update([
            'display_name' => $validated['display_name'],
            'phone' => $validated['phone'],
            'country_id' => $validated['country_id'],
            'region_id' => $validated['region_id'],
            'service_areas' => $validated['service_areas'] ?? [$validated['region_id']],
            'languages' => $validated['languages'],
            'specializations' => $validated['specializations'],
            'experience_years' => $validated['experience_years'],
            'price_per_day' => $validated['price_per_day'],
            'tagline' => $validated['tagline'],
            'bio' => $validated['bio'],
            'avatar_url' => $validated['avatar_url'] ?? $guide->avatar_url,
            'cover_image_url' => $validated['cover_image_url'] ?? $guide->cover_image_url,
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_account_holder' => $validated['bank_account_holder'],
        ]);

        if (isset($validated['destination_ids'])) {
            $guide->destinations()->sync($validated['destination_ids']);
        }

        return back()->with('success', 'Profil tour guide Anda berhasil diperbarui!');
    }
}
