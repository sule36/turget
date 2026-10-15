<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    articles: Object,
});

const { t, tr } = useI18n();

function deleteArticle(id, title) {
    if (confirm(`Hapus artikel "${title}"?`)) {
        router.delete(`/admin/articles/${id}`);
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Kelola Artikel Editorial — Turget.id Admin" />

        <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.25rem;">
                    {{ t('cms.articles', 'Artikel & Panduan Perjalanan') }}
                </h1>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Konten editorial resmi, kurasi etika budaya, tips logistik, dan panduan mendalam.
                </p>
            </div>

            <Link href="/admin/articles/create" class="turget-btn turget-btn-primary">
                + Tulis Artikel Baru
            </Link>
        </div>

        <div class="turget-table-container">
            <table class="turget-table">
                <thead>
                    <tr>
                        <th style="width: 70px;">Sampul</th>
                        <th>Judul & Ringkasan Artikel</th>
                        <th>Kategori</th>
                        <th>Penulis</th>
                        <th>Waktu Baca</th>
                        <th>Status</th>
                        <th style="text-align: right; width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="art in articles.data" :key="art.id">
                        <td>
                            <img
                                :src="art.cover_image || '/images/destinations/bali_hero.jpg'"
                                :alt="tr(art.title)"
                                style="width: 52px; height: 52px; border-radius: var(--radius-md); object-fit: cover; border: 1px solid var(--color-border-subtle);"
                            />
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.95rem;">
                                {{ tr(art.title) }}
                            </div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted); max-width: 340px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ tr(art.excerpt) || `Slug: /articles/${art.slug}` }}
                            </div>
                        </td>
                        <td>
                            <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); font-size: 0.75rem;">
                                {{ tr(art.category?.name) }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 600; font-size: 0.88rem;">
                                {{ art.author?.name || 'Redaksi Turget' }}
                            </div>
                        </td>
                        <td>
                            <span style="font-size: 0.85rem; color: var(--color-text-muted);">
                                ⏱ {{ art.reading_minutes }} menit
                            </span>
                        </td>
                        <td>
                            <span class="turget-status-badge" :class="`turget-status-${art.status}`">
                                ● {{ art.status }}
                            </span>
                        </td>
                        <td style="text-align: right;">
                            <div style="display: flex; gap: 0.4rem; justify-content: flex-end;">
                                <a
                                    :href="`/articles/${art.slug}`"
                                    target="_blank"
                                    class="turget-btn turget-btn-secondary turget-btn-sm"
                                    title="Lihat Publik"
                                >
                                    ↗
                                </a>
                                <Link
                                    :href="`/admin/articles/${art.id}/edit`"
                                    class="turget-btn turget-btn-primary turget-btn-sm"
                                >
                                    Edit
                                </Link>
                                <button
                                    type="button"
                                    @click="deleteArticle(art.id, tr(art.title))"
                                    class="turget-btn turget-btn-secondary turget-btn-sm"
                                    style="color: var(--color-coral-600);"
                                    title="Hapus"
                                >
                                    ✕
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="articles.data.length === 0">
                        <td colspan="7" style="text-align: center; padding: 3rem 1rem; color: var(--color-text-muted);">
                            Belum ada artikel yang diterbitkan.
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Pagination -->
            <div v-if="articles.links && articles.links.length > 3" class="turget-pagination">
                <div style="color: var(--color-text-muted);">
                    Menampilkan {{ articles.from || 0 }} - {{ articles.to || 0 }} dari {{ articles.total }} artikel
                </div>
                <div class="turget-pagination-links">
                    <template v-for="(link, i) in articles.links" :key="i">
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
