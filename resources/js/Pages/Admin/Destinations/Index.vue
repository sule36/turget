<script setup>
import { ref, watch } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    destinations: Object,
    filters: Object,
    statuses: Array,
});

const { t, tr } = useI18n();

const search = ref(props.filters.q || '');
const currentStatus = ref(props.filters.status || 'all');

let searchDebounceTimeout = null;

function applyFilter() {
    router.get('/admin/destinations', {
        q: search.value || undefined,
        status: currentStatus.value !== 'all' ? currentStatus.value : undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function onSearchInput() {
    clearTimeout(searchDebounceTimeout);
    searchDebounceTimeout = setTimeout(() => {
        applyFilter();
    }, 300);
}

function selectStatus(statusVal) {
    currentStatus.value = statusVal;
    applyFilter();
}

function deleteDestination(id, name) {
    if (confirm(`Yakin ingin menghapus destinasi "${name}"? Tindakan ini tidak dapat dibatalkan.`)) {
        router.delete(`/admin/destinations/${id}`);
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Kelola Destinasi — Turget.id Admin" />

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.25rem;">
                    {{ t('cms.destinations', 'Destinasi Wisata') }}
                </h1>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Katalog objek wisata, profil sejarah, jam operasional, dan alur kurasi editorial.
                </p>
            </div>

            <Link href="/admin/destinations/create" class="turget-btn turget-btn-primary">
                + Tambah Destinasi
            </Link>
        </div>

        <!-- Filter & Search Bar -->
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
                        Semua Status ({{ destinations.total }})
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

                <!-- Search Input -->
                <div style="min-width: 260px;">
                    <input
                        type="search"
                        v-model="search"
                        @input="onSearchInput"
                        placeholder="Cari nama atau slug..."
                        class="turget-form-input"
                        style="padding: 0.55rem 0.9rem; font-size: 0.88rem;"
                    />
                </div>
            </div>
        </div>

        <!-- Destinations Table -->
        <div class="turget-table-container">
            <table class="turget-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Media</th>
                        <th>Nama & Tagline Destinasi</th>
                        <th>Wilayah / Lokasi</th>
                        <th>Kategori</th>
                        <th>Status Kurasi</th>
                        <th>Pembaruan Terakhir</th>
                        <th style="text-align: right; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="dest in destinations.data" :key="dest.id">
                        <td>
                            <img
                                :src="dest.cover_image || '/images/destinations/bali_hero.jpg'"
                                :alt="tr(dest.name)"
                                style="width: 52px; height: 52px; border-radius: var(--radius-md); object-fit: cover; border: 1px solid var(--color-border-subtle);"
                            />
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.95rem;">
                                {{ tr(dest.name) }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted); max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ tr(dest.tagline) || `Slug: /places/${dest.slug}` }}
                            </div>
                        </td>
                        <td>
                            <div style="font-size: 0.88rem; font-weight: 600;">
                                {{ tr(dest.region?.name) }}
                            </div>
                            <div v-if="dest.region?.parent" style="font-size: 0.78rem; color: var(--color-text-muted);">
                                {{ tr(dest.region.parent.name) }}
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.3rem;">
                                <span
                                    v-for="cat in dest.categories"
                                    :key="cat.id"
                                    class="turget-badge"
                                    style="margin: 0; padding: 0.15rem 0.45rem; font-size: 0.7rem; background: var(--color-surface-subtle);"
                                >
                                    {{ tr(cat.name) }}
                                </span>
                            </div>
                        </td>
                        <td>
                            <span
                                class="turget-status-badge"
                                :class="`turget-status-${dest.status}`"
                            >
                                ● {{ dest.status }}
                            </span>
                        </td>
                        <td>
                            <div style="font-size: 0.82rem; color: var(--color-navy-950);">
                                {{ new Date(dest.updated_at).toLocaleDateString() }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--color-text-muted);">
                                oleh {{ dest.editor?.name || 'Admin' }}
                            </div>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                <a
                                    v-if="dest.status === 'published'"
                                    :href="`/places/${dest.slug}`"
                                    target="_blank"
                                    class="turget-btn turget-btn-secondary turget-btn-sm"
                                    title="Lihat Halaman Publik"
                                >
                                    ↗
                                </a>
                                <Link
                                    :href="`/admin/destinations/${dest.id}/edit`"
                                    class="turget-btn turget-btn-primary turget-btn-sm"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    @click="deleteDestination(dest.id, tr(dest.name))"
                                    class="turget-btn turget-btn-secondary turget-btn-sm"
                                    style="color: var(--color-coral-600);"
                                    title="Hapus"
                                >
                                    ✕
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="destinations.data.length === 0">
                        <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--color-text-muted);">
                            Tidak ada destinasi yang sesuai dengan pencarian atau filter status.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination Links -->
            <div v-if="destinations.links && destinations.links.length > 3" class="turget-pagination">
                <div style="color: var(--color-text-muted);">
                    Menampilkan {{ destinations.from || 0 }} - {{ destinations.to || 0 }} dari {{ destinations.total }} destinasi
                </div>
                <div class="turget-pagination-links">
                    <template v-for="(link, i) in destinations.links" :key="i">
                        <span
                            v-if="!link.url"
                            class="turget-pagination-btn is-disabled"
                            v-html="link.label"
                        />
                        <Link
                            v-else
                            :href="link.url"
                            class="turget-pagination-btn"
                            :class="{ 'is-active': link.active }"
                            v-html="link.label"
                            preserve-scroll
                            preserve-state
                        />
                    </template>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
