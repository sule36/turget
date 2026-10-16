<script setup>
import { computed, ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    favorites: {
        type: Array,
        default: () => [],
    },
});

const { t, tr, locale } = useI18n();
const activeFilter = ref('all'); // all | package | guide | destination

const filteredFavorites = computed(() => {
    if (activeFilter.value === 'all') {
        return props.favorites;
    }
    return props.favorites.filter((f) => f.type === activeFilter.value);
});

const packageCount = computed(() => props.favorites.filter((f) => f.type === 'package').length);
const guideCount = computed(() => props.favorites.filter((f) => f.type === 'guide').length);
const destinationCount = computed(() => props.favorites.filter((f) => f.type === 'destination').length);

function removeFavorite(type, id) {
    router.post('/favorites/toggle', {
        type: type,
        id: id,
    }, {
        preserveScroll: true,
    });
}

function formatPrice(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
}
</script>

<template>
    <PublicLayout>
        <Head :title="locale === 'id' ? 'Wishlist & Favorit Saya — Turget.id' : 'My Saved Favorites — Turget.id'" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem; min-height: 80vh;">
            <div class="turget-container">
                <!-- Header -->
                <div style="margin-bottom: 2rem;">
                    <span class="turget-badge" style="background: var(--color-rose-50); color: #e11d48; margin-bottom: 0.5rem;">
                        ❤️ {{ locale === 'id' ? 'Koleksi Liburan Impian' : 'Saved Wishlist' }}
                    </span>
                    <h1 style="font-family: var(--font-heading); font-size: 2.2rem; font-weight: 800; color: var(--color-navy-950); margin-bottom: 0.35rem;">
                        {{ locale === 'id' ? 'Daftar Favorit Saya' : 'My Favorites' }}
                    </h1>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem; margin: 0;">
                        {{ locale === 'id' 
                            ? 'Kumpulan paket wisata, pemandu profesional, dan destinasi yang Anda simpan untuk referensi liburan.' 
                            : 'All your curated tour packages, guides, and destinations saved in one place.' }}
                    </p>
                </div>

                <!-- Tabs Filter -->
                <div style="display: flex; gap: 0.5rem; margin-bottom: 2rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.5rem; flex-wrap: wrap;">
                    <button
                        type="button"
                        class="turget-tab-btn"
                        :class="{ 'is-active': activeFilter === 'all' }"
                        @click="activeFilter = 'all'"
                    >
                        Semua ({{ favorites.length }})
                    </button>
                    <button
                        type="button"
                        class="turget-tab-btn"
                        :class="{ 'is-active': activeFilter === 'package' }"
                        @click="activeFilter = 'package'"
                    >
                        🎒 Paket Wisata ({{ packageCount }})
                    </button>
                    <button
                        type="button"
                        class="turget-tab-btn"
                        :class="{ 'is-active': activeFilter === 'guide' }"
                        @click="activeFilter = 'guide'"
                    >
                        🧭 Pemandu Wisata ({{ guideCount }})
                    </button>
                    <button
                        type="button"
                        class="turget-tab-btn"
                        :class="{ 'is-active': activeFilter === 'destination' }"
                        @click="activeFilter = 'destination'"
                    >
                        🏛️ Destinasi ({{ destinationCount }})
                    </button>
                </div>

                <!-- Grid List -->
                <div v-if="filteredFavorites.length > 0" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(290px, 1fr)); gap: 1.5rem;">
                    <div
                        v-for="fav in filteredFavorites"
                        :key="fav.id"
                        class="turget-admin-card"
                        style="padding: 0; overflow: hidden; display: flex; flex-direction: column; position: relative;"
                    >
                        <!-- Top Image -->
                        <div style="position: relative; height: 180px; background: var(--color-navy-950);">
                            <img
                                v-if="fav.type === 'guide'"
                                :src="fav.data.cover_image_url || fav.data.avatar_url || '/images/destinations/bali_hero.jpg'"
                                :alt="fav.data.display_name"
                                style="width: 100%; height: 100%; object-fit: cover;"
                            />
                            <img
                                v-else-if="fav.type === 'package'"
                                :src="fav.data.cover_image || '/images/destinations/bali_hero.jpg'"
                                :alt="tr(fav.data.title)"
                                style="width: 100%; height: 100%; object-fit: cover;"
                            />
                            <img
                                v-else
                                :src="fav.data.cover_image || '/images/destinations/bali_hero.jpg'"
                                :alt="tr(fav.data.name)"
                                style="width: 100%; height: 100%; object-fit: cover;"
                            />

                            <!-- Badge Type -->
                            <span
                                style="position: absolute; top: 10px; left: 10px; background: rgba(7, 18, 29, 0.75); backdrop-filter: blur(4px); color: white; font-size: 0.75rem; font-weight: 700; padding: 3px 8px; border-radius: var(--radius-sm);"
                            >
                                {{ fav.type === 'guide' ? '🧭 Tour Guide' : (fav.type === 'package' ? '🎒 Paket Wisata' : '🏛️ Destinasi') }}
                            </span>

                            <!-- Remove Button -->
                            <button
                                type="button"
                                title="Hapus dari Favorit"
                                @click="removeFavorite(fav.type, fav.item_id)"
                                style="position: absolute; top: 10px; right: 10px; width: 32px; height: 32px; border-radius: 50%; background: white; border: none; display: flex; align-items: center; justify-content: center; cursor: pointer; box-shadow: var(--shadow-sm); font-size: 1rem; color: #e11d48;"
                            >
                                ❤️
                            </button>
                        </div>

                        <!-- Card Body -->
                        <div style="padding: 1.25rem; flex: 1; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <!-- Guide Body -->
                                <template v-if="fav.type === 'guide'">
                                    <div style="display: flex; align-items: center; gap: 0.4rem; margin-bottom: 0.25rem;">
                                        <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin: 0;">
                                            {{ fav.data.display_name }}
                                        </h3>
                                        <span v-if="fav.data.badge_verified" title="Terverifikasi" style="color: var(--color-blue-600); font-size: 0.9rem;">✓</span>
                                    </div>
                                    <p style="font-size: 0.82rem; color: var(--color-text-muted); margin-bottom: 0.5rem;">
                                        📍 {{ tr(fav.data.region?.name) }} • ⭐ {{ fav.data.rating_avg || '5.0' }} ({{ fav.data.reviews_count }} ulasan)
                                    </p>
                                    <div style="font-size: 0.85rem; color: var(--color-navy-950); margin-bottom: 1rem; font-weight: 700;">
                                        {{ formatPrice(fav.data.price_per_day) }} <span style="font-weight: 400; color: var(--color-text-muted); font-size: 0.75rem;">/ hari</span>
                                    </div>
                                </template>

                                <!-- Package Body -->
                                <template v-else-if="fav.type === 'package'">
                                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-navy-950); margin: 0 0 0.35rem; line-height: 1.35;">
                                        {{ tr(fav.data.title) }}
                                    </h3>
                                    <p style="font-size: 0.82rem; color: var(--color-text-muted); margin-bottom: 0.5rem;">
                                        ⏱️ {{ fav.data.duration_days }} Hari • 🧭 {{ fav.data.guide?.display_name || 'Guide Berlisensi' }}
                                    </p>
                                    <div style="font-size: 0.85rem; color: var(--color-blue-600); margin-bottom: 1rem; font-weight: 800;">
                                        {{ formatPrice(fav.data.base_price) }} <span style="font-weight: 400; color: var(--color-text-muted); font-size: 0.75rem;">/ orang</span>
                                    </div>
                                </template>

                                <!-- Destination Body -->
                                <template v-else>
                                    <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin: 0 0 0.25rem;">
                                        {{ tr(fav.data.name) }}
                                    </h3>
                                    <p style="font-size: 0.82rem; color: var(--color-text-muted); margin-bottom: 0.5rem;">
                                        📍 {{ tr(fav.data.region?.name) }}
                                    </p>
                                    <p style="font-size: 0.82rem; color: var(--color-navy-950); margin-bottom: 1rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; line-height: 1.4;">
                                        {{ tr(fav.data.summary) || 'Destinasi wisata unggulan di kawasan Indonesia.' }}
                                    </p>
                                </template>
                            </div>

                            <!-- Link Action -->
                            <div>
                                <Link
                                    v-if="fav.type === 'guide'"
                                    :href="`/guides/${fav.data.slug}`"
                                    class="turget-btn turget-btn-secondary"
                                    style="width: 100%; justify-content: center; font-size: 0.85rem;"
                                >
                                    Lihat Profil Pemandu →
                                </Link>
                                <Link
                                    v-else-if="fav.type === 'package'"
                                    :href="`/packages/${fav.data.slug}`"
                                    class="turget-btn turget-btn-primary"
                                    style="width: 100%; justify-content: center; font-size: 0.85rem;"
                                >
                                    Lihat & Pesan Paket →
                                </Link>
                                <Link
                                    v-else
                                    :href="`/places/${fav.data.slug}`"
                                    class="turget-btn turget-btn-secondary"
                                    style="width: 100%; justify-content: center; font-size: 0.85rem;"
                                >
                                    Jelajahi Destinasi →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else style="background: white; border-radius: var(--radius-xl); padding: 4rem 2rem; text-align: center; border: 1px dashed var(--color-border-subtle); max-width: 600px; margin: 0 auto;">
                    <div style="font-size: 3rem; margin-bottom: 0.75rem;">💖</div>
                    <h3 style="font-size: 1.35rem; font-weight: 800; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        Belum Ada Item yang Disimpan
                    </h3>
                    <p style="color: var(--color-text-muted); font-size: 0.92rem; margin-bottom: 1.5rem; line-height: 1.5;">
                        Simpan pemandu favorit, destinasi impian, atau paket wisata pilihan Anda dengan mengklik ikon hati (❤️) di setiap kartu.
                    </p>
                    <div style="display: flex; gap: 0.75rem; justify-content: center; flex-wrap: wrap;">
                        <Link href="/packages" class="turget-btn turget-btn-primary">
                            🎒 Jelajahi Paket Wisata
                        </Link>
                        <Link href="/guides" class="turget-btn turget-btn-secondary">
                            🧭 Cari Tour Guide
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
