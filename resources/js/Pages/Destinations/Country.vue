<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    country: Object,
    featuredDestinations: Array,
});

const { t, tr, locale } = useI18n();

const activeTab = ref('overview');

const tabs = [
    { id: 'overview', label: 'Tentang Negara', labelEn: 'About' },
    { id: 'history', label: 'Sejarah & Warisan', labelEn: 'History & Heritage' },
    { id: 'culture', label: 'Budaya & Etika', labelEn: 'Culture & Etiquette' },
    { id: 'cuisine', label: 'Kuliner Khas', labelEn: 'Cuisine' },
    { id: 'climate', label: 'Iklim & Musim', labelEn: 'Climate & Seasons' },
    { id: 'safety', label: 'Keselamatan & Regulasi', labelEn: 'Safety & Travel Tips' },
];
</script>

<template>
    <PublicLayout>
        <Head>
            <title>{{ tr(country.name) }} — Panduan Wisata & Destinasi</title>
            <meta name="description" :content="tr(country.summary)">
        </Head>

        <!-- Country Hero Banner -->
        <section style="position: relative; background: var(--color-navy-950); color: white; padding: 5rem 0 4rem; overflow: hidden;">
            <div style="position: absolute; inset: 0; opacity: 0.28;">
                <img
                    :src="country.cover_image || '/images/destinations/bali_hero.jpg'"
                    :alt="tr(country.name)"
                    style="width: 100%; height: 100%; object-fit: cover;"
                />
            </div>
            <div class="turget-container" style="position: relative; z-index: 2;">
                <!-- Breadcrumb -->
                <nav style="display: flex; gap: 0.5rem; font-size: 0.85rem; color: var(--color-text-subtle); margin-bottom: 1.5rem;">
                    <Link href="/" style="color: rgba(255,255,255,0.7);">Beranda</Link>
                    <span>/</span>
                    <Link href="/destinations" style="color: rgba(255,255,255,0.7);">Destinasi</Link>
                    <span>/</span>
                    <span style="color: white; font-weight: 700;">{{ tr(country.name) }}</span>
                </nav>

                <div style="max-width: 820px;">
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem;">
                        <span style="font-size: 2rem;">🇮🇩</span>
                        <span class="turget-badge" style="margin: 0; background: rgba(255,255,255,0.12); color: var(--color-sand-200); border-color: rgba(255,255,255,0.2);">
                            Negara Kepulauan • Asia Tenggara
                        </span>
                    </div>

                    <h1 style="font-family: var(--font-serif); font-size: 3.5rem; font-weight: 700; line-height: 1.15; margin-bottom: 1rem;">
                        {{ tr(country.name) }}
                    </h1>

                    <p style="font-size: 1.22rem; color: rgba(255,255,255,0.9); line-height: 1.6; margin-bottom: 2rem;">
                        {{ tr(country.tagline) }}
                    </p>

                    <!-- Key Fast Facts Row -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 1rem; background: rgba(255,255,255,0.08); backdrop-filter: blur(12px); border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; border: 1px solid rgba(255,255,255,0.15);">
                        <div>
                            <div style="font-size: 0.75rem; color: var(--color-sand-200); text-transform: uppercase; font-weight: 700;">Mata Uang</div>
                            <div style="font-size: 0.95rem; font-weight: 700;">IDR (Rupiah)</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--color-sand-200); text-transform: uppercase; font-weight: 700;">Bahasa</div>
                            <div style="font-size: 0.95rem; font-weight: 700;">Bahasa Indonesia</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--color-sand-200); text-transform: uppercase; font-weight: 700;">Zona Waktu</div>
                            <div style="font-size: 0.95rem; font-weight: 700;">WIB, WITA, WIT</div>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: var(--color-sand-200); text-transform: uppercase; font-weight: 700;">Visa</div>
                            <div style="font-size: 0.95rem; font-weight: 700;">VoA (90+ Negara)</div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Tabbed In-Depth Guide Section -->
        <section class="turget-section" style="padding-top: 3rem;">
            <div class="turget-container">
                <!-- Navigation Tabs -->
                <div style="display: flex; gap: 0.5rem; overflow-x: auto; padding-bottom: 0.5rem; border-bottom: 2px solid var(--color-border-subtle); margin-bottom: 2.5rem;">
                    <button
                        v-for="tab in tabs"
                        :key="tab.id"
                        type="button"
                        class="turget-nav-link"
                        :class="{ 'is-active': activeTab === tab.id }"
                        style="padding: 0.75rem 1.25rem; font-size: 1rem; cursor: pointer; white-space: nowrap;"
                        @click="activeTab = tab.id"
                    >
                        {{ locale === 'id' ? tab.label : tab.labelEn }}
                    </button>
                </div>

                <!-- Tab Content -->
                <div style="background: white; border-radius: var(--radius-xl); padding: 3rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-sm);">
                    <div v-if="activeTab === 'overview'">
                        <h2 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                            Tentang Indonesia
                        </h2>
                        <p style="font-size: 1.15rem; line-height: 1.8; color: var(--color-text-main); margin-bottom: 2rem;">
                            {{ tr(country.summary) }}
                        </p>
                        <div style="background: var(--color-sand-50); border-radius: var(--radius-lg); padding: 2rem; border-left: 4px solid var(--color-blue-600);">
                            <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                                Catatan Editorial & Sumber Regulasi Resmi
                            </h3>
                            <p style="font-size: 0.95rem; color: var(--color-text-muted); line-height: 1.6;">
                                Konten panduan negara Turget.id diperiksa secara berkala berdasarkan rilis resmi Kementerian Pariwisata dan Ekonomi Kreatif serta Ditjen Imigrasi Republik Indonesia.
                            </p>
                        </div>
                    </div>

                    <div v-else-if="activeTab === 'history'">
                        <h2 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                            Sejarah & Peradaban Kepulauan Rempah
                        </h2>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-main);">
                            {{ tr(country.sections?.history) }}
                        </p>
                    </div>

                    <div v-else-if="activeTab === 'culture'">
                        <h2 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                            Nilai Budaya, Tradisi & Etika Berkunjung
                        </h2>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-main); margin-bottom: 1.5rem;">
                            {{ tr(country.sections?.culture) }}
                        </p>
                    </div>

                    <div v-else-if="activeTab === 'cuisine'">
                        <h2 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                            Kekayaan Kuliner & Rempah Nusantara
                        </h2>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-main);">
                            {{ tr(country.sections?.cuisine) }}
                        </p>
                    </div>

                    <div v-else-if="activeTab === 'climate'">
                        <h2 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                            Iklim, Musim & Rekomendasi Waktu Kunjungan
                        </h2>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-main);">
                            {{ tr(country.sections?.climate) }}
                        </p>
                    </div>

                    <div v-else-if="activeTab === 'safety'">
                        <h2 style="font-family: var(--font-serif); font-size: 2rem; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                            Pedoman Keselamatan, Kesehatan & Kontak Darurat
                        </h2>
                        <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-main); margin-bottom: 1.5rem;">
                            {{ tr(country.sections?.safety) }}
                        </p>
                        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-top: 1.5rem;">
                            <div style="background: var(--color-surface-subtle); padding: 1.25rem; border-radius: var(--radius-md);">
                                <strong>Nomor Darurat Nasional:</strong> 112
                            </div>
                            <div style="background: var(--color-surface-subtle); padding: 1.25rem; border-radius: var(--radius-md);">
                                <strong>Kepolisian:</strong> 110
                            </div>
                            <div style="background: var(--color-surface-subtle); padding: 1.25rem; border-radius: var(--radius-md);">
                                <strong>Ambulans / Medis:</strong> 118
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Provinces Directory Cards -->
        <section class="turget-section" style="background: var(--color-sand-100);">
            <div class="turget-container">
                <div class="turget-section-header">
                    <div>
                        <span class="turget-badge">Wilayah Administratif</span>
                        <h2 class="turget-section-title">
                            Provinsi & Daerah Istimewa
                        </h2>
                        <p class="turget-section-desc">
                            Pilih provinsi untuk melihat detail kota, kawasan wisata, dan destinasi yang dapat dikunjungi.
                        </p>
                    </div>
                </div>

                <div class="turget-grid-3">
                    <Link
                        v-for="reg in country.top_regions"
                        :key="reg.id"
                        :href="`/destinations/indonesia/${reg.slug}`"
                        class="turget-region-card"
                    >
                        <img
                            :src="reg.cover_image || '/images/destinations/bali_hero.jpg'"
                            :alt="tr(reg.name)"
                            class="turget-region-card-img"
                            loading="lazy"
                        />
                        <div class="turget-region-card-overlay"></div>
                        <div class="turget-region-card-content">
                            <div class="turget-region-card-type">{{ reg.typeLabel ? reg.typeLabel(locale) : 'Wilayah' }}</div>
                            <h3 class="turget-region-card-title">{{ tr(reg.name) }}</h3>
                            <div class="turget-region-card-count">{{ reg.destinations_count || 2 }} Destinasi Terdaftar</div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Featured Destinations in Country -->
        <section class="turget-section">
            <div class="turget-container">
                <div class="turget-section-header">
                    <div>
                        <span class="turget-badge">Rekomendasi Utama</span>
                        <h2 class="turget-section-title">
                            Destinasi Populer di Indonesia
                        </h2>
                    </div>
                    <Link href="/destinations" class="turget-btn turget-btn-secondary">
                        Lihat Seluruhnya →
                    </Link>
                </div>

                <div class="turget-grid-3">
                    <article
                        v-for="dest in featuredDestinations"
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
                        </Link>
                        <div class="turget-card-body">
                            <div class="turget-card-location">
                                <span>📍</span>
                                <span>{{ dest.region?.tr ? dest.region.tr('name') : tr(dest.region?.name) }}</span>
                            </div>
                            <h3 class="turget-card-title">
                                <Link :href="`/places/${dest.slug}`">{{ tr(dest.name) }}</Link>
                            </h3>
                            <p class="turget-card-summary">{{ tr(dest.summary) }}</p>
                            <div class="turget-card-footer">
                                <Link :href="`/places/${dest.slug}`" class="turget-card-cta">
                                    Lihat Detail →
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
