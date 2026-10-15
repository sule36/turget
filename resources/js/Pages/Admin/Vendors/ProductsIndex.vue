<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    products: Object,
    filters: Object,
    categories: Array,
});

const { t, tr, locale } = useI18n();

const currentStatus = ref(props.filters.status || 'all');
const currentCategory = ref(props.filters.category || 'all');
const search = ref(props.filters.q || '');

function applyFilter() {
    router.get('/admin/vendor-products', {
        status: currentStatus.value !== 'all' ? currentStatus.value : undefined,
        category: currentCategory.value !== 'all' ? currentCategory.value : undefined,
        q: search.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function approveProduct(prod) {
    if (!confirm(`Publikasikan layanan "${tr(prod.name)}"?`)) return;
    router.post(`/admin/vendor-products/${prod.id}/approve`);
}

function archiveProduct(prod) {
    if (!confirm(`Arsipkan layanan "${tr(prod.name)}"?`)) return;
    router.post(`/admin/vendor-products/${prod.id}/archive`);
}

function formatPrice(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
}
</script>

<template>
    <AdminLayout>
        <Head title="Moderasi Katalog Layanan Vendor — Turget.id Admin" />

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.25rem;">
                    📦 Katalog Layanan, Penginapan & Add-on
                </h1>
                <p style="color: var(--color-text-muted); font-size: 0.95rem; margin: 0;">
                    Moderasi dan pantau seluruh kamar penginapan, armada rental, dan tiket aktivitas yang didaftarkan oleh vendor mitra.
                </p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="turget-admin-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: center;">
                <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                    <select v-model="currentStatus" class="turget-input" style="width: auto; padding: 0.45rem 1rem; font-size: 0.85rem;" @change="applyFilter">
                        <option value="all">Semua Status</option>
                        <option value="published">Publik</option>
                        <option value="draft">Draf</option>
                        <option value="archived">Diarsipkan</option>
                    </select>

                    <select v-model="currentCategory" class="turget-input" style="width: auto; padding: 0.45rem 1rem; font-size: 0.85rem;" @change="applyFilter">
                        <option value="all">Semua Kategori</option>
                        <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                            {{ cat.label }}
                        </option>
                    </select>
                </div>

                <div style="display: flex; gap: 0.5rem;">
                    <input
                        v-model="search"
                        type="text"
                        class="turget-input"
                        placeholder="Cari layanan atau nama vendor..."
                        style="font-size: 0.85rem; padding: 0.45rem 0.85rem; width: 250px;"
                        @keyup.enter="applyFilter"
                    />
                    <button type="button" class="turget-btn turget-btn-primary" style="padding: 0.45rem 0.9rem; font-size: 0.85rem;" @click="applyFilter">
                        Cari
                    </button>
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="turget-admin-card" style="padding: 0; overflow: hidden;">
            <div style="overflow-x: auto;">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.92rem;">
                    <thead>
                        <tr style="background: var(--color-surface-subtle); border-bottom: 1px solid var(--color-border-subtle); color: var(--color-text-subtle); font-size: 0.78rem; text-transform: uppercase;">
                            <th style="padding: 1rem 1.25rem;">Layanan & Destinasi</th>
                            <th style="padding: 1rem 1.25rem;">Vendor Penyedia</th>
                            <th style="padding: 1rem 1.25rem;">Kategori</th>
                            <th style="padding: 1rem 1.25rem;">Tarif</th>
                            <th style="padding: 1rem 1.25rem;">Status</th>
                            <th style="padding: 1rem 1.25rem; text-align: right;">Aksi Moderasi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="prod in products.data" :key="prod.id" style="border-bottom: 1px solid var(--color-border-subtle);">
                            <td style="padding: 1.15rem 1.25rem;">
                                <div style="display: flex; align-items: center; gap: 0.85rem;">
                                    <div style="width: 2.75rem; height: 2.75rem; border-radius: var(--radius-md); overflow: hidden; background: var(--color-sand-200); flex-shrink: 0;">
                                        <img :src="prod.cover_image || 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=100&auto=format&fit=crop&q=80'" style="width: 100%; height: 100%; object-fit: cover;" />
                                    </div>
                                    <div>
                                        <div style="font-weight: 700; color: var(--color-navy-950);">
                                            {{ tr(prod.name) }}
                                        </div>
                                        <div v-if="prod.destination" style="font-size: 0.78rem; color: var(--color-blue-600);">
                                            📍 Terkait: {{ tr(prod.destination.name) }}
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td style="padding: 1.15rem 1.25rem;">
                                <Link v-if="prod.vendor" :href="`/admin/vendors/${prod.vendor.id}`" style="font-weight: 600; color: var(--color-navy-950); text-decoration: none;">
                                    {{ prod.vendor.business_name }}
                                </Link>
                            </td>
                            <td style="padding: 1.15rem 1.25rem;">
                                <span class="turget-badge" style="font-size: 0.75rem;">
                                    {{ prod.category }}
                                </span>
                            </td>
                            <td style="padding: 1.15rem 1.25rem; font-weight: 800; color: var(--color-blue-600);">
                                {{ formatPrice(prod.price) }}
                                <span style="font-size: 0.75rem; font-weight: 400; color: var(--color-text-muted);">/ {{ prod.price_unit }}</span>
                            </td>
                            <td style="padding: 1.15rem 1.25rem;">
                                <span v-if="prod.status === 'published'" class="turget-badge turget-status-published" style="font-size: 0.75rem;">
                                    Publik
                                </span>
                                <span v-else-if="prod.status === 'archived'" class="turget-badge turget-status-archived" style="font-size: 0.75rem;">
                                    Arsip
                                </span>
                                <span v-else class="turget-badge turget-status-draft" style="font-size: 0.75rem;">
                                    Draf
                                </span>
                            </td>
                            <td style="padding: 1.15rem 1.25rem; text-align: right;">
                                <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <button
                                        v-if="prod.status !== 'published'"
                                        type="button"
                                        class="turget-btn turget-btn-primary"
                                        style="padding: 0.35rem 0.75rem; font-size: 0.78rem; background: var(--color-green-600); border-color: var(--color-green-600);"
                                        @click="approveProduct(prod)"
                                    >
                                        Publikasikan
                                    </button>
                                    <button
                                        v-if="prod.status === 'published'"
                                        type="button"
                                        class="turget-btn turget-btn-secondary"
                                        style="padding: 0.35rem 0.75rem; font-size: 0.78rem;"
                                        @click="archiveProduct(prod)"
                                    >
                                        Arsipkan
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="products.data.length === 0">
                            <td colspan="6" style="padding: 3rem; text-align: center; color: var(--color-text-muted);">
                                Tidak ada produk layanan yang ditemukan.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="products.links?.length > 3" style="display: flex; justify-content: center; gap: 0.5rem; padding: 1.5rem; border-top: 1px solid var(--color-border-subtle);">
                <Component
                    :is="link.url ? Link : 'span'"
                    v-for="(link, i) in products.links"
                    :key="i"
                    :href="link.url || undefined"
                    v-html="link.label"
                    class="turget-badge"
                    style="padding: 0.5rem 0.85rem; font-size: 0.85rem; text-decoration: none;"
                    :style="link.active ? 'background: var(--color-navy-950); color: white;' : 'background: white; color: var(--color-navy-800);'"
                />
            </div>
        </div>
    </AdminLayout>
</template>
