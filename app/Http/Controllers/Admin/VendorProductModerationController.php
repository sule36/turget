<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ListingStatus;
use App\Enums\VendorCategory;
use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\VendorProduct;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class VendorProductModerationController extends Controller
{
    /**
     * Admin Vendor Products moderation catalog (PRD §8.2, §20.1).
     */
    public function index(Request $request): Response
    {
        $query = VendorProduct::with(['vendor', 'destination']);

        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        if ($request->filled('category') && $request->category !== 'all') {
            $query->where('category', $request->category);
        }

        if ($request->filled('q')) {
            $term = '%'.mb_strtolower($request->q).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.id'))) LIKE ?", [$term])
                    ->orWhereRaw("LOWER(JSON_UNQUOTE(JSON_EXTRACT(name, '$.en'))) LIKE ?", [$term])
                    ->orWhereHas('vendor', function ($vq) use ($term) {
                        $vq->whereRaw("LOWER(business_name) LIKE ?", [$term]);
                    });
            });
        }

        $products = $query->latest('id')->paginate(15)->withQueryString();

        return Inertia::render('Admin/Vendors/ProductsIndex', [
            'products' => $products,
            'filters' => [
                'status' => $request->get('status', 'all'),
                'category' => $request->get('category', 'all'),
                'q' => $request->get('q', ''),
            ],
            'categories' => array_map(fn (VendorCategory $c) => [
                'value' => $c->value,
                'label' => $c->label(app()->getLocale()),
            ], VendorCategory::cases()),
        ]);
    }

    public function approve(Request $request, VendorProduct $product): RedirectResponse
    {
        $product->forceFill([
            'status' => ListingStatus::Published,
            'approved_at' => now(),
            'approved_by' => $request->user()->id,
            'moderation_note' => $request->input('note'),
        ])->save();

        AuditLog::record('vendor_product.published', $product);

        return back()->with('success', 'Produk layanan vendor telah disetujui & dipublikasikan.');
    }

    public function archive(Request $request, VendorProduct $product): RedirectResponse
    {
        $product->forceFill([
            'status' => ListingStatus::Archived,
            'moderation_note' => $request->input('note', 'Diarsipkan oleh admin'),
        ])->save();

        AuditLog::record('vendor_product.archived', $product);

        return back()->with('success', 'Produk layanan vendor telah diarsipkan.');
    }
}
