<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    viewMode: { type: String, default: 'products' },
    vendors: { type: Object, default: null },
    products: { type: Object, default: null },
    categoriesList: { type: Array, default: () => [] },
    regions: { type: Array, default: () => [] },
    filters: { type: Object, default: () => ({}) },
});

const { t, tr, locale } = useI18n();

const currentView = ref(props.filters.view || 'products');
const selectedCategory = ref(props.filters.category || '');
const selectedRegion = ref(props.filters.region || '');
const searchQuery = ref(props.filters.q || '');
const selectedSort = ref(props.filters.sort || 'recommended');

function applyFilters() {
    router.get('/vendors', {
        view: currentView.value,
        category: selectedCategory.value || undefined,
        region: selectedRegion.value || undefined,
        q: searchQuery.value || undefined,
        sort: selectedSort.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function switchView(mode) {
    currentView.value = mode;
    applyFilters();
}

function selectCategory(cat) {
    selectedCategory.value = selectedCategory.value === cat ? '' : cat;
    applyFilters();
}

function formatPrice(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
}

function getUnitLabel(unit) {
    const units = {
        per_night: locale.value === 'en' ? '/ night' : '/ malam',
        per_day: locale.value === 'en' ? '/ day' : '/ hari',
        per_person: locale.value === 'en' ? '/ person' : '/ orang',
        per_session: locale.value === 'en' ? '/ session' : '/ sesi',
        per_unit: locale.value === 'en' ? '/ unit' : '/ unit',
    };
    return units[unit] || '';
}

function getCategoryIcon(cat) {
    const icons = {
        accommodation: '🏨',
        vehicle_rental: '🚗',
        activity: '🤿',
        travel_support: '📷',
    };
    return icons[cat] || '✨';
}
</script>

<template>
    <PublicLayout>
        <Head :title="locale === 'en' ? 'Local Experience & Vendor Marketplace — Turget.id' : 'Marketplace Vendor & Layanan Lokal — Turget.id'" />

        <!-- Header Hero -->
        <section style="background: var(--color-surface-subtle); padding: 2.25rem 0 2rem; border-bottom: 1px solid var(--color-border-subtle);">
            <div class="turget-container">
                <div style="max-width: 820px;">
                    <span class="turget-badge" style="margin-bottom: 0.65rem; background: var(--color-blue-50); color: var(--color-blue-600); border-color: rgba(37,99,235,0.2);">
                        🏨 🚗 🤿 📷 Mitra Terverifikasi Turget.id
                    </span>
                    <h1 style="font-family: var(--font-heading); font-size: 1.85rem; font-weight: 700; color: var(--color-navy-950); line-height: 1.2; letter-spacing: -0.025em; margin-bottom: 0.5rem;">
                        {{ locale === 'en' ? 'Verified Local Vendors, Stays & Activities' : 'Penginapan, Rental, Tiket Aktivitas & Jasa Wisata Lokal' }}
                    </h1>
                    <p style="font-size: 0.92rem; color: var(--color-text-muted); line-height: 1.55; margin-bottom: 1.25rem;">
                        {{ locale === 'en'
                            ? 'Complete your trip with handpicked local accommodations, reliable 4x4 & car rentals, liveaboards, and outdoor adventures.'
                            : 'Lengkapi rencana perjalanan Anda dengan penginapan berlisensi, sewa kendaraan terpercaya, tiket wahana, kapal bahari, hingga dokumentasi foto.' }}
                    </p>

                    <!-- View Switcher Tabs -->
                    <div style="display: inline-flex; background: white; padding: 0.25rem; border-radius: var(--radius-pill); box-shadow: var(--shadow-xs); border: 1px solid var(--color-border-subtle); margin-bottom: 1.25rem;">
                        <button
                            type="button"
                            @click="switchView('products')"
                            style="padding: 0.35rem 1rem; border-radius: var(--radius-pill); font-size: 0.82rem; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s;"
                            :style="currentView === 'products' ? 'background: var(--color-blue-600); color: white; box-shadow: var(--shadow-xs);' : 'background: transparent; color: var(--color-navy-800);'"
                        >
                            📦 {{ locale === 'en' ? 'All Services & Add-ons' : 'Katalog Layanan & Add-on' }}
                        </button>
                        <button
                            type="button"
                            @click="switchView('vendors')"
                            style="padding: 0.35rem 1rem; border-radius: var(--radius-pill); font-size: 0.82rem; font-weight: 700; border: none; cursor: pointer; transition: all 0.2s;"
                            :style="currentView === 'vendors' ? 'background: var(--color-blue-600); color: white; box-shadow: var(--shadow-xs);' : 'background: transparent; color: var(--color-navy-800);'"
                        >
                            🏢 {{ locale === 'en' ? 'Vendor Partners' : 'Penyedia Jasa (Vendors)' }}
                        </button>
                    </div>

                    <!-- Search Input -->
                    <div style="display: flex; gap: 0.5rem; max-width: 580px;">
                        <div style="flex: 1; position: relative;">
                            <input
                                v-model="searchQuery"
                                type="text"
                                class="turget-form-input"
                                :placeholder="locale === 'en' ? 'Search by stay name, vehicle, activity or vendor...' : 'Cari villa, rental mobil, diving, atau nama vendor...'"
                                @keyup.enter="applyFilters"
                                style="background: white; padding-left: 2.25rem; font-size: 0.88rem; padding-top: 0.45rem; padding-bottom: 0.45rem;"
                            />
                            <span style="position: absolute; left: 0.75rem; top: 50%; transform: translateY(-50%); font-size: 0.95rem; opacity: 0.5;">🔍</span>
                        </div>
                        <button type="button" class="turget-btn turget-btn-primary turget-btn-sm" @click="applyFilters" style="padding: 0 1.25rem;">
                            {{ t('btn.search', 'Cari') }}
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categories & Filter Strip -->
        <div style="background: white; border-bottom: 1px solid var(--color-border-subtle); padding: 0.75rem 0; position: sticky; top: 3.75rem; z-index: 20;">
            <div class="turget-container" style="display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: wrap;">
                <!-- Category Pills -->
                <div style="display: flex; gap: 0.4rem; overflow-x: auto; padding-bottom: 0.15rem; flex-wrap: wrap;">
                    <button
                        type="button"
                        class="turget-badge"
                        style="cursor: pointer; padding: 0.25rem 0.75rem; font-size: 0.76rem; transition: all 0.15s; margin: 0;"
                        :style="selectedCategory === '' ? 'background: var(--color-navy-950); color: white; border-color: var(--color-navy-950);' : 'background: var(--color-surface-subtle); color: var(--color-navy-800);'"
                        @click="selectCategory('')"
                    >
                        ✨ {{ locale === 'en' ? 'All Categories' : 'Semua Kategori' }}
                    </button>
                    <button
                        v-for="cat in categoriesList"
                        :key="cat.value"
                        type="button"
                        class="turget-badge"
                        style="cursor: pointer; padding: 0.25rem 0.75rem; font-size: 0.76rem; transition: all 0.15s; margin: 0;"
                        :style="selectedCategory === cat.value ? 'background: var(--color-blue-600); color: white; border-color: var(--color-blue-600);' : 'background: var(--color-surface-subtle); color: var(--color-navy-800);'"
                        @click="selectCategory(cat.value)"
                    >
                        <span>{{ cat.icon }}</span>
                        <span>{{ locale === 'en' ? cat.label_en : cat.label_id }}</span>
                    </button>
                </div>

                <!-- Region & Sort Dropdowns -->
                <div style="display: flex; gap: 0.75rem; align-items: center;">
                    <select v-model="selectedRegion" class="turget-input" style="padding: 0.45rem 1rem; font-size: 0.85rem; width: auto;" @change="applyFilters">
                        <option value="">🗺️ {{ locale === 'en' ? 'All Regions' : 'Semua Wilayah' }}</option>
                        <option v-for="r in regions" :key="r.id" :value="r.id">
                            {{ r.name }}
                        </option>
                    </select>

                    <select v-model="selectedSort" class="turget-input" style="padding: 0.45rem 1rem; font-size: 0.85rem; width: auto;" @change="applyFilters">
                        <option value="recommended">⭐ {{ locale === 'en' ? 'Recommended' : 'Rekomendasi' }}</option>
                        <option value="rating">🏆 {{ locale === 'en' ? 'Top Rated' : 'Rating Tertinggi' }}</option>
                        <option v-if="currentView === 'products'" value="price_asc">💵 {{ locale === 'en' ? 'Price: Low to High' : 'Harga Terendah' }}</option>
                        <option v-if="currentView === 'products'" value="price_desc">💎 {{ locale === 'en' ? 'Price: High to Low' : 'Harga Tertinggi' }}</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Main Marketplace Body -->
        <main class="turget-container" style="padding: 3.5rem 0 5rem;">
            <!-- PRODUCTS CATALOG VIEW -->
            <div v-if="currentView === 'products'">
                <div v-if="products?.data?.length > 0" class="turget-grid-3" style="gap: 2rem;">
                    <div
                        v-for="item in products.data"
                        :key="item.id"
                        class="turget-card"
                        style="display: flex; flex-direction: column; overflow: hidden; border-radius: var(--radius-xl); border: 1px solid var(--color-border-subtle); transition: transform 0.2s, box-shadow 0.2s;"
                        onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-lg)';"
                        onmouseout="this.style.transform='none'; this.style.boxShadow='var(--shadow-sm)';"
                    >
                        <!-- Cover Image -->
                        <div style="position: relative; height: 210px; background: var(--color-sand-200); overflow: hidden;">
                            <img
                                :src="item.cover_image || 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=800&auto=format&fit=crop&q=80'"
                                :alt="tr(item.name)"
                                style="width: 100%; height: 100%; object-fit: cover;"
                            />
                            <!-- Category Badge -->
                            <div style="position: absolute; top: 1rem; left: 1rem;">
                                <span class="turget-badge" style="background: rgba(11,27,43,0.85); color: white; border: none; backdrop-filter: blur(8px); font-size: 0.78rem;">
                                    {{ getCategoryIcon(item.category) }} {{ item.category }}
                                </span>
                            </div>

                            <!-- Booking Model Badge -->
                            <div style="position: absolute; bottom: 0.85rem; right: 1rem;">
                                <span v-if="item.booking_model === 'instant'" class="turget-badge" style="background: rgba(16,185,129,0.9); color: white; border: none; font-size: 0.75rem;">
                                    ⚡ {{ locale === 'en' ? 'Instant Confirmation' : 'Instan' }}
                                </span>
                                <span v-else class="turget-badge" style="background: rgba(37,99,235,0.9); color: white; border: none; font-size: 0.75rem;">
                                    📅 {{ locale === 'en' ? 'Request to Book' : 'Reservasi' }}
                                </span>
                            </div>
                        </div>

                        <!-- Content Body -->
                        <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                            <!-- Destination tag if any -->
                            <div v-if="item.destination" style="font-size: 0.8rem; font-weight: 600; color: var(--color-blue-600); margin-bottom: 0.4rem;">
                                📍 Dekat {{ tr(item.destination.name) }}
                            </div>

                            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.6rem; line-height: 1.35;">
                                <Link :href="`/vendors/products/${item.slug}`" style="color: inherit; text-decoration: none;">
                                    {{ tr(item.name) }}
                                </Link>
                            </h3>

                            <p style="font-size: 0.92rem; color: var(--color-text-muted); line-height: 1.55; margin-bottom: 1.25rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ tr(item.description) }}
                            </p>

                            <!-- Vendor info pill -->
                            <div v-if="item.vendor" style="display: flex; align-items: center; gap: 0.6rem; margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--color-border-subtle); margin-bottom: 1.25rem;">
                                <div style="width: 2.25rem; height: 2.25rem; border-radius: var(--radius-full); background: var(--color-blue-50); color: var(--color-blue-600); display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.9rem;">
                                    {{ item.vendor.business_name?.charAt(0) || 'V' }}
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 0.88rem; font-weight: 600; color: var(--color-navy-900); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; display: flex; align-items: center; gap: 0.35rem;">
                                        <Link :href="`/vendors/${item.vendor.slug}`" style="color: inherit; text-decoration: none;">
                                            {{ item.vendor.business_name }}
                                        </Link>
                                        <span v-if="item.vendor.badge_verified" title="Verified Vendor" style="color: var(--color-blue-600); font-size: 0.85rem;">🛡️</span>
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--color-text-subtle);">
                                        ⭐ {{ item.rating_avg || '5.0' }} ({{ item.reviews_count || 0 }} ulasan)
                                    </div>
                                </div>
                            </div>

                            <!-- Price & Action -->
                            <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 0.5rem;">
                                <div>
                                    <div style="font-size: 0.75rem; color: var(--color-text-subtle); text-transform: uppercase;">
                                        {{ locale === 'en' ? 'Starting From' : 'Mulai Dari' }}
                                    </div>
                                    <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-blue-600);">
                                        {{ formatPrice(item.price) }}
                                        <span style="font-size: 0.8rem; font-weight: 500; color: var(--color-text-muted);">
                                            {{ getUnitLabel(item.price_unit) }}
                                        </span>
                                    </div>
                                </div>
                                <Link :href="`/vendors/products/${item.slug}`" class="turget-btn turget-btn-primary" style="padding: 0.5rem 1rem; font-size: 0.88rem;">
                                    {{ locale === 'en' ? 'Details' : 'Lihat Detail' }}
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else style="text-align: center; padding: 5rem 1rem; background: var(--color-surface-subtle); border-radius: var(--radius-2xl);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        {{ locale === 'en' ? 'No services found' : 'Layanan tidak ditemukan' }}
                    </h3>
                    <p style="color: var(--color-text-muted); max-width: 480px; margin: 0 auto 1.5rem;">
                        {{ locale === 'en' ? 'Try adjusting your filters or search keywords.' : 'Coba ubah filter kategori, wilayah, atau kata kunci pencarian Anda.' }}
                    </p>
                    <button type="button" class="turget-btn turget-btn-secondary" @click="selectedCategory = ''; selectedRegion = ''; searchQuery = ''; applyFilters();">
                        {{ locale === 'en' ? 'Reset Filters' : 'Reset Semua Filter' }}
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="products?.links?.length > 3" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 3.5rem;">
                    <Component
                        :is="link.url ? Link : 'span'"
                        v-for="(link, i) in products.links"
                        :key="i"
                        :href="link.url || undefined"
                        v-html="link.label"
                        class="turget-badge"
                        style="padding: 0.6rem 1rem; font-size: 0.9rem; text-decoration: none;"
                        :style="link.active ? 'background: var(--color-navy-950); color: white;' : 'background: white; color: var(--color-navy-800);'"
                    />
                </div>
            </div>

            <!-- VENDORS DIRECTORY VIEW -->
            <div v-else>
                <div v-if="vendors?.data?.length > 0" class="turget-grid-3" style="gap: 2rem;">
                    <div
                        v-for="v in vendors.data"
                        :key="v.id"
                        class="turget-card"
                        style="display: flex; flex-direction: column; overflow: hidden; border-radius: var(--radius-xl); border: 1px solid var(--color-border-subtle); transition: transform 0.2s, box-shadow 0.2s;"
                        onmouseover="this.style.transform='translateY(-4px)'; this.style.boxShadow='var(--shadow-lg)';"
                        onmouseout="this.style.transform='none'; this.style.boxShadow='var(--shadow-sm)';"
                    >
                        <!-- Cover Banner -->
                        <div style="position: relative; height: 140px; background: var(--color-navy-900); overflow: hidden;">
                            <img
                                :src="v.cover_image_url || 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=800&auto=format&fit=crop&q=80'"
                                :alt="v.business_name"
                                style="width: 100%; height: 100%; object-fit: cover; opacity: 0.85;"
                            />
                            <div style="position: absolute; top: 1rem; left: 1rem;">
                                <span class="turget-badge" style="background: rgba(11,27,43,0.85); color: white; border: none; font-size: 0.78rem;">
                                    {{ getCategoryIcon(v.category) }} {{ v.category }}
                                </span>
                            </div>
                        </div>

                        <!-- Profile Header -->
                        <div style="padding: 1.5rem; flex: 1; display: flex; flex-direction: column;">
                            <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 0.75rem;">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <h3 style="font-size: 1.35rem; font-weight: 700; color: var(--color-navy-950); margin: 0;">
                                        <Link :href="`/vendors/${v.slug}`" style="color: inherit; text-decoration: none;">
                                            {{ v.business_name }}
                                        </Link>
                                    </h3>
                                    <span v-if="v.badge_verified" title="Vendor Terverifikasi" style="color: var(--color-blue-600); font-size: 1.1rem;">🛡️</span>
                                </div>
                            </div>

                            <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.5rem;">
                                <span>📍 {{ v.region?.name || 'Indonesia' }}</span>
                                <span>•</span>
                                <span>⭐ {{ v.rating_avg }} ({{ v.reviews_count }} ulasan)</span>
                            </div>

                            <p style="font-size: 0.92rem; color: var(--color-text-muted); line-height: 1.55; margin-bottom: 1.5rem; display: -webkit-box; -webkit-line-clamp: 3; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ tr(v.description) }}
                            </p>

                            <!-- Featured Offerings list -->
                            <div v-if="v.products?.length > 0" style="margin-top: auto; padding-top: 1rem; border-top: 1px solid var(--color-border-subtle); margin-bottom: 1.5rem;">
                                <div style="font-size: 0.75rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-subtle); margin-bottom: 0.5rem;">
                                    {{ locale === 'en' ? 'Offerings Sample' : 'Contoh Layanan' }}
                                </div>
                                <div v-for="p in v.products" :key="p.id" style="font-size: 0.85rem; color: var(--color-navy-900); margin-bottom: 0.35rem; display: flex; justify-content: space-between;">
                                    <span style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 65%;">• {{ tr(p.name) }}</span>
                                    <span style="font-weight: 700; color: var(--color-blue-600);">{{ formatPrice(p.price) }}</span>
                                </div>
                            </div>

                            <Link :href="`/vendors/${v.slug}`" class="turget-btn turget-btn-primary" style="width: 100%; text-align: center; justify-content: center;">
                                {{ locale === 'en' ? 'View Storefront & Catalog' : 'Kunjungi Profil & Katalog' }}
                            </Link>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else style="text-align: center; padding: 5rem 1rem; background: var(--color-surface-subtle); border-radius: var(--radius-2xl);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🏢</div>
                    <h3 style="font-size: 1.5rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        {{ locale === 'en' ? 'No vendors found' : 'Tidak ada vendor ditemukan' }}
                    </h3>
                    <p style="color: var(--color-text-muted); max-width: 480px; margin: 0 auto 1.5rem;">
                        {{ locale === 'en' ? 'Try adjusting your filters.' : 'Coba ubah filter kategori atau wilayah Anda.' }}
                    </p>
                    <button type="button" class="turget-btn turget-btn-secondary" @click="selectedCategory = ''; selectedRegion = ''; searchQuery = ''; applyFilters();">
                        {{ locale === 'en' ? 'Reset Filters' : 'Reset Semua Filter' }}
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="vendors?.links?.length > 3" style="display: flex; justify-content: center; gap: 0.5rem; margin-top: 3.5rem;">
                    <Component
                        :is="link.url ? Link : 'span'"
                        v-for="(link, i) in vendors.links"
                        :key="i"
                        :href="link.url || undefined"
                        v-html="link.label"
                        class="turget-badge"
                        style="padding: 0.6rem 1rem; font-size: 0.9rem; text-decoration: none;"
                        :style="link.active ? 'background: var(--color-navy-950); color: white;' : 'background: white; color: var(--color-navy-800);'"
                    />
                </div>
            </div>
        </main>
    </PublicLayout>
</template>
