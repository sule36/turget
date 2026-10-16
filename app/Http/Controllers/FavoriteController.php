<?php

namespace App\Http\Controllers;

use App\Models\Destination;
use App\Models\Favorite;
use App\Models\GuideProfile;
use App\Models\TourPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FavoriteController extends Controller
{
    /**
     * List user's saved wishlist items.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $rawFavorites = Favorite::where('user_id', $user->id)
            ->latest()
            ->get();

        $guideIds = $rawFavorites->where('favoritable_type', 'guide')->pluck('favoritable_id');
        $packageIds = $rawFavorites->where('favoritable_type', 'package')->pluck('favoritable_id');
        $destinationIds = $rawFavorites->where('favoritable_type', 'destination')->pluck('favoritable_id');

        $guides = GuideProfile::whereIn('id', $guideIds)
            ->with(['region', 'country'])
            ->get()
            ->keyBy('id');

        $packages = TourPackage::whereIn('id', $packageIds)
            ->with(['guide', 'destination'])
            ->get()
            ->keyBy('id');

        $destinations = Destination::whereIn('id', $destinationIds)
            ->with(['region'])
            ->get()
            ->keyBy('id');

        $items = $rawFavorites->map(function ($fav) use ($guides, $packages, $destinations) {
            $item = match ($fav->favoritable_type) {
                'guide' => $guides->get($fav->favoritable_id),
                'package' => $packages->get($fav->favoritable_id),
                'destination' => $destinations->get($fav->favoritable_id),
                default => null,
            };

            if (! $item) {
                return null;
            }

            return [
                'id' => $fav->id,
                'type' => $fav->favoritable_type,
                'item_id' => $fav->favoritable_id,
                'created_at' => $fav->created_at->toIso8601String(),
                'data' => $item,
            ];
        })->filter()->values();

        return Inertia::render('Favorites/Index', [
            'favorites' => $items,
        ]);
    }

    /**
     * Toggle favorite status (Add if absent, Remove if present).
     */
    public function toggle(Request $request): JsonResponse|RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'type' => ['required', 'string', 'in:guide,package,destination'],
            'id' => ['required', 'integer'],
        ]);

        $existing = Favorite::where('user_id', $user->id)
            ->where('favoritable_type', $validated['type'])
            ->where('favoritable_id', $validated['id'])
            ->first();

        if ($existing) {
            $existing->delete();
            $isFavorited = false;
            $message = 'Dihapus dari daftar favorit Anda.';
        } else {
            Favorite::create([
                'user_id' => $user->id,
                'favoritable_type' => $validated['type'],
                'favoritable_id' => $validated['id'],
            ]);
            $isFavorited = true;
            $message = 'Ditambahkan ke daftar favorit Anda ❤️';
        }

        if ($request->wantsJson()) {
            return response()->json([
                'status' => 'ok',
                'is_favorited' => $isFavorited,
                'message' => $message,
            ]);
        }

        return back()->with('success', $message);
    }
}
