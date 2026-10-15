<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    destinations: Object,
    categories: Array,
    regions: Array,
    filters: Object,
});

const { t, tr, locale } = useI18n();

const searchInput = ref(props.filters.q || '');
const selectedCategory = ref(props.filters.category || '');
const selectedRegion = ref(props.filters.region || '');
const selectedSort = ref(props.filters.sort || 'popular');

function applyFilter() {
    router.get('/destinations', {
        q: searchInput.value || undefined,
        category: selectedCategory.value || undefined,
        region: selectedRegion.value || undefined,
        sort: selectedSort.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
}

function selectCategory(slug) {
    selectedCategory.value = selectedCategory.value === slug ? '' : slug;
    applyFilter();
}

function clearFilters() {
    searchInput.value = '';
    selectedCategory.value = '';
    selectedRegion.value = '';
    selectedSort.value = 'popular';
    applyFilter();
}
</script>

<template>
    <PublicLayout>
        <Head>
            <title>{{ t('destinations.title', 'Jelajahi Destinasi Unggulan') }}</title>
            <meta name="description" :content="t('destinations.subtitle', 'Temukan keindahan destinasi terbaik di seluruh Indonesia dengan panduan mendalam dan tour guide lokal.')">
        </Head>

        <!-- Header Hero -->
        <section style="background: linear-gradient(180deg, var(--color-sand-100) 0%, var(--color-sand-50) 100%); padding: 3.5rem 0 2.5rem; border-bottom: 1px solid var(--color-border-subtle);">
            <div class="turget-container">
                <span class="turget-badge">Direktori Destinasi</span>
                <h1 style="font-family: var(--font-serif); font-size: 2.75rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                    {{ t('destinations.title', 'Jelajahi Destinasi Unggulan') }}
                </h1>
                <p style="color: var(--color-text-muted); font-size: 1.15rem; max-width: 680px; margin-bottom: 2rem;">
                    {{ t('destinations.subtitle', 'Dari pantai tropis Bali hingga keajaiban warisan budaya dan gunung berapi megah.') }}
                </p>

                <!-- Search & Filters Bar -->
                <div style="background: white; padding: 1.25rem 1.5rem; border-radius: var(--radius-xl); box-shadow: var(--shadow-md); border: 1px solid var(--color-border-subtle); display: flex; flex-wrap: wrap; gap: 1rem; align-items: center;">
                    <!-- Keyword input -->
                    <div style="flex: 2; min-width: 240px; position: relative;">
                        <input
                            type="text"
                            v-model="searchInput"
                            @keydown.enter="applyFilter"
                            :placeholder="t('nav.search_placeholder', 'Cari destinasi atau alamat...')"
                            style="width: 100%; padding: 0.65rem 1rem 0.65rem 2.5rem; border: 1px solid var(--color-border-subtle); border-radius: var(--radius-md); font-size: 0.95rem;"
                        />
                        <span style="position: absolute; left: 0.85rem; top: 50%; transform: translateY(-50%); color: var(--color-text-subtle);">
                            🔍
                        </span>
                    </div>

                    <!-- Region selector -->
                    <div style="flex: 1; min-width: 180px;">
                        <select
                            v-model="selectedRegion"
                            @change="applyFilter"
                            style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--color-border-subtle); border-radius: var(--radius-md); font-size: 0.95rem; background: white;"
                        >
                            <option value="">{{ t('filters.region', 'Semua Wilayah') }}</option>
                            <option v-for="r in regions" :key="r.id" :value="r.slug">
                                {{ tr(r.name) }}
                            </option>
                        </select>
                    </div>

                    <!-- Sort selector -->
                    <div style="flex: 1; min-width: 160px;">
                        <select
                            v-model="selectedSort"
                            @change="applyFilter"
                            style="width: 100%; padding: 0.65rem 1rem; border: 1px solid var(--color-border-subtle); border-radius: var(--radius-md); font-size: 0.95rem; background: white;"
                        >
                            <option value="popular">{{ t('filters.sort_popular', 'Terpopuler') }}</option>
                            <option value="newest">{{ t('filters.sort_newest', 'Terbaru') }}</option>
                            <option value="name">{{ t('filters.sort_name', 'Nama (A-Z)') }}</option>
                        </select>
                    </div>

                    <button
                        type="button"
                        class="turget-btn turget-btn-primary"
                        @click="applyFilter"
                    >
                        Filter
                    </button>

                    <button
                        v-if="searchInput || selectedCategory || selectedRegion"
                        type="button"
                        class="turget-btn turget-btn-secondary turget-btn-sm"
                        @click="clearFilters"
                    >
                        Reset
                    </button>
                </div>

                <!-- Category Chips -->
                <div style="margin-top: 1.25rem; display: flex; align-items: center; gap: 0.65rem; flex-wrap: wrap;">
                    <span style="font-size: 0.85rem; font-weight: 700; color: var(--color-navy-950); margin-right: 0.25rem;">
                        {{ t('filters.category', 'Kategori') }}:
                    </span>
                    <button
                        type="button"
                        class="turget-tag-pill"
                        :style="selectedCategory === '' ? 'background: var(--color-blue-600); color: white; border-color: var(--color-blue-600);' : ''"
                        @click="selectCategory('')"
                    >
                        {{ t('filters.all', 'Semua') }}
                    </button>
                    <button
                        v-for="cat in categories"
                        :key="cat.id"
                        type="button"
                        class="turget-tag-pill"
                        :style="selectedCategory === cat.slug ? 'background: var(--color-blue-600); color: white; border-color: var(--color-blue-600);' : ''"
                        @click="selectCategory(cat.slug)"
                    >
                        {{ tr(cat.name) }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Destination Cards List -->
        <section class="turget-section">
            <div class="turget-container">
                <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 2rem;">
                    <div style="font-size: 0.95rem; font-weight: 600; color: var(--color-text-muted);">
                        Menampilkan <strong style="color: var(--color-navy-950);">{{ destinations.total }}</strong> destinasi terdaftar
                    </div>
                </div>

                <!-- Grid -->
                <div v-if="destinations.data.length > 0" class="turget-grid-3">
                    <article
                        v-for="dest in destinations.data"
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

                <!-- Empty State -->
                <div v-else style="background: white; border-radius: var(--radius-xl); padding: 5rem 2rem; text-align: center; border: 1px dashed var(--color-border-subtle);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🏖️</div>
                    <h3 style="font-family: var(--font-serif); font-size: 1.6rem; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        Tidak Ada Destinasi yang Sesuai
                    </h3>
                    <p style="color: var(--color-text-muted); max-width: 480px; margin: 0 auto 1.5rem;">
                        Coba ubah kata kunci pencarian atau reset filter untuk melihat semua tempat wisata yang tersedia.
                    </p>
                    <button type="button" class="turget-btn turget-btn-primary" @click="clearFilters">
                        Reset Semua Filter
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="destinations.links && destinations.links.length > 3" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 3.5rem;">
                    <Component
                        :is="link.url ? Link : 'span'"
                        v-for="(link, idx) in destinations.links"
                        :key="idx"
                        :href="link.url || '#'"
                        class="turget-btn turget-btn-sm"
                        :class="link.active ? 'turget-btn-primary' : 'turget-btn-secondary'"
                        :style="!link.url ? 'opacity: 0.5; pointer-events: none;' : ''"
                        v-html="link.label"
                    />
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
