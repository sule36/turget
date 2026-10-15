<?php

namespace App\Http\Controllers\Guide;

use App\Enums\ListingStatus;
use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\TourPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class PackageController extends Controller
{
    /**
     * List Guide's Tour Packages (PRD §18.2).
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $guide = $user->guideProfile;

        if (! $guide) {
            return redirect()->route('guide.onboard');
        }

        $packages = $guide->packages()
            ->with('destination')
            ->latest('updated_at')
            ->paginate(10);

        return Inertia::render('Guide/Packages/Index', [
            'guide' => $guide,
            'packages' => $packages,
        ]);
    }

    /**
     * Create Tour Package.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $guide = $user->guideProfile;

        if (! $guide) {
            return redirect()->route('guide.onboard');
        }

        return Inertia::render('Guide/Packages/Form', [
            'guide' => $guide,
            'package' => null,
            'destinations' => Destination::select('id', 'name', 'slug')->get(),
        ]);
    }

    /**
     * Store new tour package.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $guide = $user->guideProfile;

        if (! $guide) {
            return redirect()->route('guide.onboard');
        }

        $validated = $request->validate([
            'destination_id' => ['nullable', 'exists:destinations,id'],
            'title.id' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'tagline.id' => ['nullable', 'string', 'max:255'],
            'tagline.en' => ['nullable', 'string', 'max:255'],
            'description.id' => ['required', 'string'],
            'description.en' => ['nullable', 'string'],
            'tour_type' => ['required', 'string'],
            'price_type' => ['required', 'string', 'in:per_person,per_group,per_day'],
            'base_price' => ['required', 'numeric', 'min:10000'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:48'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:30'],
            'min_participants' => ['required', 'integer', 'min:1'],
            'max_participants' => ['required', 'integer', 'min:1'],
            'meeting_point' => ['nullable', 'string'],
            'end_point' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string'],
            'itinerary' => ['nullable', 'array'],
            'inclusions.id' => ['nullable', 'array'],
            'inclusions.en' => ['nullable', 'array'],
            'exclusions.id' => ['nullable', 'array'],
            'exclusions.en' => ['nullable', 'array'],
            'submit_for_review' => ['nullable', 'boolean'],
        ]);

        $slug = Str::slug($validated['title']['id']).'-'.Str::random(4);
        $status = ! empty($validated['submit_for_review']) ? ListingStatus::Submitted : ListingStatus::Draft;

        $package = TourPackage::create([
            'guide_profile_id' => $guide->id,
            'destination_id' => $validated['destination_id'] ?? null,
            'slug' => $slug,
            'title' => $validated['title'],
            'tagline' => $validated['tagline'] ?? null,
            'description' => $validated['description'],
            'tour_type' => $validated['tour_type'],
            'price_type' => $validated['price_type'],
            'base_price' => $validated['base_price'],
            'duration_hours' => $validated['duration_hours'],
            'duration_days' => $validated['duration_days'],
            'min_participants' => $validated['min_participants'],
            'max_participants' => $validated['max_participants'],
            'meeting_point' => $validated['meeting_point'] ?? null,
            'end_point' => $validated['end_point'] ?? null,
            'cover_image' => $validated['cover_image'] ?? '/images/destinations/bali_hero.jpg',
            'itinerary' => $validated['itinerary'] ?? null,
            'inclusions' => $validated['inclusions'] ?? null,
            'exclusions' => $validated['exclusions'] ?? null,
            'status' => $status,
        ]);

        $msg = $status === ListingStatus::Submitted
            ? 'Paket perjalanan berhasil dibuat dan diajukan untuk moderasi.'
            : 'Paket perjalanan berhasil disimpan sebagai draf.';

        return redirect()->route('guide.packages.index')->with('success', $msg);
    }

    /**
     * Edit Tour Package.
     */
    public function edit(Request $request, TourPackage $package): Response|RedirectResponse
    {
        $user = $request->user();
        $guide = $user->guideProfile;

        if (! $guide || $package->guide_profile_id !== $guide->id) {
            abort(403, 'Akses paket tidak sah.');
        }

        return Inertia::render('Guide/Packages/Form', [
            'guide' => $guide,
            'package' => $package,
            'destinations' => Destination::select('id', 'name', 'slug')->get(),
        ]);
    }

    /**
     * Update Tour Package.
     */
    public function update(Request $request, TourPackage $package): RedirectResponse
    {
        $user = $request->user();
        $guide = $user->guideProfile;

        if (! $guide || $package->guide_profile_id !== $guide->id) {
            abort(403, 'Akses paket tidak sah.');
        }

        $validated = $request->validate([
            'destination_id' => ['nullable', 'exists:destinations,id'],
            'title.id' => ['required', 'string', 'max:255'],
            'title.en' => ['nullable', 'string', 'max:255'],
            'tagline.id' => ['nullable', 'string', 'max:255'],
            'tagline.en' => ['nullable', 'string', 'max:255'],
            'description.id' => ['required', 'string'],
            'description.en' => ['nullable', 'string'],
            'tour_type' => ['required', 'string'],
            'price_type' => ['required', 'string', 'in:per_person,per_group,per_day'],
            'base_price' => ['required', 'numeric', 'min:10000'],
            'duration_hours' => ['required', 'integer', 'min:1', 'max:48'],
            'duration_days' => ['required', 'integer', 'min:1', 'max:30'],
            'min_participants' => ['required', 'integer', 'min:1'],
            'max_participants' => ['required', 'integer', 'min:1'],
            'meeting_point' => ['nullable', 'string'],
            'end_point' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string'],
            'itinerary' => ['nullable', 'array'],
            'inclusions.id' => ['nullable', 'array'],
            'inclusions.en' => ['nullable', 'array'],
            'exclusions.id' => ['nullable', 'array'],
            'exclusions.en' => ['nullable', 'array'],
            'submit_for_review' => ['nullable', 'boolean'],
        ]);

        $status = $package->status;
        if (! empty($validated['submit_for_review'])) {
            $status = ListingStatus::Submitted;
        }

        $package->update([
            'destination_id' => $validated['destination_id'] ?? null,
            'title' => $validated['title'],
            'tagline' => $validated['tagline'] ?? null,
            'description' => $validated['description'],
            'tour_type' => $validated['tour_type'],
            'price_type' => $validated['price_type'],
            'base_price' => $validated['base_price'],
            'duration_hours' => $validated['duration_hours'],
            'duration_days' => $validated['duration_days'],
            'min_participants' => $validated['min_participants'],
            'max_participants' => $validated['max_participants'],
            'meeting_point' => $validated['meeting_point'] ?? null,
            'end_point' => $validated['end_point'] ?? null,
            'cover_image' => $validated['cover_image'] ?? $package->cover_image,
            'itinerary' => $validated['itinerary'] ?? null,
            'inclusions' => $validated['inclusions'] ?? null,
            'exclusions' => $validated['exclusions'] ?? null,
            'status' => $status,
        ]);

        return back()->with('success', 'Paket perjalanan berhasil diperbarui.');
    }

    /**
     * Delete Package.
     */
    public function destroy(Request $request, TourPackage $package): RedirectResponse
    {
        $user = $request->user();
        $guide = $user->guideProfile;

        if (! $guide || $package->guide_profile_id !== $guide->id) {
            abort(403);
        }

        $package->delete();

        return redirect()->route('guide.packages.index')->with('success', 'Paket berhasil dihapus.');
    }
}
