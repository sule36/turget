<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    guide: Object,
    packages: Object,
});

const { t, tr } = useI18n();

function deletePackage(id, title) {
    if (confirm(`Yakin ingin menghapus paket wisata "${title}"?`)) {
        router.delete(`/guide/packages/${id}`);
    }
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
    <PublicLayout>
        <Head title="Kelola Paket Wisata — Turget.id" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container">
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
                    <div>
                        <Link href="/guide/dashboard" style="color: var(--color-blue-600); font-size: 0.88rem; font-weight: 600; text-decoration: none;">
                            ← Kembali ke Dashboard Guide
                        </Link>
                        <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950); margin-top: 0.25rem;">
                            Kelola Paket Wisata
                        </h1>
                        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                            Daftar paket tur perjalanan yang Anda tawarkan ke wisatawan.
                        </p>
                    </div>

                    <Link href="/guide/packages/create" class="turget-btn turget-btn-primary">
                        + Buat Paket Wisata Baru
                    </Link>
                </div>

                <div class="turget-table-container">
                    <table class="turget-table">
                        <thead>
                            <tr>
                                <th style="width: 70px;">Sampul</th>
                                <th>Nama Paket</th>
                                <th>Destinasi</th>
                                <th>Durasi</th>
                                <th>Harga Dasar</th>
                                <th>Status Moderasi</th>
                                <th style="text-align: right; width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="pkg in packages.data" :key="pkg.id">
                                <td>
                                    <img
                                        :src="pkg.cover_image || '/images/destinations/bali_hero.jpg'"
                                        :alt="tr(pkg.title)"
                                        style="width: 52px; height: 52px; border-radius: var(--radius-md); object-fit: cover;"
                                    />
                                </td>
                                <td>
                                    <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.95rem;">
                                        {{ tr(pkg.title) }}
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--color-text-muted); text-transform: capitalize;">
                                        Tipe: {{ pkg.tour_type }}
                                    </div>
                                </td>
                                <td>
                                    <span v-if="pkg.destination" style="font-weight: 600;">
                                        {{ tr(pkg.destination.name) }}
                                    </span>
                                    <span v-else style="color: var(--color-text-muted);">
                                        Multi-destinasi
                                    </span>
                                </td>
                                <td>
                                    {{ pkg.duration_hours }} Jam ({{ pkg.duration_days }} Hari)
                                </td>
                                <td>
                                    <strong style="color: var(--color-blue-600);">
                                        {{ formatPrice(pkg.base_price) }}
                                    </strong>
                                </td>
                                <td>
                                    <span class="turget-status-badge" :class="pkg.status === 'published' ? 'turget-status-published' : 'turget-status-in_review'">
                                        ● {{ pkg.status }}
                                    </span>
                                </td>
                                <td style="text-align: right;">
                                    <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                        <a
                                            v-if="pkg.status === 'published'"
                                            :href="`/packages/${pkg.slug}`"
                                            target="_blank"
                                            class="turget-btn turget-btn-secondary turget-btn-sm"
                                        >
                                            ↗
                                        </a>
                                        <Link :href="`/guide/packages/${pkg.id}/edit`" class="turget-btn turget-btn-primary turget-btn-sm">
                                            Edit
                                        </Link>
                                        <button
                                            type="button"
                                            @click="deletePackage(pkg.id, tr(pkg.title))"
                                            class="turget-btn turget-btn-secondary turget-btn-sm"
                                            style="color: var(--color-coral-600);"
                                        >
                                            ✕
                                        </button>
                                    </div>
                                </td>
                            </tr>

                            <tr v-if="packages.data.length === 0">
                                <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--color-text-muted);">
                                    Belum ada paket wisata yang dibuat.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
