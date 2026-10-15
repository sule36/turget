<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    vendor: Object,
    products: Object,
});

const { t, tr, locale } = useI18n();

function formatPrice(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
}

function deleteProduct(prod) {
    if (confirm(`Yakin ingin menghapus layanan "${prod.name?.id || prod.name}"?`)) {
        router.delete(`/vendor/products/${prod.id}`);
    }
}
</script>

<template>
    <PublicLayout>
        <Head title="Manajemen Layanan & Produk — Turget.id" />

        <section style="background: var(--color-surface-subtle); padding: 3rem 0 2rem; border-bottom: 1px solid var(--color-border-subtle);">
            <div class="turget-container">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <Link href="/vendor/dashboard" style="font-size: 0.88rem; color: var(--color-blue-600); text-decoration: none; margin-bottom: 0.5rem; display: inline-block;">
                            ← Kembali ke Dashboard
                        </Link>
                        <h1 style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: var(--color-navy-950); margin: 0 0 0.35rem;">
                            Katalog Layanan & Add-on Wisata
                        </h1>
                        <p style="color: var(--color-text-muted); font-size: 0.95rem; margin: 0;">
                            Kelola inventori kamar, rental kendaraan, tiket aktivitas, dan add-on paket tour Anda.
                        </p>
                    </div>

                    <Link href="/vendor/products/create" class="turget-btn turget-btn-primary" style="font-size: 0.9rem;">
                        + Tambah Layanan Baru
                    </Link>
                </div>
            </div>
        </section>

        <main class="turget-container" style="padding: 3rem 0 6rem;">
            <div class="turget-card" style="padding: 2rem;">
                <div v-if="products?.data?.length > 0" style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.92rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--color-border-subtle); color: var(--color-text-subtle); font-size: 0.78rem; text-transform: uppercase;">
                                <th style="padding: 0.85rem 1rem;">Layanan & Destinasi</th>
                                <th style="padding: 0.85rem 1rem;">Kategori</th>
                                <th style="padding: 0.85rem 1rem;">Tarif</th>
                                <th style="padding: 0.85rem 1rem;">Stok / Kapasitas</th>
                                <th style="padding: 0.85rem 1rem;">Model Pemesanan</th>
                                <th style="padding: 0.85rem 1rem;">Status</th>
                                <th style="padding: 0.85rem 1rem; text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="prod in products.data" :key="prod.id" style="border-bottom: 1px solid var(--color-border-subtle);">
                                <td style="padding: 1.15rem 1rem;">
                                    <div style="display: flex; align-items: center; gap: 0.85rem;">
                                        <div style="width: 3rem; height: 3rem; border-radius: var(--radius-md); overflow: hidden; background: var(--color-sand-200); flex-shrink: 0;">
                                            <img :src="prod.cover_image || 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=120&auto=format&fit=crop&q=80'" style="width: 100%; height: 100%; object-fit: cover;" />
                                        </div>
                                        <div>
                                            <div style="font-weight: 700; color: var(--color-navy-950);">
                                                {{ tr(prod.name) }}
                                            </div>
                                            <div v-if="prod.destination" style="font-size: 0.78rem; color: var(--color-blue-600);">
                                                📍 Dekat {{ tr(prod.destination.name) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1.15rem 1rem;">
                                    <span class="turget-badge" style="font-size: 0.75rem;">
                                        {{ prod.category }}
                                    </span>
                                </td>
                                <td style="padding: 1.15rem 1rem; font-weight: 800; color: var(--color-blue-600);">
                                    {{ formatPrice(prod.price) }}
                                    <div style="font-size: 0.75rem; font-weight: 500; color: var(--color-text-muted);">
                                        / {{ prod.price_unit }}
                                    </div>
                                </td>
                                <td style="padding: 1.15rem 1rem; color: var(--color-navy-900);">
                                    <div>{{ prod.stock_quantity }} Unit Tersedia</div>
                                    <div v-if="prod.capacity" style="font-size: 0.78rem; color: var(--color-text-muted);">
                                        Max {{ prod.capacity }} pax
                                    </div>
                                </td>
                                <td style="padding: 1.15rem 1rem;">
                                    <span v-if="prod.booking_model === 'instant'" style="color: var(--color-green-700); font-weight: 600; font-size: 0.82rem;">
                                        ⚡ Instan
                                    </span>
                                    <span v-else style="color: var(--color-blue-600); font-weight: 600; font-size: 0.82rem;">
                                        📅 Request
                                    </span>
                                </td>
                                <td style="padding: 1.15rem 1rem;">
                                    <span v-if="prod.status === 'published'" class="turget-badge turget-status-published" style="font-size: 0.75rem;">
                                        Publik
                                    </span>
                                    <span v-else class="turget-badge turget-status-draft" style="font-size: 0.75rem;">
                                        Draf
                                    </span>
                                </td>
                                <td style="padding: 1.15rem 1rem; text-align: right;">
                                    <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                        <Link :href="`/vendors/products/${prod.slug}`" class="turget-btn turget-btn-secondary" style="padding: 0.35rem 0.65rem; font-size: 0.78rem;">
                                            Pratinjau
                                        </Link>
                                        <Link :href="`/vendor/products/${prod.id}/edit`" class="turget-btn turget-btn-secondary" style="padding: 0.35rem 0.65rem; font-size: 0.78rem;">
                                            Edit
                                        </Link>
                                        <button type="button" class="turget-btn" style="padding: 0.35rem 0.65rem; font-size: 0.78rem; color: var(--color-red-600); border-color: rgba(220,38,38,0.3);" @click="deleteProduct(prod)">
                                            Hapus
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else style="text-align: center; padding: 4rem 1rem;">
                    <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">📦</div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        Belum Ada Layanan
                    </h3>
                    <p style="color: var(--color-text-muted); margin-bottom: 1.5rem;">
                        Tambahkan kamar villa, unit kendaraan rental, atau wahana aktivitas Anda sekarang.
                    </p>
                    <Link href="/vendor/products/create" class="turget-btn turget-btn-primary">
                        + Tambah Layanan Baru
                    </Link>
                </div>
            </div>
        </main>
    </PublicLayout>
</template>
