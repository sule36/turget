<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    guide: Object,
    packages: Array,
    destinations: Array,
});

const { t, tr, locale } = useI18n();

const activeTab = ref('overview'); // 'overview' | 'packages' | 'destinations' | 'policy'

function formatPrice(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
}

function startChatWithGuide() {
    router.post('/chat/start', {
        target_user_id: props.guide.user_id,
    });
}
</script>

<template>
    <PublicLayout>
        <Head :title="`${guide.display_name} — Tour Guide Terverifikasi ${tr(guide.region?.name)}`" />

        <!-- Creator Profile Hero Banner -->
        <section style="position: relative; background: var(--color-navy-950); color: white;">
            <!-- Cover Photo with Dark Overlay -->
            <div class="turget-detail-hero-cover">
                <img
                    :src="guide.cover_image_url || '/images/destinations/bali_hero.jpg'"
                    :alt="guide.display_name"
                    style="width: 100%; height: 100%; object-fit: cover; opacity: 0.55;"
                />
                <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(11,27,43,0.95) 0%, rgba(11,27,43,0.3) 100%);" />
            </div>

            <!-- Profile Info Container -->
            <div class="turget-container turget-detail-hero-info">
                <div class="turget-detail-hero-flex">
                    <div class="turget-detail-avatar-wrap">
                        <!-- Avatar with Verified Ring -->
                        <div style="position: relative;">
                            <img
                                :src="guide.avatar_url || '/images/destinations/bali_hero.jpg'"
                                :alt="guide.display_name"
                                class="turget-detail-avatar-img"
                            />
                            <span
                                v-if="guide.badge_verified"
                                title="Terverifikasi oleh Turget.id"
                                style="position: absolute; bottom: 6px; right: 6px; background: var(--color-blue-600); color: white; width: 32px; height: 32px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1rem; border: 3px solid white;"
                            >
                                ✓
                            </span>
                        </div>

                        <!-- Name & Key Bio -->
                        <div>
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.35rem; flex-wrap: wrap;">
                                <h1 class="turget-detail-title" style="color: white;">
                                    {{ guide.display_name }}
                                </h1>
                                <span
                                    v-if="guide.badge_verified"
                                    class="turget-badge"
                                    style="margin: 0; background: rgba(37,99,235,0.25); color: var(--color-blue-400); border-color: rgba(37,99,235,0.4);"
                                >
                                    🛡️ Terverifikasi
                                </span>
                            </div>

                            <p style="color: var(--color-sand-200); font-size: 1.05rem; margin-bottom: 0.5rem; max-width: 600px;">
                                {{ tr(guide.tagline) || 'Pemandu wisata profesional berbasis di ' + tr(guide.region?.name) }}
                            </p>

                            <div style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.75rem; font-size: 0.88rem; color: var(--color-text-subtle);">
                                <span>📍 {{ tr(guide.region?.name) }}, {{ tr(guide.country?.name) }}</span>
                                <span>•</span>
                                <span>⭐ {{ guide.rating_avg || '5.0' }} ({{ guide.reviews_count }} ulasan)</span>
                                <span>•</span>
                                <span>🧭 {{ guide.trips_completed_count }} perjalanan selesai</span>
                                <span>•</span>
                                <span>⚡ Balas &lt; {{ guide.response_time_minutes }} mnt</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Pricing & Direct Booking Card -->
                    <div class="turget-detail-pricing-box">
                        <div style="font-size: 0.82rem; color: var(--color-sand-200); text-transform: uppercase; letter-spacing: 0.05em;">
                            Tarif Jasa Pribadi
                        </div>
                        <div style="font-size: 1.75rem; font-weight: 800; color: white; margin: 0.25rem 0 1rem;">
                            {{ formatPrice(guide.price_per_day) }}
                            <span style="font-size: 0.85rem; font-weight: 400; color: var(--color-sand-200);">/ hari</span>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                            <button
                                type="button"
                                @click="startChatWithGuide"
                                class="turget-btn turget-btn-primary"
                                style="justify-content: center; width: 100%; font-weight: 700;"
                            >
                                💬 {{ locale === 'id' ? 'Chat Langsung Pemandu' : 'Chat with Guide' }}
                            </button>
                            <Link
                                :href="`/trips/plan?region_id=${guide.region_id}`"
                                class="turget-btn"
                                style="justify-content: center; width: 100%; background: rgba(255,255,255,0.12); color: white; border: 1px solid rgba(255,255,255,0.25); font-weight: 600;"
                            >
                                ✨ {{ locale === 'id' ? 'Rencanakan Trip Kustom' : 'Plan Custom Trip' }}
                            </Link>
                            <a
                                href="#packages-section"
                                class="turget-btn"
                                style="justify-content: center; width: 100%; background: transparent; color: var(--color-sand-200); border: 1px solid rgba(255,255,255,0.15);"
                            >
                                🎒 {{ locale === 'id' ? 'Lihat Paket Wisata ↓' : 'Browse Packages ↓' }}
                            </a>
                        </div>
                        <div style="font-size: 0.73rem; color: var(--color-sand-300); text-align: center; margin-top: 0.75rem; line-height: 1.35;">
                            🛡️ {{ locale === 'id' ? 'Chat aman terlindungi standar Tokopedia/Shopee. Kontak luar disamarkan otomatis.' : 'Secure chat with automatic contact masking.' }}
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Navigation Tabs -->
        <section class="turget-tabs-strip">
            <div class="turget-container">
                <div class="turget-tabs-nav" style="margin-bottom: 0;">
                    <button
                        type="button"
                        class="turget-tab-btn"
                        :class="{ 'is-active': activeTab === 'overview' }"
                        @click="activeTab = 'overview'"
                    >
                        Tentang & Portofolio
                    </button>
                    <button
                        type="button"
                        class="turget-tab-btn"
                        :class="{ 'is-active': activeTab === 'packages' }"
                        @click="activeTab = 'packages'"
                    >
                        Paket Wisata ({{ packages.length }})
                    </button>
                    <button
                        type="button"
                        class="turget-tab-btn"
                        :class="{ 'is-active': activeTab === 'destinations' }"
                        @click="activeTab = 'destinations'"
                    >
                        Destinasi Dikuasai ({{ destinations.length }})
                    </button>
                </div>
            </div>
        </section>

        <!-- Main Content Area -->
        <section class="turget-section" style="background: var(--color-surface-subtle); padding: 2.5rem 0 5rem;">
            <div class="turget-container">
                <div class="turget-detail-layout">
                    <!-- Left Column -->
                    <div style="display: flex; flex-direction: column; gap: 2rem;">
                        <!-- About & Narrative -->
                        <div class="turget-admin-card" style="box-shadow: var(--shadow-sm);">
                            <h2 style="font-family: var(--font-heading); font-size: 1.4rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                                Tentang Saya & Gaya Perjalanan
                            </h2>
                            <div style="font-size: 0.95rem; color: var(--color-navy-950); line-height: 1.7; white-space: pre-line;">
                                {{ tr(guide.bio) || 'Selamat datang di halaman resmi saya di Turget.id. Saya adalah tour guide berlisensi yang berdedikasi memperkenalkan pesona autentik dan kearifan lokal.' }}
                            </div>

                            <!-- Highlights & Stats Badges -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); gap: 1rem; margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-subtle);">
                                <div>
                                    <div style="font-size: 0.78rem; color: var(--color-text-muted); text-transform: uppercase;">Pengalaman Kerja</div>
                                    <div style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950);">{{ guide.experience_years }} Tahun</div>
                                </div>
                                <div>
                                    <div style="font-size: 0.78rem; color: var(--color-text-muted); text-transform: uppercase;">Bahasa Dikuasai</div>
                                    <div style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950);">
                                        {{ guide.languages?.map(l => l.toUpperCase()).join(', ') }}
                                    </div>
                                </div>
                                <div>
                                    <div style="font-size: 0.78rem; color: var(--color-text-muted); text-transform: uppercase;">Tingkat Respons</div>
                                    <div style="font-size: 1.15rem; font-weight: 700; color: var(--color-emerald-600);">{{ guide.response_rate_percent }}%</div>
                                </div>
                            </div>
                        </div>

                        <!-- Featured Packages Section -->
                        <div id="packages-section" class="turget-admin-card" style="box-shadow: var(--shadow-sm);">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem; flex-wrap: wrap; gap: 0.5rem;">
                                <h2 style="font-family: var(--font-heading); font-size: 1.4rem; font-weight: 700; color: var(--color-navy-950);">
                                    Paket Wisata Pilihan
                                </h2>
                                <span class="turget-badge" style="margin: 0; background: var(--color-blue-50); color: var(--color-blue-600);">
                                    {{ packages.length }} Paket Tersedia
                                </span>
                            </div>

                            <div v-if="packages.length > 0" style="display: flex; flex-direction: column; gap: 1.25rem;">
                                <div
                                    v-for="pkg in packages"
                                    :key="pkg.id"
                                    class="turget-package-card-row"
                                >
                                    <img
                                        :src="pkg.cover_image || '/images/destinations/bali_hero.jpg'"
                                        :alt="tr(pkg.title)"
                                        class="turget-package-card-thumb"
                                    />
                                    <div class="turget-package-card-body">
                                        <div>
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.35rem; gap: 0.5rem; flex-wrap: wrap;">
                                                <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-navy-950);">
                                                    {{ tr(pkg.title) }}
                                                </h3>
                                                <span class="turget-badge" style="margin: 0; font-size: 0.7rem; background: var(--color-surface-subtle);">
                                                    ⏱ {{ pkg.duration_hours }} Jam ({{ pkg.duration_days }} Hari)
                                                </span>
                                            </div>
                                            <p style="font-size: 0.85rem; color: var(--color-text-muted); line-height: 1.4; margin-bottom: 0.75rem;">
                                                {{ tr(pkg.tagline) || tr(pkg.description)?.substring(0, 120) + '...' }}
                                            </p>
                                        </div>

                                        <div style="display: flex; justify-content: space-between; align-items: flex-end; padding-top: 0.75rem; border-top: 1px solid var(--color-border-subtle); flex-wrap: wrap; gap: 0.5rem;">
                                            <div>
                                                <div style="font-size: 0.75rem; color: var(--color-text-muted);">Mulai dari</div>
                                                <div style="font-size: 1.15rem; font-weight: 800; color: var(--color-blue-600);">
                                                    {{ formatPrice(pkg.base_price) }}
                                                    <span style="font-size: 0.75rem; font-weight: 400; color: var(--color-text-muted);">
                                                        / {{ pkg.price_type === 'per_person' ? 'orang' : (pkg.price_type === 'per_group' ? 'grup' : 'hari') }}
                                                    </span>
                                                </div>
                                            </div>

                                            <Link :href="`/packages/${pkg.slug}`" class="turget-btn turget-btn-primary turget-btn-sm">
                                                Detail Paket →
                                            </Link>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div v-else style="text-align: center; padding: 2.5rem 1rem; color: var(--color-text-muted);">
                                Belum ada paket perjalanan yang dipublikasikan saat ini.
                            </div>
                        </div>

                        <!-- Destinations of Expertise -->
                        <div class="turget-admin-card" style="box-shadow: var(--shadow-sm);">
                            <h2 style="font-family: var(--font-heading); font-size: 1.4rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                                Destinasi yang Dikuasai
                            </h2>

                            <div v-if="destinations.length > 0" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 0.75rem;">
                                <Link
                                    v-for="dest in destinations"
                                    :key="dest.id"
                                    :href="`/places/${dest.slug}`"
                                    style="border: 1px solid var(--color-border-subtle); border-radius: var(--radius-md); padding: 0.75rem; display: flex; align-items: center; gap: 0.75rem; text-decoration: none; color: inherit; background: white;"
                                >
                                    <img
                                        :src="dest.cover_image || '/images/destinations/bali_hero.jpg'"
                                        :alt="tr(dest.name)"
                                        style="width: 44px; height: 44px; border-radius: var(--radius-sm); object-fit: cover;"
                                    />
                                    <div>
                                        <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.85rem;">
                                            {{ tr(dest.name) }}
                                        </div>
                                        <div style="font-size: 0.75rem; color: var(--color-blue-600);">
                                            Lihat Destinasi →
                                        </div>
                                    </div>
                                </Link>
                            </div>
                            <div v-else style="color: var(--color-text-muted); font-size: 0.9rem;">
                                Melayani destinasi fleksibel di seluruh wilayah {{ tr(guide.region?.name) }}.
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Verification & Trust Sidebar -->
                    <div class="turget-sticky-booking-sidebar">
                        <!-- Verified Trust Card -->
                        <div class="turget-admin-card" style="border-left: 4px solid var(--color-blue-600); box-shadow: var(--shadow-sm);">
                            <div style="display: flex; align-items: center; gap: 0.6rem; margin-bottom: 0.75rem;">
                                <span style="font-size: 1.25rem;">🛡️</span>
                                <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-navy-950);">
                                    Jaminan Standar Turget.id
                                </h3>
                            </div>
                            <ul style="display: flex; flex-direction: column; gap: 0.6rem; font-size: 0.85rem; color: var(--color-navy-950); line-height: 1.4;">
                                <li>✓ Identitas KTP/Paspor telah divalidasi resmi.</li>
                                <li>✓ Memiliki lisensi pemandu wisata lokal atau sertifikasi keahlian.</li>
                                <li>✓ Transaksi dilindungi rekening bersama (Escrow).</li>
                                <li>✓ Pemandu tidak menerima pembayaran tunai di luar sistem.</li>
                            </ul>
                        </div>

                        <!-- Specializations List -->
                        <div class="turget-admin-card" style="box-shadow: var(--shadow-sm);">
                            <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                                Spesialisasi Wisata
                            </h3>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.4rem;">
                                <span
                                    v-for="(spec, idx) in guide.specializations"
                                    :key="idx"
                                    class="turget-badge"
                                    style="margin: 0; background: var(--color-blue-50); color: var(--color-blue-600); font-size: 0.78rem;"
                                >
                                    {{ spec }}
                                </span>
                            </div>
                        </div>

                        <!-- Service Area -->
                        <div class="turget-admin-card" style="box-shadow: var(--shadow-sm);">
                            <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                                Wilayah Operasional
                            </h3>
                            <p style="font-size: 0.88rem; color: var(--color-navy-950); line-height: 1.5;">
                                Berdomisili di <strong>{{ tr(guide.region?.name) }}</strong>, siap memandu perjalanan di seluruh kawasan terkait.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
