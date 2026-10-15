<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    packages: Object,
    filters: Object,
    statuses: Array,
});

const { t, tr } = useI18n();

const currentStatus = ref(props.filters.status || 'all');
const moderationNote = ref('');

function applyFilter() {
    router.get('/admin/packages', {
        status: currentStatus.value !== 'all' ? currentStatus.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function selectStatus(statusVal) {
    currentStatus.value = statusVal;
    applyFilter();
}

function handleAction(pkgId, action) {
    if (!confirm(`Konfirmasi tindakan moderasi: "${action}"?`)) return;

    router.post(`/admin/packages/${pkgId}/action`, {
        action: action,
        note: moderationNote.value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            moderationNote.value = '';
        },
    });
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
        <Head title="Moderasi Paket Wisata — Turget.id Admin" />

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.25rem;">
                    🎒 Moderasi Paket Wisata (PRD §7.5)
                </h1>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Tinjau kelayakan isi paket, rincian agenda, transparansi harga, dan keaslian konten sebelum tayang di marketplace.
                </p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="turget-admin-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                <button
                    type="button"
                    class="turget-pill"
                    :class="{ 'is-active': currentStatus === 'all' }"
                    @click="selectStatus('all')"
                >
                    Semua Status ({{ packages.total }})
                </button>
                <button
                    v-for="st in statuses"
                    :key="st.value"
                    type="button"
                    class="turget-pill"
                    :class="{ 'is-active': currentStatus === st.value }"
                    @click="selectStatus(st.value)"
                >
                    {{ st.label }}
                </button>
            </div>
        </div>

        <!-- Table -->
        <div class="turget-table-container">
            <table class="turget-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Foto</th>
                        <th>Judul Paket & Tour Guide</th>
                        <th>Destinasi & Wilayah</th>
                        <th>Durasi & Kuota</th>
                        <th>Harga Dasar</th>
                        <th>Status Moderasi</th>
                        <th style="text-align: right; width: 180px;">Aksi Moderasi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="pkg in packages.data" :key="pkg.id">
                        <td>
                            <img
                                :src="pkg.cover_image || '/images/destinations/bali_hero.jpg'"
                                :alt="tr(pkg.title)"
                                style="width: 48px; height: 48px; border-radius: var(--radius-sm); object-fit: cover;"
                            />
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.95rem;">
                                {{ tr(pkg.title) }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">
                                Oleh: <strong style="color: var(--color-navy-950);">{{ pkg.guide?.display_name }}</strong>
                            </div>
                        </td>
                        <td>
                            <div v-if="pkg.destination" style="font-weight: 600; font-size: 0.88rem;">
                                {{ tr(pkg.destination.name) }}
                            </div>
                            <div style="font-size: 0.78rem; color: var(--color-text-muted);">
                                {{ tr(pkg.guide?.region?.name) }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">{{ pkg.duration_hours }} Jam ({{ pkg.duration_days }} Hari)</div>
                            <div style="font-size: 0.78rem; color: var(--color-text-muted);">
                                {{ pkg.min_participants }} - {{ pkg.max_participants }} Orang
                            </div>
                        </td>
                        <td>
                            <strong style="color: var(--color-blue-600);">
                                {{ formatPrice(pkg.base_price) }}
                            </strong>
                            <div style="font-size: 0.75rem; color: var(--color-text-muted);">
                                / {{ pkg.price_type === 'per_person' ? 'orang' : pkg.price_type }}
                            </div>
                        </td>
                        <td>
                            <span
                                class="turget-status-badge"
                                :class="pkg.status === 'published' ? 'turget-status-published' : 'turget-status-in_review'"
                            >
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
                                    title="Lihat Halaman Publik"
                                >
                                    ↗
                                </a>

                                <button
                                    v-if="pkg.status !== 'published'"
                                    type="button"
                                    @click="handleAction(pkg.id, 'approve')"
                                    class="turget-btn turget-btn-primary turget-btn-sm"
                                    style="background: var(--color-emerald-600);"
                                >
                                    Setujui
                                </button>

                                <button
                                    v-if="pkg.status === 'published'"
                                    type="button"
                                    @click="handleAction(pkg.id, 'pause')"
                                    class="turget-btn turget-btn-secondary turget-btn-sm"
                                >
                                    Jeda
                                </button>

                                <button
                                    v-if="pkg.status !== 'rejected'"
                                    type="button"
                                    @click="handleAction(pkg.id, 'reject')"
                                    class="turget-btn turget-btn-secondary turget-btn-sm"
                                    style="color: var(--color-coral-600);"
                                >
                                    Tolak
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="packages.data.length === 0">
                        <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--color-text-muted);">
                            Tidak ada paket wisata dalam antrean moderasi.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="packages.links && packages.links.length > 3" class="turget-pagination">
                <div style="color: var(--color-text-muted);">
                    Menampilkan {{ packages.from || 0 }} - {{ packages.to || 0 }} dari {{ packages.total }} paket
                </div>
                <div class="turget-pagination-links">
                    <template v-for="(link, i) in packages.links" :key="i">
                        <span v-if="!link.url" class="turget-pagination-btn is-disabled" v-html="link.label" />
                        <Link v-else :href="link.url" class="turget-pagination-btn" :class="{ 'is-active': link.active }" v-html="link.label" preserve-scroll preserve-state />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
