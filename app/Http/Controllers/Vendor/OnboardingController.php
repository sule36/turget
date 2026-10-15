<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\VendorCategory;
use App\Enums\VendorVerificationStatus;
use App\Http\Controllers\Controller;
use App\Models\AdministrativeRegion;
use App\Models\Country;
use App\Models\VendorProfile;
use App\Models\VendorVerification;
use App\Services\VendorVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function __construct(
        protected VendorVerificationService $verificationService
    ) {}

    /**
     * Show Become a Vendor onboarding wizard (PRD §8.2, §9.3).
     */
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if ($user && $user->vendorProfile) {
            return redirect()->route('vendor.dashboard');
        }

        $categories = array_map(fn (VendorCategory $c) => [
            'value' => $c->value,
            'label_id' => $c->label('id'),
            'label_en' => $c->label('en'),
            'icon' => $c->icon(),
        ], VendorCategory::cases());

        return Inertia::render('Vendor/Onboard', [
            'countries' => Country::select('id', 'name')->get(),
            'regions' => AdministrativeRegion::with('parent:id,name')->select('id', 'country_id', 'parent_id', 'name', 'type')->get(),
            'categories' => $categories,
        ]);
    }

    /**
     * Store new vendor registration and KYC legal documents.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->vendorProfile) {
            return redirect()->route('vendor.dashboard');
        }

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'business_type' => ['required', 'string', 'in:individual,cv,pt,koperasi'],
            'owner_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'country_id' => ['required', 'exists:countries,id'],
            'region_id' => ['required', 'exists:administrative_regions,id'],
            'service_areas' => ['nullable', 'array'],
            'service_areas.*' => ['exists:administrative_regions,id'],
            'category' => ['required', 'string'],
            'description.id' => ['required', 'string'],
            'description.en' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'string'],
            'avatar_url' => ['nullable', 'string'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_holder' => ['required', 'string', 'max:255'],
            'cancellation_policy.id' => ['nullable', 'string'],
            'cancellation_policy.en' => ['nullable', 'string'],
            'opening_hours.id' => ['nullable', 'string'],
            'opening_hours.en' => ['nullable', 'string'],
            // KYC Legal Document fields
            'nib' => ['nullable', 'string', 'max:100'],
            'nib_file_path' => ['nullable', 'string'],
            'siup' => ['nullable', 'string', 'max:100'],
            'siup_file_path' => ['nullable', 'string'],
            'npwp' => ['nullable', 'string', 'max:100'],
            'npwp_file_path' => ['nullable', 'string'],
            'ktp_owner' => ['nullable', 'string', 'max:100'],
            'ktp_file_path' => ['nullable', 'string'],
        ]);

        $baseSlug = Str::slug($validated['business_name']);
        $slug = $baseSlug;
        $count = 1;
        while (VendorProfile::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$count++;
        }

        $vendor = new VendorProfile();
        $vendor->user_id = $user->id;
        $vendor->slug = $slug;
        $vendor->business_name = $validated['business_name'];
        $vendor->business_type = $validated['business_type'];
        $vendor->owner_name = $validated['owner_name'];
        $vendor->phone = $validated['phone'];
        $vendor->email = $validated['email'];
        $vendor->address = $validated['address'];
        $vendor->latitude = $validated['latitude'];
        $vendor->longitude = $validated['longitude'];
        $vendor->country_id = $validated['country_id'];
        $vendor->region_id = $validated['region_id'];
        $vendor->service_areas = $validated['service_areas'] ?? [$validated['region_id']];
        $vendor->category = VendorCategory::from($validated['category']);
        $vendor->description = $validated['description'];
        $vendor->cover_image_url = $validated['cover_image_url'] ?? null;
        $vendor->avatar_url = $validated['avatar_url'] ?? null;
        $vendor->bank_name = $validated['bank_name'];
        $vendor->bank_account_number = $validated['bank_account_number'];
        $vendor->bank_account_holder = $validated['bank_account_holder'];
        $vendor->cancellation_policy = $validated['cancellation_policy'] ?? null;
        $vendor->opening_hours = $validated['opening_hours'] ?? null;
        $vendor->verification_status = VendorVerificationStatus::Draft;
        $vendor->badge_verified = false;
        $vendor->is_active = true;
        $vendor->save();

        // KYC Verification Entry
        VendorVerification::create([
            'vendor_profile_id' => $vendor->id,
            'nib' => $validated['nib'] ?? null,
            'nib_file_path' => $validated['nib_file_path'] ?? null,
            'siup' => $validated['siup'] ?? null,
            'siup_file_path' => $validated['siup_file_path'] ?? null,
            'npwp' => $validated['npwp'] ?? null,
            'npwp_file_path' => $validated['npwp_file_path'] ?? null,
            'ktp_owner' => $validated['ktp_owner'] ?? null,
            'ktp_file_path' => $validated['ktp_file_path'] ?? null,
            'status' => VendorVerificationStatus::Submitted,
        ]);

        // Submit for verification review
        $this->verificationService->submit($vendor);

        if (! $user->hasRole('vendor')) {
            $user->assignRole('vendor');
        }

        return redirect()->route('vendor.dashboard')->with('success', 'Pendaftaran kemitraan vendor berhasil diajukan! Tim Turget akan memverifikasi dokumen Anda.');
    }
}
