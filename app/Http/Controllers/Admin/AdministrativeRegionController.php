<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdministrativeRegion;
use App\Models\Country;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AdministrativeRegionController extends Controller
{
    public function index(): Response
    {
        $regions = AdministrativeRegion::with(['country', 'parent'])
            ->withCount('destinations')
            ->orderBy('country_id')
            ->orderBy('level')
            ->orderBy('sort_order')
            ->paginate(20);

        return Inertia::render('Admin/Regions/Index', [
            'regions' => $regions,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Regions/Form', [
            'region' => null,
            'countries' => Country::select('id', 'name')->get(),
            'parentRegions' => AdministrativeRegion::topLevel()->select('id', 'name')->get(),
            'types' => AdministrativeRegion::TYPES,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'parent_id' => ['nullable', 'exists:administrative_regions,id'],
            'type' => ['required', 'string', 'in:'.implode(',', AdministrativeRegion::TYPES)],
            'slug' => ['nullable', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'tagline.id' => ['nullable', 'string', 'max:255'],
            'tagline.en' => ['nullable', 'string', 'max:255'],
            'summary.id' => ['nullable', 'string'],
            'summary.en' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $level = empty($validated['parent_id']) ? 1 : 2;
        $slug = $validated['slug'] ?? Str::slug($validated['name']['id']);

        AdministrativeRegion::create(array_merge($validated, [
            'slug' => $slug,
            'level' => $level,
            'status' => 'published',
            'published_at' => now(),
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]));

        return redirect()->route('admin.regions.index')->with('success', 'Wilayah berhasil ditambahkan.');
    }

    public function edit(AdministrativeRegion $region): Response
    {
        return Inertia::render('Admin/Regions/Form', [
            'region' => $region,
            'countries' => Country::select('id', 'name')->get(),
            'parentRegions' => AdministrativeRegion::topLevel()->where('id', '!=', $region->id)->select('id', 'name')->get(),
            'types' => AdministrativeRegion::TYPES,
        ]);
    }

    public function update(Request $request, AdministrativeRegion $region): RedirectResponse
    {
        $validated = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'parent_id' => ['nullable', 'exists:administrative_regions,id'],
            'type' => ['required', 'string', 'in:'.implode(',', AdministrativeRegion::TYPES)],
            'slug' => ['required', 'string', 'max:255'],
            'name.id' => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'tagline.id' => ['nullable', 'string', 'max:255'],
            'tagline.en' => ['nullable', 'string', 'max:255'],
            'summary.id' => ['nullable', 'string'],
            'summary.en' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
        ]);

        $region->update(array_merge($validated, [
            'level' => empty($validated['parent_id']) ? 1 : 2,
            'updated_by' => auth()->id(),
        ]));

        return back()->with('success', 'Wilayah berhasil diperbarui.');
    }
}
