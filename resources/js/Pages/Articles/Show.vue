<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    article: Object,
    related: Array,
});

const { t, tr } = useI18n();

// Format line breaks / paragraphs in body text cleanly
function formatBody(text) {
    if (!text) return '';
    return text.split('\n\n').map(p => {
        if (p.startsWith('### ')) {
            return `<h3 style="font-family: var(--font-serif); font-size: 1.5rem; color: var(--color-navy-950); margin: 2rem 0 0.75rem;">${p.replace('### ', '')}</h3>`;
        }
        if (p.startsWith('- ')) {
            const items = p.split('\n- ').map(item => `<li>${item.replace('- ', '')}</li>`).join('');
            return `<ul style="margin: 1rem 0; padding-left: 1.5rem; line-height: 1.8;">${items}</ul>`;
        }
        return `<p style="font-size: 1.12rem; line-height: 1.85; color: #1E293B; margin-bottom: 1.25rem;">${p}</p>`;
    }).join('');
}
</script>

<template>
    <PublicLayout>
        <Head>
            <title>{{ tr(article.title) }} — Editorial Turget.id</title>
            <meta name="description" :content="tr(article.excerpt)">
        </Head>

        <!-- Article Header -->
        <article style="padding: 4rem 0 6rem;">
            <div class="turget-container" style="max-width: 820px;">
                <nav style="display: flex; gap: 0.5rem; font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 1.5rem;">
                    <Link href="/">Beranda</Link>
                    <span>/</span>
                    <Link href="/articles">Artikel</Link>
                    <span>/</span>
                    <span style="color: var(--color-navy-950); font-weight: 700;">{{ tr(article.category?.name) || 'Panduan' }}</span>
                </nav>

                <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                    <span class="turget-badge" style="margin: 0;">
                        {{ tr(article.category?.name) || 'Editorial' }}
                    </span>
                    <span style="font-size: 0.85rem; color: var(--color-text-muted);">
                        📖 {{ article.reading_minutes || 5 }} {{ t('articles.read_time', 'menit membaca') }}
                    </span>
                </div>

                <h1 class="turget-article-title">
                    {{ tr(article.title) }}
                </h1>

                <p style="font-size: 1.1rem; line-height: 1.6; color: var(--color-text-muted); margin-bottom: 2rem;">
                    {{ tr(article.excerpt) }}
                </p>

                <!-- Author info -->
                <div style="display: flex; align-items: center; gap: 1rem; padding: 1.25rem 0; border-top: 1px solid var(--color-border-subtle); border-bottom: 1px solid var(--color-border-subtle); margin-bottom: 2.5rem;">
                    <div style="width: 3rem; height: 3rem; border-radius: 50%; background: var(--color-navy-900); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                        ✍️
                    </div>
                    <div>
                        <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.95rem;">
                            {{ article.author?.name || 'Tim Editorial Turget.id' }}
                        </div>
                        <div style="font-size: 0.82rem; color: var(--color-text-muted);">
                            Ditinjau & Diverifikasi Sesuai Standar Editorial
                        </div>
                    </div>
                </div>

                <!-- Cover Image -->
                <div style="border-radius: var(--radius-xl); overflow: hidden; margin-bottom: 3rem; box-shadow: var(--shadow-md);">
                    <img
                        :src="article.cover_image || '/images/destinations/bali_hero.jpg'"
                        :alt="tr(article.title)"
                        style="width: 100%; aspect-ratio: 16 / 9; object-fit: cover;"
                    />
                    <div v-if="article.cover_credit" style="font-size: 0.78rem; color: var(--color-text-muted); padding: 0.5rem 0.75rem; text-align: right; background: white;">
                        Sumber Foto: {{ article.cover_credit }}
                    </div>
                </div>

                <!-- Body Content -->
                <div class="turget-article-content" v-html="formatBody(tr(article.body))"></div>

                <!-- Sources & References (PRD §6.4) -->
                <div v-if="article.sources && article.sources.length > 0" style="margin-top: 3.5rem; background: var(--color-sand-50); border-radius: var(--radius-lg); padding: 2rem; border: 1px solid var(--color-border-subtle);">
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                        {{ t('articles.sources', 'Sumber Referensi & Rujukan') }}
                    </h3>
                    <ul style="padding-left: 1.25rem; font-size: 0.92rem; color: var(--color-text-muted);">
                        <li v-for="(src, idx) in article.sources" :key="idx" style="margin-bottom: 0.35rem;">
                            <a :href="src.url" target="_blank" rel="noopener" style="color: var(--color-blue-600); text-decoration: underline;">
                                {{ src.title }} ↗
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </article>

        <!-- Related Articles -->
        <section v-if="related && related.length > 0" class="turget-section" style="background: var(--color-sand-100);">
            <div class="turget-container">
                <div class="turget-section-header">
                    <div>
                        <span class="turget-badge">Inspirasi Lanjutan</span>
                        <h2 class="turget-section-title">Artikel Terkait Lainnya</h2>
                    </div>
                </div>

                <div class="turget-grid-3">
                    <article
                        v-for="rel in related"
                        :key="rel.id"
                        class="turget-article-card"
                    >
                        <Link :href="`/articles/${rel.slug}`">
                            <img
                                :src="rel.cover_image || '/images/destinations/bali_hero.jpg'"
                                :alt="tr(rel.title)"
                                class="turget-article-thumb"
                                loading="lazy"
                            />
                        </Link>
                        <div class="turget-article-body">
                            <h3 class="turget-article-title" style="font-size: 1.15rem;">
                                <Link :href="`/articles/${rel.slug}`">{{ tr(rel.title) }}</Link>
                            </h3>
                            <div style="margin-top: auto;">
                                <Link :href="`/articles/${rel.slug}`" class="turget-card-cta">
                                    Baca Artikel →
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
