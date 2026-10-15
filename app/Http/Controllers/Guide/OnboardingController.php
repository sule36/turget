<?php

namespace App\Http\Controllers\Guide;

use App\Enums\GuideVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\AdministrativeRegion;
use App\Models\Country;
use App\Models\Destination;
use App\Models\GuideProfile;
use App\Models\GuideVerification;
use App\Services\GuideVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function __construct(
        protected GuideVerificationService $verificationService
    ) {}

    /**
     * Show Become a Tour Guide onboarding wizard (PRD §7.1).
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->guideProfile) {
            return redirect()->route('guide.dashboard');
        }

        return Inertia::render('Guide/Onboard', [
            'countries' => Country::select('id', 'name')->get(),
            'regions' => AdministrativeRegion::with('parent:id,name')->select('id', 'country_id', 'parent_id', 'name', 'type')->get(),
            'destinations' => Destination::select('id', 'region_id', 'name', 'slug')->get(),
        ]);
    }

    /**
     * Store new guide registration and submit KYC documents.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'display_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'country_id' => ['required', 'exists:countries,id'],
            'region_id' => ['required', 'exists:administrative_regions,id'],
            'service_areas' => ['nullable', 'array'],
            'service_areas.*' => ['exists:administrative_regions,id'],
            'languages' => ['required', 'array', 'min:1'],
            'languages.*' => ['string', 'max:10'],
            'specializations' => ['required', 'array', 'min:1'],
            'specializations.*' => ['string'],
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
            // KYC fields
            'id_card_number' => ['required', 'string', 'max:50'],
            'id_card_file' => ['nullable', 'string'],
            'guide_license_number' => ['nullable', 'string', 'max:50'],
            'guide_license_file' => ['nullable', 'string'],
        ]);

        $slug = Str::slug($validated['display_name']).'-'.Str::random(4);

        $guide = GuideProfile::create([
            'user_id' => $user->id,
            'slug' => $slug,
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
            'avatar_url' => $validated['avatar_url'] ?? '/images/destinations/bali_hero.jpg',
            'cover_image_url' => $validated['cover_image_url'] ?? '/images/destinations/bali_hero.jpg',
            'bank_name' => $validated['bank_name'],
            'bank_account_number' => $validated['bank_account_number'],
            'bank_account_holder' => $validated['bank_account_holder'],
            'verification_status' => GuideVerificationStatus::Submitted,
            'badge_verified' => false,
            'rating_avg' => 5.00,
            'reviews_count' => 0,
            'trips_completed_count' => 0,
        ]);

        if (! empty($validated['destination_ids'])) {
            $guide->destinations()->sync($validated['destination_ids']);
        }

        // Create KYC Record 1: Identity Card (KTP / Passport)
        GuideVerification::create([
            'guide_profile_id' => $guide->id,
            'document_type' => 'id_card',
            'document_number' => $validated['id_card_number'],
            'document_file_path' => $validated['id_card_file'] ?? '/storage/kyc/ktp_sample.jpg',
            'status' => 'pending',
        ]);

        // Create KYC Record 2: Guide License (if provided)
        if (! empty($validated['guide_license_number'])) {
            GuideVerification::create([
                'guide_profile_id' => $guide->id,
                'document_type' => 'guide_license',
                'document_number' => $validated['guide_license_number'],
                'document_file_path' => $validated['guide_license_file'] ?? '/storage/kyc/license_sample.jpg',
                'status' => 'pending',
            ]);
        }

        // Assign tour_guide role
        if (! $user->hasRole('tour_guide')) {
            $user->assignRole('tour_guide');
        }

        return redirect()->route('guide.dashboard')
            ->with('success', 'Pendaftaran profil tour guide berhasil dikirimkan dan saat ini sedang dalam antrean verifikasi!');
    }
}
