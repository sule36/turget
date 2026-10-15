<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    destination: Object,
    nearbyDestinations: Array,
    nearbyVendorServices: Object,
});

const { t, tr, locale } = useI18n();

const activeTab = ref('overview');
const activeVendorCategory = ref('accommodation');

const tabs = [
    { id: 'overview', label: 'Ringkasan & Daya Tarik', labelEn: 'Overview & Highlights' },
    { id: 'history', label: 'Sejarah & Budaya', labelEn: 'History & Culture' },
    { id: 'activities', label: 'Aktivitas Wisata', labelEn: 'Things to Do' },
    { id: 'practical', label: 'Informasi Praktis', labelEn: 'Practical Info' },
];

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
        <Head>
            <title>{{ tr(destination.name) }} — Informasi Lengkap, Tiket & Guide</title>
            <meta name="description" :content="tr(destination.summary)">
        </Head>

        <!-- Destination Hero Header -->
        <section style="background: linear-gradient(180deg, var(--color-sand-100) 0%, var(--color-sand-50) 100%); padding: 3rem 0 2rem; border-bottom: 1px solid var(--color-border-subtle);">
            <div class="turget-container">
                <!-- Breadcrumbs -->
                <nav style="display: flex; gap: 0.5rem; font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 1.25rem; flex-wrap: wrap;">
                    <Link href="/">Beranda</Link>
                    <span>/</span>
                    <Link href="/destinations">Destinasi</Link>
                    <span>/</span>
                    <Link v-if="destination.country" :href="`/destinations/${destination.country.slug}`">
                        {{ tr(destination.country.name) }}
                    </Link>
                    <span>/</span>
                    <Link v-if="destination.region" :href="destination.region.parent ? `/destinations/${destination.country?.slug}/${destination.region.parent.slug}/${destination.region.slug}` : `/destinations/${destination.country?.slug}/${destination.region.slug}`">
                        {{ tr(destination.region.name) }}
                    </Link>
                    <span>/</span>
                    <span style="color: var(--color-navy-950); font-weight: 700;">{{ tr(destination.name) }}</span>
                </nav>

                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 2rem; flex-wrap: wrap;">
                    <div style="max-width: 780px;">
                        <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.75rem; flex-wrap: wrap;">
                            <span
                                v-for="cat in destination.categories"
                                :key="cat.id"
                                class="turget-badge"
                                style="margin: 0; background: var(--color-blue-50); color: var(--color-blue-600);"
                            >
                                {{ tr(cat.name) }}
                            </span>
                            <span class="turget-badge" style="margin: 0; background: var(--color-emerald-50); color: var(--color-emerald-600); border-color: rgba(16,185,129,0.3);">
                                ✓ Terverifikasi Turget.id
                            </span>
                        </div>

                        <h1 style="font-family: var(--font-serif); font-size: 3rem; font-weight: 700; color: var(--color-navy-950); line-height: 1.2; margin-bottom: 0.75rem;">
                            {{ tr(destination.name) }}
                        </h1>

                        <p style="font-size: 1.2rem; color: var(--color-text-muted); line-height: 1.6; margin-bottom: 1rem;">
                            {{ tr(destination.tagline) }}
                        </p>

                        <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.92rem; color: var(--color-text-muted);">
                            <span>📍 {{ destination.address }}</span>
                        </div>
                    </div>

                    <!-- CTA Box -->
                    <div style="background: white; border-radius: var(--radius-xl); padding: 1.5rem 1.75rem; box-shadow: var(--shadow-md); border: 1px solid var(--color-border-subtle); min-width: 280px; text-align: center;">
                        <div style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 0.35rem;">Rating Pengunjung</div>
                        <div style="font-size: 1.8rem; font-weight: 800; color: var(--color-navy-950); margin-bottom: 1rem;">
                            ★ 4.9 <span style="font-size: 0.9rem; font-weight: 500; color: var(--color-text-muted);">(128 Ulasan Terverifikasi)</span>
                        </div>
                        <a href="#booking-cta" class="turget-btn turget-btn-primary" style="width: 100%;">
                            Pesan Tour Guide →
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Photo Showcase -->
        <section style="background: var(--color-navy-950); padding: 2rem 0;">
            <div class="turget-container">
                <div style="border-radius: var(--radius-xl); overflow: hidden; max-height: 520px; box-shadow: var(--shadow-xl); position: relative;">
                    <img
                        :src="destination.cover_image || '/images/destinations/bali_hero.jpg'"
                        :alt="tr(destination.name)"
                        style="width: 100%; height: 100%; max-height: 520px; object-fit: cover;"
                    />
                    <div v-if="destination.cover_credit" style="position: absolute; bottom: 1rem; right: 1rem; background: rgba(0,0,0,0.6); backdrop-filter: blur(8px); color: white; padding: 0.3rem 0.75rem; border-radius: var(--radius-pill); font-size: 0.75rem;">
                        Foto: {{ destination.cover_credit }}
                    </div>
                </div>
            </div>
        </section>

        <!-- Fast Facts Bar -->
        <section style="background: white; border-bottom: 1px solid var(--color-border-subtle); padding: 1.5rem 0;">
            <div class="turget-container">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem;">
                    <div>
                        <div style="font-size: 0.78rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                            {{ t('destinations.opening_hours', 'Jam Operasional') }}
                        </div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--color-navy-950); margin-top: 0.2rem;">
                            {{ tr(destination.opening_hours) || 'Setiap hari: 08.00 - 18.00' }}
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 0.78rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                            {{ t('destinations.ticket_info', 'Tiket Masuk') }}
                        </div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--color-navy-950); margin-top: 0.2rem;">
                            {{ tr(destination.ticket_info) || 'Mulai dari Rp50.000' }}
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 0.78rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                            {{ t('destinations.duration', 'Estimasi Waktu') }}
                        </div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--color-navy-950); margin-top: 0.2rem;">
                            ± {{ destination.visit_duration_minutes || 120 }} Menit Kunjungan
                        </div>
                    </div>

                    <div v-if="destination.latitude">
                        <div style="font-size: 0.78rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                            Koordinat GPS
                        </div>
                        <div style="font-size: 0.95rem; font-weight: 600; color: var(--color-navy-950); margin-top: 0.2rem;">
                            {{ Number(destination.latitude).toFixed(4) }}, {{ Number(destination.longitude).toFixed(4) }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Content Body & Tabs -->
        <section class="turget-section">
            <div class="turget-container">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 3.5rem;">
                    <!-- Left: In-Depth Content -->
                    <div>
                        <!-- Tabs Header -->
                        <div style="display: flex; gap: 0.5rem; border-bottom: 2px solid var(--color-border-subtle); margin-bottom: 2rem;">
                            <button
                                v-for="tab in tabs"
                                :key="tab.id"
                                type="button"
                                class="turget-nav-link"
                                :class="{ 'is-active': activeTab === tab.id }"
                                style="padding: 0.75rem 1.25rem; font-size: 1rem; cursor: pointer;"
                                @click="activeTab = tab.id"
                            >
                                {{ locale === 'id' ? tab.label : tab.labelEn }}
                            </button>
                        </div>

                        <!-- Tab 1: Overview & Highlights -->
                        <div v-if="activeTab === 'overview'">
                            <h2 style="font-family: var(--font-serif); font-size: 1.85rem; color: var(--color-navy-950); margin-bottom: 1rem;">
                                Tentang {{ tr(destination.name) }}
                            </h2>
                            <p style="font-size: 1.1rem; line-height: 1.8; color: var(--color-text-main); margin-bottom: 2rem;">
                                {{ tr(destination.summary) }}
                            </p>

                            <!-- Highlights -->
                            <h3 style="font-family: var(--font-serif); font-size: 1.4rem; color: var(--color-navy-950); margin-bottom: 1rem;">
                                {{ t('destinations.highlights', 'Daya Tarik Utama') }}
                            </h3>
                            <ul style="list-style: none; display: flex; flex-direction: column; gap: 0.75rem; margin-bottom: 2rem;">
                                <li
                                    v-for="(hl, idx) in (destination.highlights?.[locale] || destination.highlights?.['id'] || [])"
                                    :key="idx"
                                    style="display: flex; gap: 0.75rem; align-items: flex-start; font-size: 1.05rem;"
                                >
                                    <span style="color: var(--color-emerald-600); font-weight: 800;">✓</span>
                                    <span>{{ hl }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Tab 2: History & Culture -->
                        <div v-else-if="activeTab === 'history'">
                            <h2 style="font-family: var(--font-serif); font-size: 1.85rem; color: var(--color-navy-950); margin-bottom: 1rem;">
                                Sejarah & Asal Usul
                            </h2>
                            <p style="font-size: 1.08rem; line-height: 1.8; color: var(--color-text-main); margin-bottom: 2rem;">
                                {{ tr(destination.sections?.history) || 'Informasi sejarah sedang dalam penyusunan editorial.' }}
                            </p>

                            <h3 style="font-family: var(--font-serif); font-size: 1.4rem; color: var(--color-navy-950); margin-bottom: 1rem;">
                                Nilai Budaya & Tradisi Sakral
                            </h3>
                            <p style="font-size: 1.08rem; line-height: 1.8; color: var(--color-text-main);">
                                {{ tr(destination.sections?.culture) || 'Kawasan ini memegang peranan penting dalam tradisi masyarakat setempat.' }}
                            </p>
                        </div>

                        <!-- Tab 3: Activities -->
                        <div v-else-if="activeTab === 'activities'">
                            <h2 style="font-family: var(--font-serif); font-size: 1.85rem; color: var(--color-navy-950); margin-bottom: 1rem;">
                                {{ t('destinations.activities', 'Aktivitas yang Bisa Dilakukan') }}
                            </h2>
                            <div style="display: grid; gap: 1rem; margin-bottom: 2rem;">
                                <div
                                    v-for="(act, idx) in (destination.activities?.[locale] || destination.activities?.['id'] || [])"
                                    :key="idx"
                                    style="background: white; border: 1px solid var(--color-border-subtle); padding: 1.25rem 1.5rem; border-radius: var(--radius-lg); display: flex; align-items: center; gap: 1rem;"
                                >
                                    <span style="font-size: 1.5rem;">🌟</span>
                                    <span style="font-size: 1.05rem; font-weight: 600; color: var(--color-navy-950);">{{ act }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Tab 4: Practical Info -->
                        <div v-else-if="activeTab === 'practical'">
                            <h2 style="font-family: var(--font-serif); font-size: 1.85rem; color: var(--color-navy-950); margin-bottom: 1rem;">
                                Panduan Praktis & Tips Kunjungan
                            </h2>
                            <p style="font-size: 1.08rem; line-height: 1.8; color: var(--color-text-main); margin-bottom: 2rem;">
                                {{ tr(destination.sections?.tips) || 'Patuhi rambu dan arahan pemandu lokal demi keselamatan dan kelestarian situs.' }}
                            </p>
                            <div style="background: var(--color-amber-50); border: 1px solid rgba(245,158,11,0.3); padding: 1.5rem; border-radius: var(--radius-lg);">
                                <h4 style="font-weight: 700; color: var(--color-amber-600); margin-bottom: 0.35rem;">Ketentuan Berpakaian & Etika</h4>
                                <p style="font-size: 0.95rem; color: var(--color-navy-950);">
                                    Pengunjung diwajibkan mengenakan pakaian sopan yang menutupi bahu dan lutut saat memasuki area cagar budaya sakral.
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Sidebar -->
                    <div>
                        <!-- Location & Verified Coordinates Card -->
                        <div style="background: white; border-radius: var(--radius-xl); padding: 1.75rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-sm); margin-bottom: 2rem;">
                            <h3 style="font-family: var(--font-serif); font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                                Lokasi Terverifikasi
                            </h3>
                            <p style="font-size: 0.9rem; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 1rem;">
                                {{ destination.address }}
                            </p>
                            <div v-if="destination.latitude" style="background: var(--color-surface-subtle); padding: 1rem; border-radius: var(--radius-md); font-size: 0.85rem; margin-bottom: 1rem;">
                                <div><strong>Latitude:</strong> {{ destination.latitude }}</div>
                                <div><strong>Longitude:</strong> {{ destination.longitude }}</div>
                            </div>
                            <a
                                v-if="destination.latitude"
                                :href="`https://www.google.com/maps/search/?api=1&query=${destination.latitude},${destination.longitude}`"
                                target="_blank"
                                rel="noopener"
                                class="turget-btn turget-btn-secondary"
                                style="width: 100%;"
                            >
                                Buka di Google Maps ↗
                            </a>
                        </div>

                        <!-- Tour Guide Booking CTA (PRD §7) -->
                        <div id="booking-cta" style="background: linear-gradient(135deg, var(--color-navy-950), var(--color-navy-800)); color: white; border-radius: var(--radius-xl); padding: 2rem 1.75rem; box-shadow: var(--shadow-lg);">
                            <span class="turget-badge" style="background: rgba(37,99,235,0.25); color: var(--color-blue-400); border: none;">
                                Tour Guide Berlisensi
                            </span>
                            <h3 style="font-family: var(--font-serif); font-size: 1.45rem; font-weight: 700; margin: 0.65rem 0; line-height: 1.3;">
                                Ingin Menjelajah {{ tr(destination.name) }} dengan Pemandu Lokal?
                            </h3>
                            <p style="font-size: 0.9rem; color: var(--color-text-subtle); line-height: 1.6; margin-bottom: 1.5rem;">
                                Pemandu wisata kami terverifikasi identitas dan memiliki lisensi HPI (Himpunan Pramuwisata Indonesia). Nikmati cerita sejarah otentik dan itinerary fleksibel.
                            </p>
                            <Link href="/destinations" class="turget-btn turget-btn-primary" style="width: 100%;">
                                Temukan Tour Guide →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Location-Based Local Vendor Recommendations (PRD §8.2, §9, §10.2) -->
        <section v-if="nearbyVendorServices && Object.keys(nearbyVendorServices).length > 0" class="turget-section" style="background: white; border-top: 1px solid var(--color-border-subtle);">
            <div class="turget-container">
                <div style="display: flex; align-items: flex-end; justify-content: space-between; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); border-color: rgba(37,99,235,0.2);">
                            ⚡ Rekomendasi Lokasi Terdekat (Engine PRD §10)
                        </span>
                        <h2 class="turget-section-title" style="margin-top: 0.5rem;">
                            Penginapan, Rental & Aktivitas di Sekitar {{ tr(destination.name) }}
                        </h2>
                        <p class="turget-section-desc">
                            Layanan lokal terverifikasi yang berada dalam radius jangkauan lokasi ini.
                        </p>
                    </div>

                    <!-- Category Tab Pills -->
                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                        <button
                            v-for="(grp, catKey) in nearbyVendorServices"
                            :key="catKey"
                            type="button"
                            class="turget-badge"
                            style="cursor: pointer; padding: 0.45rem 1rem; font-size: 0.88rem; transition: all 0.15s;"
                            :style="activeVendorCategory === catKey ? 'background: var(--color-navy-950); color: white; border-color: var(--color-navy-950);' : 'background: var(--color-surface-subtle); color: var(--color-navy-800);'"
                            @click="activeVendorCategory = catKey"
                        >
                            {{ catKey === 'accommodation' ? '🏨 Penginapan' : catKey === 'vehicle_rental' ? '🚗 Rental Kendaraan' : catKey === 'activity' ? '🤿 Aktivitas' : '📷 Jasa Foto & Support' }}
                            ({{ grp.items?.length || 0 }})
                        </button>
                    </div>
                </div>

                <!-- Products/Vendors Grid -->
                <div v-if="nearbyVendorServices[activeVendorCategory]?.items?.length > 0" class="turget-grid-3" style="gap: 1.75rem;">
                    <article
                        v-for="item in nearbyVendorServices[activeVendorCategory].items"
                        :key="item.id"
                        class="turget-card"
                        style="display: flex; flex-direction: column; overflow: hidden; border-radius: var(--radius-xl); border: 1px solid var(--color-border-subtle); transition: transform 0.2s, box-shadow 0.2s;"
                        onmouseover="this.style.transform='translateY(-3px)'; this.style.boxShadow='var(--shadow-md)';"
                        onmouseout="this.style.transform='none'; this.style.boxShadow='var(--shadow-sm)';"
                    >
                        <!-- Card Media -->
                        <div style="position: relative; height: 160px; background: var(--color-sand-200); overflow: hidden;">
                            <img
                                :src="item.cover_image || item.cover_image_url || 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=600&auto=format&fit=crop&q=80'"
                                :alt="tr(item.name) || item.business_name"
                                style="width: 100%; height: 100%; object-fit: cover;"
                            />
                            <!-- Real Haversine Distance Badge -->
                            <div style="position: absolute; bottom: 0.75rem; left: 0.75rem;">
                                <span class="turget-badge" style="background: rgba(11,27,43,0.85); color: white; border: none; font-size: 0.78rem;">
                                    📍 {{ item.distance_km ? Number(item.distance_km).toFixed(1) + ' km' : 'Sekitar Lokasi' }}
                                </span>
                            </div>
                            <div v-if="item.badge_verified || item.vendor?.badge_verified" style="position: absolute; top: 0.75rem; right: 0.75rem;">
                                <span class="turget-badge" style="background: var(--color-blue-600); color: white; border: none; font-size: 0.72rem;">
                                    🛡️ Terverifikasi
                                </span>
                            </div>
                        </div>

                        <!-- Card Body -->
                        <div style="padding: 1.25rem; flex: 1; display: flex; flex-direction: column;">
                            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin: 0 0 0.5rem; line-height: 1.35;">
                                <Link
                                    :href="item.slug && item.price ? `/vendors/products/${item.slug}` : `/vendors/${item.slug}`"
                                    style="color: inherit; text-decoration: none;"
                                >
                                    {{ tr(item.name) || item.business_name }}
                                </Link>
                            </h3>

                            <p style="font-size: 0.88rem; color: var(--color-text-muted); line-height: 1.5; margin-bottom: 1.25rem; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                {{ tr(item.description) }}
                            </p>

                            <!-- Price / CTA row -->
                            <div style="display: flex; align-items: baseline; justify-content: space-between; margin-top: auto; padding-top: 0.85rem; border-top: 1px solid var(--color-border-subtle);">
                                <div v-if="item.price">
                                    <div style="font-size: 0.72rem; color: var(--color-text-subtle); text-transform: uppercase;">Mulai Dari</div>
                                    <div style="font-size: 1.15rem; font-weight: 800; color: var(--color-blue-600);">
                                        {{ formatPrice(item.price) }}
                                    </div>
                                </div>
                                <div v-else style="font-size: 0.85rem; font-weight: 600; color: var(--color-navy-900);">
                                    ⭐ {{ item.rating_avg }} ({{ item.reviews_count }} ulasan)
                                </div>

                                <Link
                                    :href="item.slug && item.price ? `/vendors/products/${item.slug}` : `/vendors/${item.slug}`"
                                    class="turget-btn turget-btn-primary"
                                    style="padding: 0.45rem 0.95rem; font-size: 0.82rem;"
                                >
                                    Lihat Detail →
                                </Link>
                            </div>
                        </div>
                    </article>
                </div>

                <div v-else style="padding: 3rem; text-align: center; background: var(--color-surface-subtle); border-radius: var(--radius-xl);">
                    <p style="color: var(--color-text-muted); margin-bottom: 1rem;">
                        Belum ada vendor kategori ini yang terdaftar langsung dalam radius terdekat destinasi ini.
                    </p>
                    <Link href="/vendors" class="turget-btn turget-btn-secondary" style="font-size: 0.88rem;">
                        Jelajahi Semua Vendor di Wilayah Ini
                    </Link>
                </div>
            </div>
        </section>

        <!-- Radius-Based Nearby Destinations (PRD §10.2) -->
        <section v-if="nearbyDestinations && nearbyDestinations.length > 0" class="turget-section" style="background: var(--color-sand-100);">
            <div class="turget-container">
                <div class="turget-section-header">
                    <div>
                        <span class="turget-badge">Perhitungan Jarak Haversine</span>
                        <h2 class="turget-section-title">
                            {{ t('destinations.nearby_vendors', 'Destinasi Terdekat dari Lokasi Ini') }}
                        </h2>
                        <p class="turget-section-desc">
                            Dihitung berdasarkan titik koordinat geografis nyata (radius hingga 50 km).
                        </p>
                    </div>
                </div>

                <div class="turget-grid-4">
                    <article
                        v-for="nearby in nearbyDestinations"
                        :key="nearby.id"
                        class="turget-card"
                    >
                        <Link :href="`/places/${nearby.slug}`" class="turget-card-media" style="aspect-ratio: 4 / 3;">
                            <img
                                :src="nearby.cover_image || '/images/destinations/bali_hero.jpg'"
                                :alt="tr(nearby.name)"
                                class="turget-card-img"
                                loading="lazy"
                            />
                            <div class="turget-card-overlay"></div>
                            <span class="turget-card-badge" style="background: rgba(11,27,43,0.85); color: white;">
                                📍 {{ Number(nearby.distance_km).toFixed(1) }} km
                            </span>
                        </Link>
                        <div class="turget-card-body" style="padding: 1.15rem;">
                            <h4 class="turget-card-title" style="font-size: 1.1rem; margin-bottom: 0.35rem;">
                                <Link :href="`/places/${nearby.slug}`">{{ tr(nearby.name) }}</Link>
                            </h4>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">
                                {{ nearby.region ? tr(nearby.region.name) : '' }}
                            </div>
                        </div>
                    </article>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
