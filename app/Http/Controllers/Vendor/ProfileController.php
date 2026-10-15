<?php

namespace App\Http\Controllers\Vendor;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeRegion;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Edit Vendor Profile details.
     */
    public function edit(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $vendor = $user->vendorProfile()->with('region')->first();

        if (! $vendor) {
            return redirect()->route('vendor.onboard');
        }

        return Inertia::render('Vendor/Profile', [
            'vendor' => $vendor,
            'countries' => Country::select('id', 'name')->get(),
            'regions' => AdministrativeRegion::with('parent:id,name')->select('id', 'country_id', 'parent_id', 'name', 'type')->get(),
        ]);
    }

    /**
     * Update Vendor Profile.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        $vendor = $user->vendorProfile()->firstOrFail();

        $validated = $request->validate([
            'business_name' => ['required', 'string', 'max:255'],
            'owner_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'email' => ['required', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:500'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'region_id' => ['required', 'exists:administrative_regions,id'],
            'service_areas' => ['nullable', 'array'],
            'description.id' => ['required', 'string'],
            'description.en' => ['nullable', 'string'],
            'avatar_url' => ['nullable', 'string'],
            'cover_image_url' => ['nullable', 'string'],
            'gallery' => ['nullable', 'array'],
            'bank_name' => ['required', 'string', 'max:100'],
            'bank_account_number' => ['required', 'string', 'max:50'],
            'bank_account_holder' => ['required', 'string', 'max:255'],
            'cancellation_policy.id' => ['nullable', 'string'],
            'cancellation_policy.en' => ['nullable', 'string'],
            'opening_hours.id' => ['nullable', 'string'],
            'opening_hours.en' => ['nullable', 'string'],
        ]);

        $vendor->update($validated);

        return back()->with('success', 'Profil vendor berhasil diperbarui.');
    }
}
