<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    vendors: Object,
    filters: Object,
    statuses: Array,
});

const { t, tr, locale } = useI18n();

const currentStatus = ref(props.filters.status || 'all');
const search = ref(props.filters.q || '');

function applyFilter() {
    router.get('/admin/vendors', {
        status: currentStatus.value !== 'all' ? currentStatus.value : undefined,
        q: search.value || undefined,
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

function getBadgeClass(status) {
    const classes = {
        draft: 'turget-status-draft',
        submitted: 'turget-status-in_review',
        under_review: 'turget-status-in_review',
        need_revision: 'turget-status-archived',
        verified: 'turget-status-published',
        rejected: 'turget-status-archived',
        suspended: 'turget-status-archived',
    };
    return classes[status] || 'turget-status-draft';
}
</script>

<template>
    <AdminLayout>
        <Head title="Verifikasi Vendor Mitra — Turget.id Admin" />

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.25rem;">
                    🏨 Antrean Verifikasi Vendor & Mitra (KYC)
                </h1>
                <p style="color: var(--color-text-muted); font-size: 0.95rem; margin: 0;">
                    Tinjau legalitas badan usaha, NIB, SIUP, NPWP, dan titik koordinat operasional penyedia jasa wisata lokal.
                </p>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="turget-admin-card" style="margin-bottom: 1.5rem; padding: 1.25rem;">
            <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: center;">
                <!-- Status Pills -->
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; align-items: center;">
                    <button
                        type="button"
                        class="turget-badge"
                        style="cursor: pointer; padding: 0.45rem 0.9rem;"
                        :style="currentStatus === 'all' ? 'background: var(--color-navy-950); color: white;' : 'background: var(--color-surface-subtle); color: var(--color-navy-800);'"
                        @click="selectStatus('all')"
                    >
                        Semua Status
                    </button>
                    <button
                        v-for="s in statuses"
                        :key="s.value"
                        type="button"
                        class="turget-badge"
                        style="cursor: pointer; padding: 0.45rem 0.9rem;"
                        :style="currentStatus === s.value ? 'background: var(--color-blue-600); color: white;' : 'background: var(--color-surface-subtle); color: var(--color-navy-800);'"
                        @click="selectStatus(s.value)"
                    >
                        {{ s.label }}
                    </button>
                </div>

                <!-- Search -->
                <div style="display: flex; gap: 0.5rem;">
                    <input
                        v-model="search"
                        type="text"
                        class="turget-input"
                        placeholder="Cari nama usaha atau pemilik..."
                        style="font-size: 0.85rem; padding: 0.45rem 0.85rem; width: 240px;"
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
                            <th style="padding: 1rem 1.25rem;">Nama Usaha & PIC</th>
                            <th style="padding: 1rem 1.25rem;">Entitas & Kategori</th>
                            <th style="padding: 1rem 1.25rem;">Wilayah Operasional</th>
                            <th style="padding: 1rem 1.25rem;">Status KYC</th>
                            <th style="padding: 1rem 1.25rem;">Badge Verified</th>
                            <th style="padding: 1rem 1.25rem; text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-for="v in vendors.data" :key="v.id" style="border-bottom: 1px solid var(--color-border-subtle);">
                            <td style="padding: 1.15rem 1.25rem;">
                                <div style="font-weight: 700; color: var(--color-navy-950);">
                                    {{ v.business_name }}
                                </div>
                                <div style="font-size: 0.82rem; color: var(--color-text-muted);">
                                    👤 {{ v.owner_name }} • 📞 {{ v.phone }}
                                </div>
                            </td>
                            <td style="padding: 1.15rem 1.25rem;">
                                <div style="font-weight: 600; text-transform: uppercase; font-size: 0.82rem; color: var(--color-navy-900);">
                                    {{ v.business_type }}
                                </div>
                                <div style="font-size: 0.78rem; color: var(--color-blue-600); font-weight: 600;">
                                    {{ v.category }}
                                </div>
                            </td>
                            <td style="padding: 1.15rem 1.25rem; color: var(--color-navy-900);">
                                <div>{{ v.region?.name || 'Indonesia' }}</div>
                                <div style="font-size: 0.78rem; color: var(--color-text-subtle);">
                                    GPS: {{ v.latitude }}, {{ v.longitude }}
                                </div>
                            </td>
                            <td style="padding: 1.15rem 1.25rem;">
                                <span class="turget-badge" :class="getBadgeClass(v.verification_status)" style="font-size: 0.75rem;">
                                    {{ v.verification_status }}
                                </span>
                            </td>
                            <td style="padding: 1.15rem 1.25rem;">
                                <span v-if="v.badge_verified" style="color: var(--color-blue-600); font-weight: 700; font-size: 0.88rem;">
                                    🛡️ Terverifikasi
                                </span>
                                <span v-else style="color: var(--color-text-subtle); font-size: 0.82rem;">
                                    Belum
                                </span>
                            </td>
                            <td style="padding: 1.15rem 1.25rem; text-align: right;">
                                <Link :href="`/admin/vendors/${v.id}`" class="turget-btn turget-btn-primary" style="padding: 0.4rem 0.85rem; font-size: 0.82rem;">
                                    Inspeksi Dokumen →
                                </Link>
                            </td>
                        </tr>

                        <tr v-if="vendors.data.length === 0">
                            <td colspan="6" style="padding: 3rem; text-align: center; color: var(--color-text-muted);">
                                Tidak ada data vendor yang cocok dengan kriteria filter ini.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="vendors.links?.length > 3" style="display: flex; justify-content: center; gap: 0.5rem; padding: 1.5rem; border-top: 1px solid var(--color-border-subtle);">
                <Component
                    :is="link.url ? Link : 'span'"
                    v-for="(link, i) in vendors.links"
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
