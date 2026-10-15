<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    article: Object,
    categories: Array,
});

const { t, tr } = useI18n();

const isEdit = computed(() => !!props.article?.id);
const activeTab = ref('id');

const form = useForm({
    article_category_id: props.article?.article_category_id || props.categories[0]?.id || null,
    slug: props.article?.slug || '',
    title: {
        id: props.article?.title?.id || '',
        en: props.article?.title?.en || '',
    },
    excerpt: {
        id: props.article?.excerpt?.id || '',
        en: props.article?.excerpt?.en || '',
    },
    body: {
        id: props.article?.body?.id || '',
        en: props.article?.body?.en || '',
    },
    reading_minutes: props.article?.reading_minutes || 5,
    cover_image: props.article?.cover_image || '/images/destinations/bali_hero.jpg',
});

function submit() {
    if (isEdit.value) {
        form.put(`/admin/articles/${props.article.id}`);
    } else {
        form.post('/admin/articles');
    }
}
</script>

<template>
    <AdminLayout>
        <Head :title="(isEdit ? 'Edit: ' + tr(article.title) : 'Tulis Artikel Baru') + ' — Turget.id Admin'" />

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                    <Link href="/admin/articles" style="color: var(--color-blue-600); font-size: 0.88rem; font-weight: 600; text-decoration: none;">
                        ← Kembali ke Daftar Artikel
                    </Link>
                </div>
                <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950);">
                    {{ isEdit ? 'Edit Artikel: ' + tr(article.title) : 'Tulis Panduan Editorial Baru' }}
                </h1>
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <a
                    v-if="isEdit"
                    :href="`/articles/${article.slug}`"
                    target="_blank"
                    class="turget-btn turget-btn-secondary"
                >
                    Lihat Publik ↗
                </a>
                <button
                    type="button"
                    @click="submit"
                    :disabled="form.processing"
                    class="turget-btn turget-btn-primary"
                >
                    {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Terbitkan Artikel') }}
                </button>
            </div>
        </div>

        <form @submit.prevent="submit" style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 2rem; align-items: start;">
            <!-- Article Body & Metadata -->
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <div class="turget-admin-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                        <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950);">
                            Konten Artikel
                        </h2>

                        <!-- Tabs -->
                        <div class="turget-tabs-nav" style="margin-bottom: 0;">
                            <button
                                type="button"
                                class="turget-tab-btn"
                                :class="{ 'is-active': activeTab === 'id' }"
                                @click="activeTab = 'id'"
                            >
                                🇮🇩 Bahasa Indonesia
                            </button>
                            <button
                                type="button"
                                class="turget-tab-btn"
                                :class="{ 'is-active': activeTab === 'en' }"
                                @click="activeTab = 'en'"
                            >
                                🇬🇧 English
                            </button>
                        </div>
                    </div>

                    <!-- ID Fields -->
                    <div v-show="activeTab === 'id'" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div>
                            <label class="turget-form-label">Judul Artikel (ID) *</label>
                            <input
                                type="text"
                                v-model="form.title.id"
                                required
                                placeholder="cth: Panduan Lengkap Menyaksikan Tari Kecak Uluwatu"
                                class="turget-form-input"
                            />
                            <div v-if="form.errors['title.id']" class="turget-form-error">{{ form.errors['title.id'] }}</div>
                        </div>

                        <div>
                            <label class="turget-form-label">Ringkasan Singkat / Excerpt (ID)</label>
                            <textarea
                                v-model="form.excerpt.id"
                                rows="3"
                                placeholder="Tuliskan 1-2 kalimat pengantar artikel untuk kartu preview..."
                                class="turget-form-textarea"
                            />
                        </div>

                        <div>
                            <label class="turget-form-label">Isi Lengkap Artikel (ID, Mendukung Markdown) *</label>
                            <textarea
                                v-model="form.body.id"
                                required
                                rows="14"
                                placeholder="Tuliskan narasi lengkap, tips praktis, panduan waktu terbaik, dan etika..."
                                class="turget-form-textarea"
                                style="font-family: monospace; font-size: 0.9rem; line-height: 1.6;"
                            />
                            <div v-if="form.errors['body.id']" class="turget-form-error">{{ form.errors['body.id'] }}</div>
                        </div>
                    </div>

                    <!-- EN Fields -->
                    <div v-show="activeTab === 'en'" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div>
                            <label class="turget-form-label">Article Title (EN)</label>
                            <input
                                type="text"
                                v-model="form.title.en"
                                placeholder="e.g. Complete Guide to Watching the Uluwatu Kecak Fire Dance"
                                class="turget-form-input"
                            />
                        </div>

                        <div>
                            <label class="turget-form-label">Excerpt (EN)</label>
                            <textarea
                                v-model="form.excerpt.en"
                                rows="3"
                                placeholder="Brief 1-2 sentence lead for preview cards..."
                                class="turget-form-textarea"
                            />
                        </div>

                        <div>
                            <label class="turget-form-label">Full Article Body (EN)</label>
                            <textarea
                                v-model="form.body.en"
                                rows="14"
                                placeholder="Write the full editorial body in English..."
                                class="turget-form-textarea"
                                style="font-family: monospace; font-size: 0.9rem; line-height: 1.6;"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Sidebar -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="turget-admin-card">
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                        Pengaturan Publikasi
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div>
                            <label class="turget-form-label">Kategori Artikel *</label>
                            <select v-model="form.article_category_id" required class="turget-form-select">
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">
                                    {{ tr(cat.name) }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="turget-form-label">Estimasi Waktu Baca (Menit)</label>
                            <input
                                type="number"
                                v-model="form.reading_minutes"
                                min="1"
                                placeholder="5"
                                class="turget-form-input"
                            />
                        </div>

                        <div>
                            <label class="turget-form-label">URL Slug</label>
                            <input
                                type="text"
                                v-model="form.slug"
                                placeholder="cth: panduan-tari-kecak-uluwatu"
                                class="turget-form-input"
                            />
                            <div v-if="form.errors.slug" class="turget-form-error">{{ form.errors.slug }}</div>
                        </div>
                    </div>
                </div>

                <div class="turget-admin-card">
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                        Foto Sampul Artikel
                    </h3>

                    <div>
                        <input
                            type="text"
                            v-model="form.cover_image"
                            placeholder="/images/destinations/uluwatu.jpg"
                            class="turget-form-input"
                            style="margin-bottom: 0.75rem;"
                        />
                        <div v-if="form.cover_image" style="border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--color-border-subtle); height: 140px;">
                            <img :src="form.cover_image" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;" />
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
