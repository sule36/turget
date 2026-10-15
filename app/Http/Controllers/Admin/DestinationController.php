<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ContentStatus;
use App\Http\Controllers\Controller;
use App\Models\AdministrativeRegion;
use App\Models\AuditLog;
use App\Models\ContentRevision;
use App\Models\Country;
use App\Models\Destination;
use App\Models\DestinationCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class DestinationController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Destination::with(['region.parent', 'categories', 'editor']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $term = '%'.mb_strtolower($request->q).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.id'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", [$term])
                    ->orWhere('slug', 'like', $term);
            });
        }

        $destinations = $query->latest('updated_at')->paginate(12)->withQueryString();

        return Inertia::render('Admin/Destinations/Index', [
            'destinations' => $destinations,
            'filters' => [
                'status' => $request->get('status', 'all'),
                'q' => $request->get('q', ''),
            ],
            'statuses' => array_map(fn ($s) => [
                'value' => $s->value,
                'label' => $s->label(app()->getLocale()),
            ], ContentStatus::cases()),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Destinations/Form', [
            'destination' => null,
            'countries' => Country::select('id', 'name')->get(),
            'regions' => AdministrativeRegion::with('parent:id,name')->select('id', 'country_id', 'parent_id', 'name', 'type')->get(),
            'categories' => DestinationCategory::select('id', 'name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'region_id' => ['required', 'exists:administrative_regions,id'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:destinations,slug'],
            'name.id' => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'tagline.id' => ['nullable', 'string', 'max:255'],
            'tagline.en' => ['nullable', 'string', 'max:255'],
            'summary.id' => ['nullable', 'string'],
            'summary.en' => ['nullable', 'string'],
            'sections' => ['nullable', 'array'],
            'highlights.id' => ['nullable', 'array'],
            'highlights.en' => ['nullable', 'array'],
            'activities.id' => ['nullable', 'array'],
            'activities.en' => ['nullable', 'array'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'opening_hours.id' => ['nullable', 'string'],
            'opening_hours.en' => ['nullable', 'string'],
            'ticket_info.id' => ['nullable', 'string'],
            'ticket_info.en' => ['nullable', 'string'],
            'visit_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'cover_image' => ['nullable', 'string'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:destination_categories,id'],
        ]);

        $slug = $validated['slug'] ?? Str::slug($validated['name']['id']);

        $destination = Destination::create([
            'country_id' => $validated['country_id'],
            'region_id' => $validated['region_id'],
            'slug' => $slug,
            'name' => $validated['name'],
            'tagline' => $validated['tagline'] ?? null,
            'summary' => $validated['summary'] ?? null,
            'sections' => $validated['sections'] ?? null,
            'highlights' => $validated['highlights'] ?? null,
            'activities' => $validated['activities'] ?? null,
            'address' => $validated['address'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'opening_hours' => $validated['opening_hours'] ?? null,
            'ticket_info' => $validated['ticket_info'] ?? null,
            'visit_duration_minutes' => $validated['visit_duration_minutes'] ?? null,
            'cover_image' => $validated['cover_image'] ?? null,
            'status' => ContentStatus::Draft,
            'created_by' => auth()->id(),
            'updated_by' => auth()->id(),
        ]);

        if (! empty($validated['category_ids'])) {
            $destination->categories()->sync($validated['category_ids']);
        }

        ContentRevision::create([
            'revisionable_type' => Destination::class,
            'revisionable_id' => $destination->id,
            'user_id' => auth()->id(),
            'action' => 'created',
            'to_status' => ContentStatus::Draft->value,
            'note' => 'Destinasi baru dibuat',
            'snapshot' => $destination->toArray(),
        ]);

        AuditLog::record('destination.create', $destination);

        return redirect()->route('admin.destinations.edit', $destination->id)
            ->with('success', 'Destinasi berhasil disimpan sebagai draf.');
    }

    public function edit(Destination $destination): Response
    {
        $destination->load(['categories', 'revisions.user']);

        return Inertia::render('Admin/Destinations/Form', [
            'destination' => array_merge($destination->toArray(), [
                'category_ids' => $destination->categories->pluck('id')->toArray(),
                'available_actions' => $destination->status->availableActions(),
            ]),
            'countries' => Country::select('id', 'name')->get(),
            'regions' => AdministrativeRegion::with('parent:id,name')->select('id', 'country_id', 'parent_id', 'name', 'type')->get(),
            'categories' => DestinationCategory::select('id', 'name')->get(),
        ]);
    }

    public function update(Request $request, Destination $destination): RedirectResponse
    {
        $validated = $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'region_id' => ['required', 'exists:administrative_regions,id'],
            'slug' => ['required', 'string', 'max:255', 'unique:destinations,slug,'.$destination->id],
            'name.id' => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'tagline.id' => ['nullable', 'string', 'max:255'],
            'tagline.en' => ['nullable', 'string', 'max:255'],
            'summary.id' => ['nullable', 'string'],
            'summary.en' => ['nullable', 'string'],
            'sections' => ['nullable', 'array'],
            'highlights.id' => ['nullable', 'array'],
            'highlights.en' => ['nullable', 'array'],
            'activities.id' => ['nullable', 'array'],
            'activities.en' => ['nullable', 'array'],
            'address' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'opening_hours.id' => ['nullable', 'string'],
            'opening_hours.en' => ['nullable', 'string'],
            'ticket_info.id' => ['nullable', 'string'],
            'ticket_info.en' => ['nullable', 'string'],
            'visit_duration_minutes' => ['nullable', 'integer', 'min:1'],
            'cover_image' => ['nullable', 'string'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:destination_categories,id'],
        ]);

        $destination->update([
            'country_id' => $validated['country_id'],
            'region_id' => $validated['region_id'],
            'slug' => $validated['slug'],
            'name' => $validated['name'],
            'tagline' => $validated['tagline'] ?? null,
            'summary' => $validated['summary'] ?? null,
            'sections' => $validated['sections'] ?? null,
            'highlights' => $validated['highlights'] ?? null,
            'activities' => $validated['activities'] ?? null,
            'address' => $validated['address'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'opening_hours' => $validated['opening_hours'] ?? null,
            'ticket_info' => $validated['ticket_info'] ?? null,
            'visit_duration_minutes' => $validated['visit_duration_minutes'] ?? null,
            'cover_image' => $validated['cover_image'] ?? null,
            'updated_by' => auth()->id(),
        ]);

        if (isset($validated['category_ids'])) {
            $destination->categories()->sync($validated['category_ids']);
        }

        ContentRevision::create([
            'revisionable_type' => Destination::class,
            'revisionable_id' => $destination->id,
            'user_id' => auth()->id(),
            'action' => 'updated',
            'to_status' => $destination->status->value,
            'changes' => $destination->getChanges(),
            'snapshot' => $destination->toArray(),
        ]);

        AuditLog::record('destination.update', $destination);

        return back()->with('success', 'Perubahan destinasi berhasil disimpan.');
    }

    public function destroy(Destination $destination): RedirectResponse
    {
        $destination->delete();
        AuditLog::record('destination.delete', $destination);

        return redirect()->route('admin.destinations.index')
            ->with('success', 'Destinasi berhasil dihapus.');
    }
}
