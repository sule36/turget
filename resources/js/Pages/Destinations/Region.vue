<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    country: Object,
    region: Object,
    ancestors: Array,
    destinations: Array,
});

const { t, tr, locale } = useI18n();
</script>

<template>
    <PublicLayout>
        <Head>
            <title>{{ tr(region.name) }} — Panduan Wisata Wilayah</title>
            <meta name="description" :content="tr(region.summary)">
        </Head>

        <!-- Region Hero Banner -->
        <section style="position: relative; background: var(--color-navy-950); color: white; padding: 2.5rem 0 2rem; overflow: hidden;">
            <div style="position: absolute; inset: 0; opacity: 0.3;">
                <img
                    :src="region.cover_image || '/images/destinations/bali_hero.jpg'"
                    :alt="tr(region.name)"
                    style="width: 100%; height: 100%; object-fit: cover;"
                />
            </div>
            <div class="turget-container" style="position: relative; z-index: 2;">
                <!-- Hierarchical Breadcrumb -->
                <nav style="display: flex; gap: 0.4rem; font-size: 0.8rem; color: var(--color-text-subtle); margin-bottom: 1rem; flex-wrap: wrap;">
                    <Link href="/" style="color: rgba(255,255,255,0.7);">Beranda</Link>
                    <span>/</span>
                    <Link href="/destinations" style="color: rgba(255,255,255,0.7);">Destinasi</Link>
                    <span>/</span>
                    <Link :href="`/destinations/${country.slug}`" style="color: rgba(255,255,255,0.7);">{{ tr(country.name) }}</Link>
                    <template v-for="anc in ancestors" :key="anc.id">
                        <span>/</span>
                        <Link :href="anc.publicPath ? anc.publicPath() : `/destinations/${country.slug}/${anc.slug}`" style="color: rgba(255,255,255,0.7);">
                            {{ tr(anc.name) }}
                        </Link>
                    </template>
                    <span>/</span>
                    <span style="color: white; font-weight: 700;">{{ tr(region.name) }}</span>
                </nav>

                <div style="max-width: 800px;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.65rem;">
                        <span class="turget-badge" style="margin: 0; background: rgba(255,255,255,0.12); color: var(--color-sand-200); border-color: rgba(255,255,255,0.2); padding: 0.2rem 0.55rem; font-size: 0.72rem;">
                            {{ region.typeLabel ? region.typeLabel(locale) : 'Wilayah' }}
                        </span>
                        <span v-if="region.latitude" style="font-size: 0.76rem; color: rgba(255,255,255,0.7);">
                            🌐 {{ Number(region.latitude).toFixed(4) }}, {{ Number(region.longitude).toFixed(4) }}
                        </span>
                    </div>

                    <h1 style="font-family: var(--font-heading); font-size: 2.25rem; font-weight: 800; line-height: 1.15; letter-spacing: -0.035em; margin-bottom: 0.5rem;">
                        {{ tr(region.name) }}
                    </h1>

                    <p style="font-size: 0.96rem; color: rgba(255,255,255,0.9); line-height: 1.55; margin-bottom: 0.85rem;">
                        {{ tr(region.tagline) }}
                    </p>

                    <p style="font-size: 0.88rem; color: rgba(255,255,255,0.8); line-height: 1.55; max-width: 720px;">
                        {{ tr(region.summary) }}
                    </p>
                </div>
            </div>
        </section>

        <!-- Child Regencies / Cities Section (if available) -->
        <section v-if="region.children && region.children.length > 0" class="turget-section" style="background: var(--color-sand-100); padding: 3.5rem 0;">
            <div class="turget-container">
                <div class="turget-section-header" style="margin-bottom: 2rem;">
                    <div>
                        <span class="turget-badge">Kota & Kabupaten</span>
                        <h2 class="turget-section-title" style="font-size: 1.85rem;">
                            Kawasan di {{ tr(region.name) }}
                        </h2>
                    </div>
                </div>

                <div class="turget-grid-3">
                    <Link
                        v-for="child in region.children"
                        :key="child.id"
                        :href="`/destinations/${country.slug}/${region.slug}/${child.slug}`"
                        class="turget-card"
                        style="padding: 1.25rem;"
                    >
                        <div style="display: flex; gap: 1rem; align-items: center;">
                            <img
                                :src="child.cover_image || '/images/destinations/bali_hero.jpg'"
                                :alt="tr(child.name)"
                                style="width: 3.5rem; height: 3.5rem; border-radius: var(--radius-md); object-fit: cover;"
                            />
                            <div>
                                <h3 style="font-family: var(--font-serif); font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.2rem;">
                                    {{ tr(child.name) }}
                                </h3>
                                <div style="font-size: 0.8rem; color: var(--color-blue-600); font-weight: 600;">
                                    {{ child.destinations_count || 1 }}+ Destinasi Wisata
                                </div>
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Destinations in this Region -->
        <section class="turget-section">
            <div class="turget-container">
                <div class="turget-section-header">
                    <div>
                        <span class="turget-badge">Tempat Menarik</span>
                        <h2 class="turget-section-title">
                            Destinasi di {{ tr(region.name) }}
                        </h2>
                        <p class="turget-section-desc">
                            Tempat wisata, peninggalan bersejarah, dan pengalaman lokal yang siap dijelajahi.
                        </p>
                    </div>
                </div>

                <div v-if="destinations.length > 0" class="turget-grid-3">
                    <article
                        v-for="dest in destinations"
                        :key="dest.id"
                        class="turget-card"
                    >
                        <Link :href="`/places/${dest.slug}`" class="turget-card-media">
                            <img
                                :src="dest.cover_image || '/images/destinations/bali_hero.jpg'"
                                :alt="tr(dest.name)"
                                class="turget-card-img"
                                loading="lazy"
                            />
                            <div class="turget-card-overlay"></div>
                            <span class="turget-card-badge" v-if="dest.categories?.[0]">
                                {{ tr(dest.categories[0].name) }}
                            </span>
                            <span class="turget-card-score">★ 4.9</span>
                        </Link>
                        <div class="turget-card-body">
                            <div class="turget-card-location">
                                <span>📍</span>
                                <span>{{ dest.region ? tr(dest.region.name) : tr(region.name) }}</span>
                            </div>
                            <h3 class="turget-card-title">
                                <Link :href="`/places/${dest.slug}`">{{ tr(dest.name) }}</Link>
                            </h3>
                            <p class="turget-card-summary">{{ tr(dest.summary) }}</p>
                            <div class="turget-card-footer">
                                <span style="font-weight: 600; color: var(--color-text-muted); font-size: 0.82rem;">
                                    ⏱ {{ dest.visit_duration_minutes || 120 }} menit
                                </span>
                                <Link :href="`/places/${dest.slug}`" class="turget-card-cta">
                                    Lihat Detail →
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else style="background: white; border-radius: var(--radius-xl); padding: 4rem 2rem; text-align: center; border: 1px dashed var(--color-border-subtle);">
                    <div style="font-size: 2.5rem; margin-bottom: 0.75rem;">🗺️</div>
                    <h3 style="font-family: var(--font-serif); font-size: 1.4rem; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        Belum Ada Destinasi Terdaftar
                    </h3>
                    <p style="color: var(--color-text-muted); max-width: 420px; margin: 0 auto;">
                        Konten untuk wilayah ini sedang dalam tahap kurasi editorial oleh tim Turget.id.
                    </p>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
