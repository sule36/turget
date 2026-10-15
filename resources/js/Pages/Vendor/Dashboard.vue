<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    vendor: Object,
    stats: Object,
    latestVerification: Object,
    recentProducts: Array,
});

const { t, tr, locale } = useI18n();

function formatPrice(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
}
</script>

<template>
    <PublicLayout>
        <Head title="Dashboard Mitra Vendor — Turget.id" />

        <!-- Dashboard Header -->
        <section style="background: var(--color-surface-subtle); padding: 3rem 0 2rem; border-bottom: 1px solid var(--color-border-subtle);">
            <div class="turget-container">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                            <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); border-color: rgba(37,99,235,0.2);">
                                🏨 Portal Mitra Vendor
                            </span>
                            <span v-if="vendor.badge_verified" class="turget-badge" style="background: var(--color-green-50); color: var(--color-green-700); border-color: rgba(16,185,129,0.3);">
                                🛡️ Terverifikasi
                            </span>
                        </div>
                        <h1 style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: var(--color-navy-950); margin: 0 0 0.35rem;">
                            {{ vendor.business_name }}
                        </h1>
                        <p style="color: var(--color-text-muted); font-size: 0.95rem; margin: 0;">
                            {{ vendor.business_type?.toUpperCase() }} • {{ vendor.address }}, {{ vendor.region?.name }}
                        </p>
                    </div>

                    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                        <Link href="/payouts" class="turget-btn turget-btn-secondary" style="font-size: 0.88rem; font-weight: 700;">
                            💰 Dompet Payout
                        </Link>
                        <Link :href="`/vendors/${vendor.slug}`" class="turget-btn turget-btn-secondary" style="font-size: 0.88rem;">
                            🌐 Lihat Storefront
                        </Link>
                        <Link href="/vendor/products/create" class="turget-btn turget-btn-primary" style="font-size: 0.88rem;">
                            + Tambah Layanan
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <main class="turget-container" style="padding: 2.5rem 0 5rem;">
            <!-- Verification Alert Banner -->
            <div
                v-if="vendor.verification_status !== 'verified'"
                class="turget-card"
                style="padding: 1.5rem 2rem; margin-bottom: 2.5rem; border-left: 5px solid;"
                :style="vendor.verification_status === 'need_revision' ? 'border-color: var(--color-red-600); background: #FEF2F2;' : 'border-color: var(--color-blue-600); background: var(--color-blue-50);'"
            >
                <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; flex-wrap: wrap;">
                    <div>
                        <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin: 0 0 0.35rem;">
                            Status Kemitraan: {{ stats.verification_label }}
                        </h3>
                        <p v-if="vendor.verification_status === 'need_revision'" style="color: var(--color-red-800); font-size: 0.92rem; margin: 0;">
                            <strong>Catatan Tim Review:</strong> {{ vendor.verification_note || 'Mohon lengkapi perbaikan dokumen legalitas.' }}
                        </p>
                        <p v-else style="color: var(--color-navy-800); font-size: 0.92rem; margin: 0;">
                            Dokumen KYC dan profil usaha Anda sedang ditinjau oleh Tim Compliance Turget.id. Setelah disetujui, badge verifikasi akan otomatis tampil pada profil publik Anda.
                        </p>
                    </div>
                    <Link href="/vendor/profile" class="turget-btn turget-btn-secondary" style="font-size: 0.82rem; background: white;">
                        Perbarui Data Profil →
                    </Link>
                </div>
            </div>

            <!-- Stats Metric Cards -->
            <div class="turget-grid-4" style="gap: 1.5rem; margin-bottom: 3rem;">
                <div class="turget-card" style="padding: 1.5rem;">
                    <div style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-subtle); margin-bottom: 0.5rem;">
                        Total Layanan Aktif
                    </div>
                    <div style="font-size: 2rem; font-weight: 800; color: var(--color-navy-950);">
                        {{ stats.published_products }}
                        <span style="font-size: 0.9rem; font-weight: 500; color: var(--color-text-muted);">/ {{ stats.total_products }} total</span>
                    </div>
                </div>

                <div class="turget-card" style="padding: 1.5rem;">
                    <div style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-subtle); margin-bottom: 0.5rem;">
                        Rating Kepuasan
                    </div>
                    <div style="font-size: 2rem; font-weight: 800; color: var(--color-blue-600);">
                        ⭐ {{ Number(stats.rating_avg).toFixed(1) }}
                    </div>
                </div>

                <div class="turget-card" style="padding: 1.5rem;">
                    <div style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-subtle); margin-bottom: 0.5rem;">
                        Total Ulasan Wisatawan
                    </div>
                    <div style="font-size: 2rem; font-weight: 800; color: var(--color-navy-950);">
                        {{ stats.reviews_count }}
                    </div>
                </div>

                <div class="turget-card" style="padding: 1.5rem;">
                    <div style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-subtle); margin-bottom: 0.5rem;">
                        Pemesanan Selesai
                    </div>
                    <div style="font-size: 2rem; font-weight: 800; color: var(--color-green-600);">
                        {{ stats.bookings_completed }}
                    </div>
                </div>
            </div>

            <!-- Products Inventory Table -->
            <div class="turget-card" style="padding: 2rem;">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                    <div>
                        <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--color-navy-950); margin: 0 0 0.25rem;">
                            Katalog Layanan & Add-on Anda
                        </h2>
                        <p style="font-size: 0.88rem; color: var(--color-text-muted); margin: 0;">
                            Kelola ketersediaan kamar, armada kendaraan, tiket aktivitas, dan add-on paket wisata.
                        </p>
                    </div>
                    <Link href="/vendor/products" class="turget-btn turget-btn-secondary" style="font-size: 0.85rem;">
                        Lihat Semua Produk →
                    </Link>
                </div>

                <div v-if="recentProducts?.length > 0" style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.92rem;">
                        <thead>
                            <tr style="border-bottom: 2px solid var(--color-border-subtle); color: var(--color-text-subtle); font-size: 0.78rem; text-transform: uppercase;">
                                <th style="padding: 0.75rem 1rem;">Layanan</th>
                                <th style="padding: 0.75rem 1rem;">Kategori</th>
                                <th style="padding: 0.75rem 1rem;">Tarif</th>
                                <th style="padding: 0.75rem 1rem;">Stok / Kapasitas</th>
                                <th style="padding: 0.75rem 1rem;">Status</th>
                                <th style="padding: 0.75rem 1rem; text-align: right;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="prod in recentProducts" :key="prod.id" style="border-bottom: 1px solid var(--color-border-subtle);">
                                <td style="padding: 1rem; font-weight: 600; color: var(--color-navy-950);">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <div style="width: 2.5rem; height: 2.5rem; border-radius: var(--radius-md); overflow: hidden; background: var(--color-sand-200); flex-shrink: 0;">
                                            <img :src="prod.cover_image || 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=100&auto=format&fit=crop&q=80'" style="width: 100%; height: 100%; object-fit: cover;" />
                                        </div>
                                        <div>
                                            <div>{{ tr(prod.name) }}</div>
                                            <div v-if="prod.destination" style="font-size: 0.75rem; color: var(--color-blue-600);">
                                                📍 {{ tr(prod.destination.name) }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td style="padding: 1rem;">
                                    <span class="turget-badge" style="font-size: 0.75rem;">
                                        {{ prod.category }}
                                    </span>
                                </td>
                                <td style="padding: 1rem; font-weight: 700; color: var(--color-blue-600);">
                                    {{ formatPrice(prod.price) }}
                                    <span style="font-size: 0.75rem; font-weight: 400; color: var(--color-text-muted);">/ {{ prod.price_unit }}</span>
                                </td>
                                <td style="padding: 1rem; color: var(--color-navy-900);">
                                    {{ prod.stock_quantity }} Unit
                                </td>
                                <td style="padding: 1rem;">
                                    <span v-if="prod.status === 'published'" class="turget-badge turget-status-published" style="font-size: 0.75rem;">
                                        Publik
                                    </span>
                                    <span v-else class="turget-badge turget-status-draft" style="font-size: 0.75rem;">
                                        Draf
                                    </span>
                                </td>
                                <td style="padding: 1rem; text-align: right;">
                                    <Link :href="`/vendor/products/${prod.id}/edit`" class="turget-btn turget-btn-secondary" style="padding: 0.35rem 0.75rem; font-size: 0.8rem;">
                                        Edit
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else style="text-align: center; padding: 3rem 1rem;">
                    <p style="color: var(--color-text-muted); margin-bottom: 1rem;">Anda belum menambahkan layanan ke katalog.</p>
                    <Link href="/vendor/products/create" class="turget-btn turget-btn-primary" style="font-size: 0.88rem;">
                        + Tambah Produk Pertama
                    </Link>
                </div>
            </div>
        </main>
    </PublicLayout>
</template>
