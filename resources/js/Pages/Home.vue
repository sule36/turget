<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    country: Object,
    featuredDestinations: Array,
    provinces: Array,
    popularCities: Array,
    categories: Array,
    articles: Array,
});

const { t, tr, locale } = useI18n();

const searchQuery = ref('');
const searchResults = ref([]);
const isSearching = ref(false);
const showDropdown = ref(false);
let searchDebounceTimer = null;

function onSearchInput() {
    clearTimeout(searchDebounceTimer);
    if (searchQuery.value.trim().length < 2) {
        searchResults.value = [];
        showDropdown.value = false;
        return;
    }

    searchDebounceTimer = setTimeout(async () => {
        isSearching.value = true;
        try {
            const res = await fetch(`/api/search/autocomplete?q=${encodeURIComponent(searchQuery.value)}`);
            const data = await res.json();
            searchResults.value = data.results || [];
            showDropdown.value = searchResults.value.length > 0;
        } catch (e) {
            searchResults.value = [];
        } finally {
            isSearching.value = false;
        }
    }, 250);
}

function submitSearch() {
    if (searchQuery.value.trim()) {
        router.visit(`/destinations?q=${encodeURIComponent(searchQuery.value.trim())}`);
    }
}
</script>

<template>
    <PublicLayout>
        <Head>
            <title>{{ t('hero.title', 'Kenali Destinasinya, Temukan Guide Lokal Terpercaya') }}</title>
            <meta name="description" :content="t('hero.subtitle', 'Platform perjalanan berbasis pengetahuan destinasi mendalam. Rencanakan perjalanan fleksibel, pesan layanan lokal terverifikasi, dan nikmati pengalaman autentik tanpa rasa cemas.')">
        </Head>

        <!-- Hero Section -->
        <section class="turget-hero">
            <div class="turget-hero-bg-accent"></div>
            <div class="turget-container">
                <div class="turget-hero-content">
                    <div class="turget-badge">
                        <span>🇮🇩</span>
                        <span>{{ t('hero.badge', 'Eksplorasi Indonesia dengan Aman & Percaya Diri') }}</span>
                    </div>

                    <h1 class="turget-hero-title">
                        {{ t('hero.title', 'Kenali Destinasinya, Temukan Guide Lokal Terpercaya') }}
                    </h1>

                    <p class="turget-hero-subtitle">
                        {{ t('hero.subtitle', 'Platform perjalanan berbasis pengetahuan destinasi mendalam. Rencanakan perjalanan fleksibel, pesan layanan lokal terverifikasi, dan nikmati pengalaman autentik tanpa rasa cemas.') }}
                    </p>

                    <!-- Interactive Search Box -->
                    <div class="turget-search-box">
                        <div class="turget-search-icon">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                            </svg>
                        </div>

                        <input
                            type="text"
                            v-model="searchQuery"
                            @input="onSearchInput"
                            @keydown.enter="submitSearch"
                            :placeholder="t('nav.search_placeholder', 'Cari destinasi, kota, provinsi, atau aktivitas...')"
                            class="turget-search-input"
                            autocomplete="off"
                        />

                        <button
                            type="button"
                            class="turget-btn turget-btn-primary"
                            @click="submitSearch"
                        >
                            {{ t('hero.search_btn', 'Cari Pengalaman') }}
                        </button>

                        <!-- Autocomplete Dropdown -->
                        <div v-if="showDropdown" class="turget-search-dropdown">
                            <div
                                v-for="(item, idx) in searchResults"
                                :key="idx"
                                class="turget-search-item"
                                @click="router.visit(item.url)"
                            >
                                <img
                                    v-if="item.image"
                                    :src="item.image"
                                    :alt="item.title"
                                    class="turget-search-item-thumb"
                                />
                                <div v-else class="turget-search-item-thumb" style="background: var(--color-blue-50); display:flex; align-items:center; justify-content:center; color: var(--color-blue-600); font-weight:700;">
                                    📍
                                </div>
                                <div class="turget-search-item-info">
                                    <div class="turget-search-item-title">{{ item.title }}</div>
                                    <div class="turget-search-item-sub">{{ item.subtitle }}</div>
                                </div>
                                <span class="turget-search-tag-badge">{{ item.badge }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Tag Pills -->
                    <div class="turget-quick-tags">
                        <span>{{ t('hero.popular_searches', 'Pencarian Populer:') }}</span>
                        <Link href="/places/pura-luhur-uluwatu" class="turget-tag-pill">Pura Uluwatu</Link>
                        <Link href="/places/candi-borobudur" class="turget-tag-pill">Candi Borobudur</Link>
                        <Link href="/places/gunung-bromo-tengger" class="turget-tag-pill">Gunung Bromo</Link>
                        <Link href="/destinations/indonesia/bali" class="turget-tag-pill">Bali</Link>
                        <Link href="/destinations/indonesia/di-yogyakarta" class="turget-tag-pill">Yogyakarta</Link>
                        <Link href="/places/pulau-padar-komodo" class="turget-tag-pill">Labuan Bajo</Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Category Row -->
        <section style="padding: 0.85rem 0; border-bottom: 1px solid var(--color-border-subtle); background: white;">
            <div class="turget-container">
                <div style="display: flex; align-items: center; justify-content: center; gap: 0.5rem; flex-wrap: wrap;">
                    <Link
                        v-for="cat in categories"
                        :key="cat.id"
                        :href="`/destinations?category=${cat.slug}`"
                        class="turget-tag-pill"
                        style="padding: 0.3rem 0.85rem; font-size: 0.82rem; background: var(--color-surface-subtle);"
                    >
                        <span>{{ tr(cat.name) }}</span>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Signature Destinations Section -->
        <section class="turget-section">
            <div class="turget-container">
                <div class="turget-section-header">
                    <div>
                        <span class="turget-badge">{{ t('destinations.title', 'Jelajahi Destinasi Unggulan') }}</span>
                        <h2 class="turget-section-title">
                            {{ t('destinations.title', 'Destinasi Unggulan Indonesia') }}
                        </h2>
                        <p class="turget-section-desc">
                            {{ t('destinations.subtitle', 'Dari pantai tropis Bali hingga keajaiban warisan budaya dan gunung berapi megah.') }}
                        </p>
                    </div>
                    <Link href="/destinations" class="turget-btn turget-btn-secondary">
                        Lihat Semua Destinasi →
                    </Link>
                </div>

                <div class="turget-grid-3">
                    <article
                        v-for="dest in featuredDestinations"
                        :key="dest.id"
                        class="turget-card"
                    >
                        <Link :href="dest.publicPath ? dest.publicPath : `/places/${dest.slug}`" class="turget-card-media">
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
                            <span class="turget-card-score">
                                ★ 4.9
                            </span>
                        </Link>

                        <div class="turget-card-body">
                            <div class="turget-card-location">
                                <span>📍</span>
                                <span>{{ dest.region?.tr ? dest.region.tr('name') : tr(dest.region?.name) }}</span>
                            </div>

                            <h3 class="turget-card-title">
                                <Link :href="`/places/${dest.slug}`">
                                    {{ tr(dest.name) }}
                                </Link>
                            </h3>

                            <p class="turget-card-summary">
                                {{ tr(dest.summary) }}
                            </p>

                            <div class="turget-card-footer">
                                <span style="font-weight: 600; color: var(--color-text-muted); font-size: 0.82rem;">
                                    ⏱ {{ dest.visit_duration_minutes || 120 }} menit
                                </span>
                                <Link :href="`/places/${dest.slug}`" class="turget-card-cta">
                                    {{ t('destinations.view_detail', 'Lihat Detail') }} →
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- Explore Provinces Section -->
        <section class="turget-section" style="background: var(--color-sand-100);">
            <div class="turget-container">
                <div class="turget-section-header">
                    <div>
                        <span class="turget-badge">Wilayah & Kawasan</span>
                        <h2 class="turget-section-title">
                            {{ t('destinations.explore_country', 'Jelajahi Wilayah di Indonesia') }}
                        </h2>
                        <p class="turget-section-desc">
                            Mulai eksplorasi berdasarkan provinsi, pulau, dan daerah istimewa dengan karakteristik budaya uniknya.
                        </p>
                    </div>
                    <Link href="/destinations/indonesia" class="turget-btn turget-btn-secondary">
                        Panduan Lengkap Indonesia →
                    </Link>
                </div>

                <div class="turget-grid-3">
                    <Link
                        v-for="prov in provinces"
                        :key="prov.id"
                        :href="`/destinations/indonesia/${prov.slug}`"
                        class="turget-region-card"
                    >
                        <img
                            :src="prov.cover_image || '/images/destinations/bali_hero.jpg'"
                            :alt="tr(prov.name)"
                            class="turget-region-card-img"
                            loading="lazy"
                        />
                        <div class="turget-region-card-overlay"></div>
                        <div class="turget-region-card-content">
                            <div class="turget-region-card-type">{{ prov.typeLabel ? prov.typeLabel(locale) : 'Provinsi' }}</div>
                            <h3 class="turget-region-card-title">{{ tr(prov.name) }}</h3>
                            <div class="turget-region-card-count">{{ prov.destinations_count || 3 }}+ Destinasi Unggulan</div>
                        </div>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Popular Cities Spotlight -->
        <section class="turget-section">
            <div class="turget-container">
                <div class="turget-section-header">
                    <div>
                        <span class="turget-badge">Kota & Kawasan</span>
                        <h2 class="turget-section-title">
                            {{ t('destinations.popular_cities', 'Kota & Kawasan Terpopuler') }}
                        </h2>
                        <p class="turget-section-desc">
                            Destinasi perkotaan, sentra kebudayaan, dan pusat aktivitas pariwisata lokal yang paling dicari.
                        </p>
                    </div>
                </div>

                <div class="turget-grid-3">
                    <Link
                        v-for="city in popularCities"
                        :key="city.id"
                        :href="city.parent ? `/destinations/indonesia/${city.parent.slug}/${city.slug}` : `/destinations/indonesia/${city.slug}`"
                        class="turget-card"
                        style="padding: 0.9rem 1rem;"
                    >
                        <div style="display:flex; align-items:center; gap: 0.85rem;">
                            <img
                                :src="city.cover_image || '/images/destinations/bali_hero.jpg'"
                                :alt="tr(city.name)"
                                style="width: 3.25rem; height: 3.25rem; border-radius: var(--radius-md); object-fit: cover;"
                            />
                            <div>
                                <h3 style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.15rem; letter-spacing: -0.02em;">
                                    {{ tr(city.name) }}
                                </h3>
                                <div style="font-size: 0.78rem; color: var(--color-text-muted);">
                                    {{ city.parent ? tr(city.parent.name) : 'Indonesia' }}
                                </div>
                            </div>
                        </div>
                        <p style="font-size: 0.82rem; color: var(--color-text-muted); margin-top: 0.65rem; line-height: 1.45;">
                            {{ tr(city.tagline) || tr(city.summary) }}
                        </p>
                    </Link>
                </div>
            </div>
        </section>

        <!-- Trust & Safety Pillars (PRD §15) -->
        <section class="turget-container">
            <div class="turget-trust-section">
                <div class="turget-badge turget-trust-badge">
                    🛡️ {{ t('trust.badge', 'Mengapa Turget.id?') }}
                </div>
                <h2 class="turget-trust-title">
                    {{ t('trust.title', 'Platform Perjalanan yang Berorientasi pada Rasa Aman') }}
                </h2>

                <div class="turget-trust-grid">
                    <div>
                        <div class="turget-trust-item-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                            </svg>
                        </div>
                        <h3 class="turget-trust-item-title">{{ t('trust.point_1_title', 'Konten Terverifikasi') }}</h3>
                        <p class="turget-trust-item-desc">{{ t('trust.point_1_desc', 'Setiap informasi sejarah, jam operasional, dan fakta budaya dikurasi melalui editorial review ketat.') }}</p>
                    </div>

                    <div>
                        <div class="turget-trust-item-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                                <circle cx="9" cy="7" r="4"></circle>
                                <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                                <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                            </svg>
                        </div>
                        <h3 class="turget-trust-item-title">{{ t('trust.point_2_title', 'Guide Lokal Berlisensi') }}</h3>
                        <p class="turget-trust-item-desc">{{ t('trust.point_2_desc', 'Pemandu wisata melewati verifikasi identitas, lisensi pemandu, dan rekam jejak profesional.') }}</p>
                    </div>

                    <div>
                        <div class="turget-trust-item-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"></circle>
                                <polygon points="16.24 7.76 14.12 14.12 7.76 16.24 9.88 9.88 16.24 7.76"></polygon>
                            </svg>
                        </div>
                        <h3 class="turget-trust-item-title">{{ t('trust.point_3_title', 'Rekomendasi Berbasis Jarak') }}</h3>
                        <p class="turget-trust-item-desc">{{ t('trust.point_3_desc', 'Perhitungan koordinat presisi memastikan penginapan dan rental benar-benar dekat destinasi.') }}</p>
                    </div>

                    <div>
                        <div class="turget-trust-item-icon">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect x="2" y="4" width="20" height="16" rx="2"></rect>
                                <line x1="2" y1="10" x2="22" y2="10"></line>
                            </svg>
                        </div>
                        <h3 class="turget-trust-item-title">{{ t('trust.point_4_title', 'Transaksi Aman & Transparan') }}</h3>
                        <p class="turget-trust-item-desc">{{ t('trust.point_4_desc', 'Perlindungan pembayaran menyeluruh dengan rincian biaya transparan dan audit ledger.') }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Travel Inspiration & Articles Section -->
        <section class="turget-section" style="padding-top: 1rem;">
            <div class="turget-container">
                <div class="turget-section-header">
                    <div>
                        <span class="turget-badge">Inspirasi & Cerita</span>
                        <h2 class="turget-section-title">
                            {{ t('articles.title', 'Inspirasi & Panduan Perjalanan') }}
                        </h2>
                        <p class="turget-section-desc">
                            {{ t('articles.subtitle', 'Panduan terkurasi, cerita sejarah kawasan, dan tips praktis dari tim editorial Turget.id.') }}
                        </p>
                    </div>
                    <Link href="/articles" class="turget-btn turget-btn-secondary">
                        Semua Artikel →
                    </Link>
                </div>

                <div class="turget-grid-3">
                    <article
                        v-for="art in articles"
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
                                <span>📖 {{ art.reading_minutes || 5 }} {{ t('articles.read_time', 'menit membaca') }}</span>
                                <span>•</span>
                                <span>{{ art.category ? tr(art.category.name) : 'Panduan' }}</span>
                            </div>
                            <h3 class="turget-article-title">
                                <Link :href="`/articles/${art.slug}`">
                                    {{ tr(art.title) }}
                                </Link>
                            </h3>
                            <p class="turget-article-excerpt">
                                {{ tr(art.excerpt) }}
                            </p>
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

        <!-- Custom Trip Call To Action -->
        <section class="turget-container" style="padding-bottom: 3rem;">
            <div style="background: linear-gradient(135deg, var(--color-blue-600), var(--color-navy-900)); color: white; border-radius: 16px; padding: 2.25rem 2rem; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem; flex-wrap: wrap;">
                <div style="max-width: 600px;">
                    <span class="turget-badge" style="background: rgba(255,255,255,0.15); color: white; border: none; margin-bottom: 0.5rem;">Custom Trip Request</span>
                    <h2 style="font-family: var(--font-heading); font-size: 1.65rem; font-weight: 700; margin: 0.4rem 0 0.5rem; line-height: 1.25; letter-spacing: -0.025em;">
                        Ingin Rencana Perjalanan yang Dibuat Khusus Sesuai Keinginan Anda?
                    </h2>
                    <p style="color: rgba(255,255,255,0.88); font-size: 0.92rem; line-height: 1.55;">
                        Tentukan destinasi, durasi, dan preferensi Anda. Dapatkan proposal penawaran langsung dari tour guide lokal berlisensi kami.
                    </p>
                </div>
                <div>
                    <Link href="/destinations" class="turget-btn turget-btn-lg" style="background: white; color: var(--color-navy-950); font-weight: 700; box-shadow: var(--shadow-md);">
                        Mulai Buat Perjalanan →
                    </Link>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
