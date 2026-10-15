<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    guides: Object,
    filters: Object,
    statuses: Array,
});

const { t, tr } = useI18n();

const currentStatus = ref(props.filters.status || 'all');
const search = ref(props.filters.q || '');

function applyFilter() {
    router.get('/admin/guides', {
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
</script>

<template>
    <AdminLayout>
        <Head title="Verifikasi Tour Guide — Turget.id Admin" />

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.25rem;">
                    🛡️ Antrean Verifikasi Tour Guide (KYC)
                </h1>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Validasi dokumen identitas, nomor lisensi resmi pramuwisata (HPI), dan kelayakan pandu wisata lokal.
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
                        class="turget-pill"
                        :class="{ 'is-active': currentStatus === 'all' }"
                        @click="selectStatus('all')"
                    >
                        Semua Status ({{ guides.total }})
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

                <!-- Search -->
                <div style="min-width: 250px;">
                    <input
                        type="search"
                        v-model="search"
                        @keyup.enter="applyFilter"
                        placeholder="Cari nama atau email..."
                        class="turget-form-input"
                        style="padding: 0.55rem 0.85rem; font-size: 0.88rem;"
                    />
                </div>
            </div>
        </div>

        <!-- Table -->
        <div class="turget-table-container">
            <table class="turget-table">
                <thead>
                    <tr>
                        <th style="width: 60px;">Foto</th>
                        <th>Nama & Kontak</th>
                        <th>Wilayah Layanan</th>
                        <th>Pengalaman & Bahasa</th>
                        <th>Dokumen KYC</th>
                        <th>Status Verifikasi</th>
                        <th style="text-align: right; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="guide in guides.data" :key="guide.id">
                        <td>
                            <img
                                :src="guide.avatar_url || '/images/destinations/bali_hero.jpg'"
                                :alt="guide.display_name"
                                style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover; border: 1px solid var(--color-border-subtle);"
                            />
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.95rem;">
                                {{ guide.display_name }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">
                                {{ guide.user?.email }} • {{ guide.phone }}
                            </div>
                        </td>
                        <td>
                            <div style="font-weight: 600; font-size: 0.88rem;">
                                {{ tr(guide.region?.name) }}
                            </div>
                            <div style="font-size: 0.78rem; color: var(--color-text-muted);">
                                {{ tr(guide.country?.name) }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.85rem;">{{ guide.experience_years }} Thn Pengalaman</div>
                            <div style="font-size: 0.78rem; color: var(--color-text-muted);">
                                {{ guide.languages?.map(l => l.toUpperCase()).join(', ') }}
                            </div>
                        </td>
                        <td>
                            <span class="turget-badge" style="margin: 0; background: var(--color-blue-50); color: var(--color-blue-600); font-size: 0.75rem;">
                                📄 {{ guide.verifications?.length || 0 }} Dokumen Terunggah
                            </span>
                        </td>
                        <td>
                            <span
                                class="turget-status-badge"
                                :class="guide.badge_verified ? 'turget-status-published' : 'turget-status-in_review'"
                            >
                                ● {{ guide.verification_status }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                <Link
                                    :href="`/admin/guides/${guide.id}`"
                                    class="turget-btn turget-btn-primary turget-btn-sm"
                                >
                                    Periksa Berkas →
                                </Link>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="guides.data.length === 0">
                        <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--color-text-muted);">
                            Tidak ada permohonan tour guide dalam status ini.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="guides.links && guides.links.length > 3" class="turget-pagination">
                <div style="color: var(--color-text-muted);">
                    Menampilkan {{ guides.from || 0 }} - {{ guides.to || 0 }} dari {{ guides.total }} guide
                </div>
                <div class="turget-pagination-links">
                    <template v-for="(link, i) in guides.links" :key="i">
                        <span v-if="!link.url" class="turget-pagination-btn is-disabled" v-html="link.label" />
                        <Link v-else :href="link.url" class="turget-pagination-btn" :class="{ 'is-active': link.active }" v-html="link.label" preserve-scroll preserve-state />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
