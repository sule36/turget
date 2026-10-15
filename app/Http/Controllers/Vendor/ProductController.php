<?php

namespace App\Http\Controllers\Vendor;

use App\Enums\ListingStatus;
use App\Enums\VendorCategory;
use App\Http\Controllers\Controller;
use App\Models\Destination;
use App\Models\VendorProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class ProductController extends Controller
{
    /**
     * Vendor's Products / Inventory List (PRD §9).
     */
    public function index(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $vendor = $user->vendorProfile;

        if (! $vendor) {
            return redirect()->route('vendor.onboard');
        }

        $products = $vendor->products()
            ->with('destination:id,name')
            ->latest('id')
            ->paginate(10);

        return Inertia::render('Vendor/Products/Index', [
            'vendor' => $vendor,
            'products' => $products,
        ]);
    }

    /**
     * Show form to create a new product/service listing.
     */
    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();
        $vendor = $user->vendorProfile;

        if (! $vendor) {
            return redirect()->route('vendor.onboard');
        }

        $categories = array_map(fn (VendorCategory $c) => [
            'value' => $c->value,
            'label_id' => $c->label('id'),
            'label_en' => $c->label('en'),
            'icon' => $c->icon(),
        ], VendorCategory::cases());

        $destinations = Destination::select('id', 'name', 'latitude', 'longitude')->orderBy('name->id')->get();

        return Inertia::render('Vendor/Products/Form', [
            'vendor' => $vendor,
            'product' => null,
            'categories' => $categories,
            'destinations' => $destinations,
            'priceUnits' => [
                ['value' => 'per_night', 'label_id' => 'Per Malam (Penginapan)', 'label_en' => 'Per Night'],
                ['value' => 'per_day', 'label_id' => 'Per Hari (Kendaraan/Alat)', 'label_en' => 'Per Day'],
                ['value' => 'per_person', 'label_id' => 'Per Orang (Tiket/Aktivitas)', 'label_en' => 'Per Person'],
                ['value' => 'per_session', 'label_id' => 'Per Sesi (Foto/Workshop)', 'label_en' => 'Per Session'],
                ['value' => 'per_unit', 'label_id' => 'Per Unit', 'label_en' => 'Per Unit'],
            ],
            'bookingModels' => [
                ['value' => 'instant', 'label_id' => 'Konfirmasi Instan', 'label_en' => 'Instant Confirmation'],
                ['value' => 'request_to_book', 'label_id' => 'Permintaan Pemesanan', 'label_en' => 'Request to Book'],
                ['value' => 'manual', 'label_id' => 'Inquiry & Koordinasi Manual', 'label_en' => 'Manual Inquiry'],
            ],
        ]);
    }

    /**
     * Store new product listing.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $vendor = $user->vendorProfile()->firstOrFail();

        $validated = $request->validate([
            'name.id' => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'description.id' => ['required', 'string'],
            'description.en' => ['nullable', 'string'],
            'category' => ['required', 'string'],
            'destination_id' => ['nullable', 'exists:destinations,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_unit' => ['required', 'string', 'in:per_night,per_day,per_person,per_session,per_unit'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'stock_quantity' => ['required', 'integer', 'min:1'],
            'booking_model' => ['required', 'string', 'in:instant,request_to_book,manual'],
            'inclusions.id' => ['nullable', 'array'],
            'inclusions.en' => ['nullable', 'array'],
            'exclusions.id' => ['nullable', 'array'],
            'exclusions.en' => ['nullable', 'array'],
            'terms.id' => ['nullable', 'string'],
            'terms.en' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string'],
            'gallery' => ['nullable', 'array'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $baseTitle = $validated['name']['id'] ?? $validated['name']['en'] ?? 'layanan';
        $baseSlug = Str::slug($baseTitle);
        $slug = $baseSlug;
        $counter = 1;
        while (VendorProduct::where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$counter++;
        }

        $product = new VendorProduct();
        $product->vendor_profile_id = $vendor->id;
        $product->destination_id = $validated['destination_id'] ?? null;
        $product->slug = $slug;
        $product->name = $validated['name'];
        $product->description = $validated['description'];
        $product->category = VendorCategory::from($validated['category']);
        $product->price = $validated['price'];
        $product->price_unit = $validated['price_unit'];
        $product->currency = 'IDR';
        $product->capacity = $validated['capacity'] ?? null;
        $product->stock_quantity = $validated['stock_quantity'];
        $product->booking_model = $validated['booking_model'];
        $product->inclusions = $validated['inclusions'] ?? null;
        $product->exclusions = $validated['exclusions'] ?? null;
        $product->terms = $validated['terms'] ?? null;
        $product->cover_image = $validated['cover_image'] ?? null;
        $product->gallery = $validated['gallery'] ?? null;
        $product->latitude = $validated['latitude'] ?? $vendor->latitude;
        $product->longitude = $validated['longitude'] ?? $vendor->longitude;
        $product->address = $validated['address'] ?? $vendor->address;
        $product->status = ($validated['is_published'] ?? false) ? ListingStatus::Published : ListingStatus::Draft;
        $product->rating_avg = 5.00;
        $product->reviews_count = 0;
        $product->is_featured = false;
        $product->save();

        return redirect()->route('vendor.products.index')->with('success', 'Produk layanan berhasil ditambahkan ke katalog!');
    }

    /**
     * Show form to edit an existing product.
     */
    public function edit(Request $request, VendorProduct $product): Response|RedirectResponse
    {
        $user = $request->user();
        $vendor = $user->vendorProfile;

        if (! $vendor || $product->vendor_profile_id !== $vendor->id) {
            abort(403);
        }

        $categories = array_map(fn (VendorCategory $c) => [
            'value' => $c->value,
            'label_id' => $c->label('id'),
            'label_en' => $c->label('en'),
            'icon' => $c->icon(),
        ], VendorCategory::cases());

        $destinations = Destination::select('id', 'name', 'latitude', 'longitude')->orderBy('name->id')->get();

        return Inertia::render('Vendor/Products/Form', [
            'vendor' => $vendor,
            'product' => $product,
            'categories' => $categories,
            'destinations' => $destinations,
            'priceUnits' => [
                ['value' => 'per_night', 'label_id' => 'Per Malam (Penginapan)', 'label_en' => 'Per Night'],
                ['value' => 'per_day', 'label_id' => 'Per Hari (Kendaraan/Alat)', 'label_en' => 'Per Day'],
                ['value' => 'per_person', 'label_id' => 'Per Orang (Tiket/Aktivitas)', 'label_en' => 'Per Person'],
                ['value' => 'per_session', 'label_id' => 'Per Sesi (Foto/Workshop)', 'label_en' => 'Per Session'],
                ['value' => 'per_unit', 'label_id' => 'Per Unit', 'label_en' => 'Per Unit'],
            ],
            'bookingModels' => [
                ['value' => 'instant', 'label_id' => 'Konfirmasi Instan', 'label_en' => 'Instant Confirmation'],
                ['value' => 'request_to_book', 'label_id' => 'Permintaan Pemesanan', 'label_en' => 'Request to Book'],
                ['value' => 'manual', 'label_id' => 'Inquiry & Koordinasi Manual', 'label_en' => 'Manual Inquiry'],
            ],
        ]);
    }

    /**
     * Update product.
     */
    public function update(Request $request, VendorProduct $product): RedirectResponse
    {
        $user = $request->user();
        $vendor = $user->vendorProfile;

        if (! $vendor || $product->vendor_profile_id !== $vendor->id) {
            abort(403);
        }

        $validated = $request->validate([
            'name.id' => ['required', 'string', 'max:255'],
            'name.en' => ['nullable', 'string', 'max:255'],
            'description.id' => ['required', 'string'],
            'description.en' => ['nullable', 'string'],
            'category' => ['required', 'string'],
            'destination_id' => ['nullable', 'exists:destinations,id'],
            'price' => ['required', 'numeric', 'min:0'],
            'price_unit' => ['required', 'string', 'in:per_night,per_day,per_person,per_session,per_unit'],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'stock_quantity' => ['required', 'integer', 'min:1'],
            'booking_model' => ['required', 'string', 'in:instant,request_to_book,manual'],
            'inclusions.id' => ['nullable', 'array'],
            'inclusions.en' => ['nullable', 'array'],
            'exclusions.id' => ['nullable', 'array'],
            'exclusions.en' => ['nullable', 'array'],
            'terms.id' => ['nullable', 'string'],
            'terms.en' => ['nullable', 'string'],
            'cover_image' => ['nullable', 'string'],
            'gallery' => ['nullable', 'array'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'address' => ['nullable', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $product->name = $validated['name'];
        $product->description = $validated['description'];
        $product->category = VendorCategory::from($validated['category']);
        $product->destination_id = $validated['destination_id'] ?? null;
        $product->price = $validated['price'];
        $product->price_unit = $validated['price_unit'];
        $product->capacity = $validated['capacity'] ?? null;
        $product->stock_quantity = $validated['stock_quantity'];
        $product->booking_model = $validated['booking_model'];
        $product->inclusions = $validated['inclusions'] ?? null;
        $product->exclusions = $validated['exclusions'] ?? null;
        $product->terms = $validated['terms'] ?? null;
        $product->cover_image = $validated['cover_image'] ?? $product->cover_image;
        $product->gallery = $validated['gallery'] ?? $product->gallery;
        $product->latitude = $validated['latitude'] ?? $product->latitude;
        $product->longitude = $validated['longitude'] ?? $product->longitude;
        $product->address = $validated['address'] ?? $product->address;
        $product->status = ($validated['is_published'] ?? false) ? ListingStatus::Published : ListingStatus::Draft;
        $product->save();

        return redirect()->route('vendor.products.index')->with('success', 'Produk layanan berhasil diperbarui.');
    }

    /**
     * Delete product.
     */
    public function destroy(Request $request, VendorProduct $product): RedirectResponse
    {
        $user = $request->user();
        $vendor = $user->vendorProfile;

        if (! $vendor || $product->vendor_profile_id !== $vendor->id) {
            abort(403);
        }

        $product->delete();

        return redirect()->route('vendor.products.index')->with('success', 'Produk layanan berhasil dihapus.');
    }
}
