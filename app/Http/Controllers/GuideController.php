<?php

namespace App\Http\Controllers;

use App\Models\AdministrativeRegion;
use App\Models\GuideProfile;
use App\Models\TourPackage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class GuideController extends Controller
{
    /**
     * Public Directory of Verified Tour Guides (PRD §7.2).
     */
    public function index(Request $request): Response
    {
        $query = GuideProfile::verified()
            ->with(['region.parent', 'country', 'destinations', 'packages' => function ($q) {
                $q->published()->take(2);
            }]);

        // Filter by Region
        if ($request->filled('region')) {
            $regionId = (int) $request->region;
            $query->inRegion($regionId);
        }

        // Filter by Specialization
        if ($request->filled('specialization')) {
            $spec = $request->specialization;
            $query->whereJsonContains('specializations', $spec);
        }

        // Filter by Language
        if ($request->filled('language')) {
            $lang = $request->language;
            $query->whereJsonContains('languages', $lang);
        }

        // Search Name or Tagline
        if ($request->filled('q')) {
            $term = '%'.mb_strtolower($request->q).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw("LOWER(display_name) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(tagline, '$.id'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(tagline, '$.en'))) LIKE ?", [$term]);
            });
        }

        $guides = $query->latest('rating_avg')->paginate(9)->withQueryString();

        $regions = AdministrativeRegion::topLevel()->with('children')->get();

        return Inertia::render('Guides/Index', [
            'guides' => $guides,
            'regions' => $regions,
            'filters' => [
                'region' => $request->get('region', ''),
                'specialization' => $request->get('specialization', ''),
                'language' => $request->get('language', ''),
                'q' => $request->get('q', ''),
            ],
            'specializationsList' => [
                ['value' => 'cultural', 'label_id' => 'Budaya & Sejarah', 'label_en' => 'Culture & Heritage'],
                ['value' => 'nature_adventure', 'label_id' => 'Alam & Petualangan', 'label_en' => 'Nature & Adventure'],
                ['value' => 'photography', 'label_id' => 'Fotografi & Konten', 'label_en' => 'Photography'],
                ['value' => 'culinary', 'label_id' => 'Kuliner Lokal', 'label_en' => 'Local Food & Culinary'],
                ['value' => 'trekking', 'label_id' => 'Trekking & Gunung', 'label_en' => 'Trekking & Mountains'],
                ['value' => 'marine', 'label_id' => 'Diving & Bahari', 'label_en' => 'Diving & Marine'],
            ],
        ]);
    }

    /**
     * Public Customizable Creator Landing Page for Guide (PRD §7.3).
     */
    public function show(GuideProfile $guide): Response
    {
        $guide->load([
            'region.parent',
            'country',
            'destinations',
            'packages' => function ($q) {
                $q->published();
            },
            'availabilities' => function ($q) {
                $q->where('date', '>=', now()->toDateString())
                    ->orderBy('date')
                    ->take(30);
            },
        ]);

        return Inertia::render('Guides/Show', [
            'guide' => $guide,
            'packages' => $guide->packages,
            'destinations' => $guide->destinations,
        ]);
    }

    /**
     * Public Tour Package Detail Page (PRD §8.1).
     */
    public function showPackage(TourPackage $package): Response
    {
        $package->load([
            'guide.region',
            'guide.country',
            'destination.region',
        ]);

        return Inertia::render('Guides/PackageShow', [
            'package' => $package,
            'guide' => $package->guide,
        ]);
    }
}
