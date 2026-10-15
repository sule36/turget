<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    regions: Object,
});

const { t, tr } = useI18n();
</script>

<template>
    <AdminLayout>
        <Head title="Kelola Wilayah & Kota — Turget.id Admin" />

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.25rem;">
                    {{ t('cms.regions', 'Wilayah & Kota') }}
                </h1>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Struktur hierarki geografis: Negara → Provinsi/Wilayah Utama (Level 1) → Kabupaten/Kota (Level 2).
                </p>
            </div>

            <Link href="/admin/regions/create" class="turget-btn turget-btn-primary">
                + Tambah Wilayah
            </Link>
        </div>

        <div class="turget-table-container">
            <table class="turget-table">
                <thead>
                    <tr>
                        <th>Nama Wilayah</th>
                        <th>Tingkat / Level</th>
                        <th>Wilayah Induk (Parent)</th>
                        <th>Negara</th>
                        <th>Jumlah Destinasi</th>
                        <th>URL Slug</th>
                        <th style="text-align: right; width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="reg in regions.data" :key="reg.id">
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.95rem;">
                                    <span v-if="reg.level === 2" style="color: var(--color-text-muted); margin-right: 0.25rem;">↳</span>
                                    {{ tr(reg.name) }}
                                </div>
                                <span class="turget-badge" style="margin: 0; font-size: 0.7rem; text-transform: uppercase;">
                                    {{ reg.type }}
                                </span>
                            </div>
                            <div v-if="reg.tagline" style="font-size: 0.8rem; color: var(--color-text-muted);">
                                {{ tr(reg.tagline) }}
                            </div>
                        </td>
                        <td>
                            <span class="turget-badge" :style="reg.level === 1 ? 'background: var(--color-blue-50); color: var(--color-blue-600);' : 'background: var(--color-surface-subtle);'">
                                Level {{ reg.level }}
                            </span>
                        </td>
                        <td>
                            <div v-if="reg.parent" style="font-weight: 600;">
                                {{ tr(reg.parent.name) }}
                            </div>
                            <span v-else style="color: var(--color-text-subtle); font-size: 0.85rem;">
                                — (Wilayah Utama)
                            </span>
                        </td>
                        <td>
                            {{ tr(reg.country?.name) }}
                        </td>
                        <td>
                            <strong style="color: var(--color-blue-600);">
                                {{ reg.destinations_count }}
                            </strong> destinasi
                        </td>
                        <td>
                            <code style="font-size: 0.82rem; background: var(--color-surface-subtle); padding: 0.2rem 0.4rem; border-radius: var(--radius-sm);">
                                {{ reg.slug }}
                            </code>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                <Link
                                    :href="`/admin/regions/${reg.id}/edit`"
                                    class="turget-btn turget-btn-primary turget-btn-sm"
                                >
                                    Edit
                                </Link>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="regions.data.length === 0">
                        <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--color-text-muted);">
                            Belum ada data wilayah administratif.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="regions.links && regions.links.length > 3" class="turget-pagination">
                <div style="color: var(--color-text-muted);">
                    Menampilkan {{ regions.from || 0 }} - {{ regions.to || 0 }} dari {{ regions.total }} wilayah
                </div>
                <div class="turget-pagination-links">
                    <template v-for="(link, i) in regions.links" :key="i">
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
