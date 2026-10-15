<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    articles: Object,
    categories: Array,
    currentCategory: String,
});

const { t, tr } = useI18n();
</script>

<template>
    <PublicLayout>
        <Head>
            <title>{{ t('articles.title', 'Inspirasi & Panduan Perjalanan') }}</title>
            <meta name="description" :content="t('articles.subtitle', 'Panduan terkurasi, cerita sejarah kawasan, dan tips praktis dari tim editorial Turget.id.')">
        </Head>

        <!-- Header -->
        <section style="background: linear-gradient(180deg, var(--color-sand-100) 0%, var(--color-sand-50) 100%); padding: 3.5rem 0 2.5rem; border-bottom: 1px solid var(--color-border-subtle);">
            <div class="turget-container">
                <span class="turget-badge">Turget.id Editorial</span>
                <h1 style="font-family: var(--font-serif); font-size: 2.75rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                    {{ t('articles.title', 'Inspirasi & Panduan Perjalanan') }}
                </h1>
                <p style="color: var(--color-text-muted); font-size: 1.15rem; max-width: 680px; margin-bottom: 1.5rem;">
                    {{ t('articles.subtitle', 'Panduan terkurasi, cerita sejarah kawasan, dan tips praktis dari tim editorial Turget.id.') }}
                </p>

                <!-- Categories -->
                <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                    <Link
                        href="/articles"
                        class="turget-tag-pill"
                        :style="!currentCategory ? 'background: var(--color-blue-600); color: white; border-color: var(--color-blue-600);' : ''"
                    >
                        Semua Kategori
                    </Link>
                    <Link
                        v-for="cat in categories"
                        :key="cat.id"
                        :href="`/articles?category=${cat.slug}`"
                        class="turget-tag-pill"
                        :style="currentCategory === cat.slug ? 'background: var(--color-blue-600); color: white; border-color: var(--color-blue-600);' : ''"
                    >
                        {{ tr(cat.name) }}
                    </Link>
                </div>
            </div>
        </section>

        <!-- Article Grid -->
        <section class="turget-section">
            <div class="turget-container">
                <div class="turget-grid-3">
                    <article
                        v-for="art in articles.data"
                        :key="art.id"
                        class="turget-article-card"
                    >
                        <Link :href="`/articles/${art.slug}`">
                            <img
                                :src="art.cover_image || '/images/destinations/bali_hero.jpg'"
                                :alt="tr(art.title)"
                                class="turget-article-thumb"
                                loading="lazy"
                            />
                        </Link>
                        <div class="turget-article-body">
                            <div class="turget-article-meta">
                                <span>📖 {{ art.reading_minutes || 5 }} {{ t('articles.read_time', 'menit') }}</span>
                                <span>•</span>
                                <span>{{ art.category ? tr(art.category.name) : 'Panduan' }}</span>
                            </div>
                            <h3 class="turget-article-title">
                                <Link :href="`/articles/${art.slug}`">{{ tr(art.title) }}</Link>
                            </h3>
                            <p class="turget-article-excerpt">{{ tr(art.excerpt) }}</p>
                            <div style="margin-top: auto;">
                                <Link :href="`/articles/${art.slug}`" class="turget-card-cta">
                                    Baca Selengkapnya →
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
