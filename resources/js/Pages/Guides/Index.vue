<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    guides: Object,
    regions: Array,
    filters: Object,
    specializationsList: Array,
});

const { t, tr, locale } = useI18n();

const selectedRegion = ref(props.filters.region || '');
const selectedSpecialization = ref(props.filters.specialization || '');
const selectedLanguage = ref(props.filters.language || '');
const searchQuery = ref(props.filters.q || '');

function applyFilters() {
    router.get('/guides', {
        region: selectedRegion.value || undefined,
        specialization: selectedSpecialization.value || undefined,
        language: selectedLanguage.value || undefined,
        q: searchQuery.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
}

function selectSpecialization(spec) {
    selectedSpecialization.value = selectedSpecialization.value === spec ? '' : spec;
    applyFilters();
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
        <Head title="Tour Guide Lokal Terverifikasi — Turget.id" />

        <!-- Header Hero -->
        <section style="background: var(--color-surface-subtle); padding: 2.25rem 0 2rem; border-bottom: 1px solid var(--color-border-subtle);">
            <div class="turget-container">
                <div style="max-width: 720px;">
                    <span class="turget-badge" style="margin-bottom: 0.65rem; background: var(--color-blue-50); color: var(--color-blue-600); border-color: rgba(37,99,235,0.2);">
                        🛡️ 100% Pemandu Wisata Terverifikasi & Berlisensi
                    </span>
                    <h1 style="font-family: var(--font-heading); font-size: 1.85rem; font-weight: 700; color: var(--color-navy-950); line-height: 1.2; letter-spacing: -0.025em; margin-bottom: 0.5rem;">
                        Temukan Tour Guide Lokal Terpercaya di Indonesia
                    </h1>
                    <p style="font-size: 0.92rem; color: var(--color-text-muted); line-height: 1.55;">
                        Jelajahi keajaiban nusantara bersama pemandu lokal berpengetahuan mendalam. Transparan, aman, dan dapat disesuaikan dengan kebutuhan perjalanan Anda.
                    </p>
                </div>
            </div>
        </section>

        <!-- Filters Bar -->
        <section style="background: white; border-bottom: 1px solid var(--color-border-subtle); padding: 0.85rem 0; position: sticky; top: 3.75rem; z-index: 20; box-shadow: var(--shadow-xs);">
            <div class="turget-container">
                <div style="display: flex; flex-wrap: wrap; gap: 1rem; justify-content: space-between; align-items: center;">
                    <!-- Filter Controls -->
                    <div style="display: flex; flex-wrap: wrap; gap: 0.75rem; align-items: center; flex: 1;">
                        <!-- Region Dropdown -->
                        <select
                            v-model="selectedRegion"
                            @change="applyFilters"
                            class="turget-form-select"
                            style="width: auto; min-width: 170px; padding: 0.55rem 0.85rem; font-size: 0.88rem;"
                        >
                            <option value="">Semua Wilayah</option>
                            <option v-for="reg in regions" :key="reg.id" :value="reg.id">
                                {{ tr(reg.name) }}
                            </option>
                        </select>

                        <!-- Language Dropdown -->
                        <select
                            v-model="selectedLanguage"
                            @change="applyFilters"
                            class="turget-form-select"
                            style="width: auto; min-width: 150px; padding: 0.55rem 0.85rem; font-size: 0.88rem;"
                        >
                            <option value="">Semua Bahasa</option>
                            <option value="id">Bahasa Indonesia</option>
                            <option value="en">English</option>
                            <option value="ja">日本語 (Japanese)</option>
                            <option value="fr">Français (French)</option>
                            <option value="nl">Nederlands (Dutch)</option>
                            <option value="zh">中文 (Chinese)</option>
                        </select>

                        <!-- Search Input -->
                        <div style="min-width: 220px;">
                            <input
                                type="search"
                                v-model="searchQuery"
                                @keyup.enter="applyFilters"
                                placeholder="Cari nama guide..."
                                class="turget-form-input"
                                style="padding: 0.55rem 0.85rem; font-size: 0.88rem;"
                            />
                        </div>
                    </div>

                    <!-- Join CTA -->
                    <div>
                        <Link href="/become-a-guide" class="turget-btn turget-btn-secondary turget-btn-sm" style="color: var(--color-blue-600); border-color: rgba(37,99,235,0.3);">
                            + Daftar Jadi Tour Guide
                        </Link>
                    </div>
                </div>

                <!-- Specialization Pills -->
                <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 1rem; align-items: center;">
                    <span style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase; margin-right: 0.25rem;">
                        Spesialisasi:
                    </span>
                    <button
                        type="button"
                        class="turget-pill"
                        :class="{ 'is-active': selectedSpecialization === '' }"
                        @click="selectSpecialization('')"
                        style="font-size: 0.82rem; padding: 0.35rem 0.85rem;"
                    >
                        Semua
                    </button>
                    <button
                        v-for="spec in specializationsList"
                        :key="spec.value"
                        type="button"
                        class="turget-pill"
                        :class="{ 'is-active': selectedSpecialization === spec.value }"
                        @click="selectSpecialization(spec.value)"
                        style="font-size: 0.82rem; padding: 0.35rem 0.85rem;"
                    >
                        {{ locale === 'en' ? spec.label_en : spec.label_id }}
                    </button>
                </div>
            </div>
        </section>

        <!-- Guides Grid -->
        <section class="turget-section" style="background: var(--color-surface-white);">
            <div class="turget-container">
                <div v-if="guides.data.length > 0" class="turget-grid-3">
                    <div
                        v-for="guide in guides.data"
                        :key="guide.id"
                        class="turget-card"
                        style="display: flex; flex-direction: column; height: 100%; transition: transform 0.2s, box-shadow 0.2s;"
                    >
                        <!-- Cover & Avatar Banner -->
                        <div style="position: relative; height: 100px; background: var(--color-surface-subtle); overflow: hidden;">
                            <img
                                :src="guide.cover_image_url || '/images/destinations/bali_hero.jpg'"
                                :alt="guide.display_name"
                                style="width: 100%; height: 100%; object-fit: cover;"
                            />
                            <!-- Region Badge -->
                            <span class="turget-card-badge" style="top: 0.5rem; left: 0.5rem; font-size: 0.7rem; padding: 0.15rem 0.5rem;">
                                📍 {{ tr(guide.region?.name) }}
                            </span>
                        </div>

                        <!-- Card Body -->
                        <div class="turget-card-body" style="padding: 0.85rem 0.95rem; flex: 1; display: flex; flex-direction: column;">
                            <!-- Avatar with Verified Badge -->
                            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: -2.6rem; margin-bottom: 0.5rem;">
                                <div style="position: relative;">
                                    <img
                                        :src="guide.avatar_url || '/images/destinations/bali_hero.jpg'"
                                        :alt="guide.display_name"
                                        style="width: 58px; height: 58px; border-radius: 50%; object-fit: cover; border: 2.5px solid white; box-shadow: var(--shadow-sm);"
                                    />
                                    <span
                                        v-if="guide.badge_verified"
                                        title="Pemandu Terverifikasi Turget"
                                        style="position: absolute; bottom: 1px; right: 1px; background: var(--color-blue-600); color: white; width: 18px; height: 18px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; border: 2px solid white;"
                                    >
                                        ✓
                                    </span>
                                </div>

                                <!-- Rating & Trips Count -->
                                <div style="text-align: right;">
                                    <div style="display: flex; align-items: center; gap: 0.25rem; font-weight: 700; color: var(--color-navy-950); font-size: 0.88rem;">
                                        <span style="color: #F59E0B;">★</span>
                                        <span>{{ guide.rating_avg || '5.0' }}</span>
                                        <span style="font-weight: 400; color: var(--color-text-muted); font-size: 0.75rem;">
                                            ({{ guide.reviews_count }})
                                        </span>
                                    </div>
                                    <div style="font-size: 0.74rem; color: var(--color-text-muted);">
                                        {{ guide.trips_completed_count }} perjalanan
                                    </div>
                                </div>
                            </div>

                            <!-- Name & Tagline -->
                            <div style="margin-bottom: 0.5rem;">
                                <h3 style="font-family: var(--font-heading); font-size: 1.05rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.2rem; letter-spacing: -0.02em;">
                                    {{ guide.display_name }}
                                </h3>
                                <p style="font-size: 0.8rem; color: var(--color-text-muted); line-height: 1.4; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                    {{ tr(guide.tagline) || tr(guide.bio) }}
                                </p>
                            </div>

                            <!-- Specializations -->
                            <div style="display: flex; flex-wrap: wrap; gap: 0.3rem; margin-bottom: 0.65rem;">
                                <span
                                    v-for="(spec, sIdx) in guide.specializations?.slice(0, 3)"
                                    :key="sIdx"
                                    class="turget-badge"
                                    style="margin: 0; padding: 0.12rem 0.45rem; font-size: 0.68rem; background: var(--color-surface-subtle);"
                                >
                                    {{ spec }}
                                </span>
                            </div>

                            <!-- Spoken Languages -->
                            <div style="display: flex; align-items: center; gap: 0.35rem; font-size: 0.76rem; color: var(--color-text-muted); margin-bottom: 0.85rem;">
                                <span>🗣️</span>
                                <span>{{ guide.languages?.map(l => l.toUpperCase()).join(', ') }}</span>
                                <span style="margin: 0 0.2rem;">•</span>
                                <span>{{ guide.experience_years }} thn</span>
                            </div>

                            <!-- Card Footer: Rate & Action -->
                            <div style="margin-top: auto; padding-top: 0.65rem; border-top: 1px solid var(--color-border-subtle); display: flex; align-items: center; justify-content: space-between;">
                                <div>
                                    <div style="font-size: 0.72rem; color: var(--color-text-muted);">Tarif Jasa</div>
                                    <div style="font-size: 0.98rem; font-weight: 800; color: var(--color-blue-600);">
                                        {{ formatPrice(guide.price_per_day) }}
                                        <span style="font-size: 0.72rem; font-weight: 400; color: var(--color-text-muted);">/ hari</span>
                                    </div>
                                </div>

                                <Link :href="`/guides/${guide.slug}`" class="turget-btn turget-btn-primary turget-btn-sm">
                                    Lihat Profil →
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-else style="text-align: center; padding: 5rem 1rem; background: var(--color-surface-subtle); border-radius: var(--radius-xl);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🧭</div>
                    <h3 style="font-family: var(--font-serif); font-size: 1.5rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        Belum ada tour guide yang sesuai kriteria pencarian
                    </h3>
                    <p style="color: var(--color-text-muted); max-width: 500px; margin: 0 auto 1.5rem;">
                        Coba ubah filter wilayah, bahasa, atau spesialisasi untuk menemukan pandu wisata yang Anda inginkan.
                    </p>
                    <button type="button" @click="selectedRegion = ''; selectedSpecialization = ''; selectedLanguage = ''; searchQuery = ''; applyFilters();" class="turget-btn turget-btn-secondary">
                        Reset Semua Filter
                    </button>
                </div>

                <!-- Pagination -->
                <div v-if="guides.links && guides.links.length > 3" class="turget-pagination" style="margin-top: 2.5rem; border-radius: var(--radius-lg);">
                    <div style="color: var(--color-text-muted);">
                        Menampilkan {{ guides.from || 0 }} - {{ guides.to || 0 }} dari {{ guides.total }} tour guide terverifikasi
                    </div>
                    <div class="turget-pagination-links">
                        <template v-for="(link, i) in guides.links" :key="i">
                            <span v-if="!link.url" class="turget-pagination-btn is-disabled" v-html="link.label" />
                            <Link v-else :href="link.url" class="turget-pagination-btn" :class="{ 'is-active': link.active }" v-html="link.label" preserve-scroll preserve-state />
                        </template>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
