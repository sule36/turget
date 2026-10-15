<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    package: Object,
    guide: Object,
});

const { t, tr, locale } = useI18n();

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
        <Head :title="`${tr(package.title)} — Paket Wisata oleh ${guide.display_name}`" />

        <!-- Breadcrumbs & Header -->
        <section style="background: var(--color-surface-subtle); padding: 2.5rem 0 2rem; border-bottom: 1px solid var(--color-border-subtle);">
            <div class="turget-container">
                <!-- Breadcrumbs -->
                <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.82rem; color: var(--color-text-muted); margin-bottom: 1rem;">
                    <Link href="/" style="color: inherit; text-decoration: none;">Beranda</Link>
                    <span>/</span>
                    <Link href="/guides" style="color: inherit; text-decoration: none;">Tour Guide</Link>
                    <span>/</span>
                    <Link :href="`/guides/${guide.slug}`" style="color: inherit; text-decoration: none;">{{ guide.display_name }}</Link>
                    <span>/</span>
                    <span style="color: var(--color-navy-950); font-weight: 600;">{{ tr(package.title) }}</span>
                </div>

                <div style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; margin-bottom: 0.75rem;">
                    <span class="turget-badge" style="margin: 0; background: var(--color-blue-50); color: var(--color-blue-600); text-transform: uppercase;">
                        🎒 {{ package.tour_type }}
                    </span>
                    <span class="turget-badge" style="margin: 0; background: var(--color-surface-subtle);">
                        ⏱ {{ package.duration_hours }} Jam ({{ package.duration_days }} Hari)
                    </span>
                    <span class="turget-badge" style="margin: 0; background: var(--color-surface-subtle);">
                        👥 {{ package.min_participants }} - {{ package.max_participants }} Peserta
                    </span>
                </div>

                <h1 style="font-family: var(--font-serif); font-size: 2.4rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                    {{ tr(package.title) }}
                </h1>
                <p v-if="package.tagline" style="font-size: 1.05rem; color: var(--color-text-muted); max-width: 800px;">
                    {{ tr(package.tagline) }}
                </p>
            </div>
        </section>

        <!-- Main Content -->
        <section class="turget-section" style="background: white; padding: 3rem 0 6rem;">
            <div class="turget-container">
                <div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 3rem; align-items: start;">
                    <!-- Left: Itinerary & Inclusions -->
                    <div style="display: flex; flex-direction: column; gap: 2.5rem;">
                        <!-- Cover Image -->
                        <div style="height: 380px; border-radius: var(--radius-xl); overflow: hidden; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-sm);">
                            <img
                                :src="package.cover_image || '/images/destinations/bali_hero.jpg'"
                                :alt="tr(package.title)"
                                style="width: 100%; height: 100%; object-fit: cover;"
                            />
                        </div>

                        <!-- Description Narrative -->
                        <div>
                            <h2 style="font-family: var(--font-serif); font-size: 1.5rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                                Gambaran Pengalaman Perjalanan
                            </h2>
                            <div style="font-size: 1rem; color: var(--color-navy-950); line-height: 1.7; white-space: pre-line;">
                                {{ tr(package.description) }}
                            </div>
                        </div>

                        <!-- Itinerary Timeline -->
                        <div v-if="package.itinerary && package.itinerary.length > 0">
                            <h2 style="font-family: var(--font-serif); font-size: 1.5rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                                Rencana Perjalanan (Itinerary)
                            </h2>

                            <div style="display: flex; flex-direction: column; gap: 1rem; position: relative; padding-left: 1.5rem; border-left: 2px solid var(--color-blue-100);">
                                <div
                                    v-for="(step, idx) in package.itinerary"
                                    :key="idx"
                                    style="position: relative;"
                                >
                                    <!-- Bullet indicator -->
                                    <div style="position: absolute; left: -1.95rem; top: 0.2rem; width: 14px; height: 14px; border-radius: 50%; background: var(--color-blue-600); border: 3px solid white; box-shadow: 0 0 0 2px var(--color-blue-100);" />

                                    <div style="background: var(--color-surface-subtle); padding: 1rem 1.25rem; border-radius: var(--radius-md);">
                                        <div style="display: flex; justify-content: space-between; align-items: baseline; margin-bottom: 0.25rem;">
                                            <h4 style="font-weight: 700; color: var(--color-navy-950); font-size: 0.98rem;">
                                                {{ step.title || step.time }}
                                            </h4>
                                            <span v-if="step.time" style="font-size: 0.8rem; color: var(--color-blue-600); font-weight: 600;">
                                                {{ step.time }}
                                            </span>
                                        </div>
                                        <p style="font-size: 0.88rem; color: var(--color-text-muted); line-height: 1.4; margin: 0;">
                                            {{ step.description || step.activity }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Inclusions & Exclusions -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                            <!-- Inclusions -->
                            <div style="background: #F0FDF4; border: 1px solid rgba(16,185,129,0.3); border-radius: var(--radius-lg); padding: 1.5rem;">
                                <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-emerald-700); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>✓</span> Sudah Termasuk (Inklusi)
                                </h3>
                                <ul v-if="package.inclusions" style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.88rem; color: var(--color-navy-950);">
                                    <li v-for="(inc, iIdx) in (locale === 'en' ? package.inclusions.en || package.inclusions.id : package.inclusions.id)" :key="iIdx">
                                        {{ inc }}
                                    </li>
                                </ul>
                                <p v-else style="font-size: 0.85rem; color: var(--color-text-muted);">
                                    Jasa pemandu wisata berlisensi resmi.
                                </p>
                            </div>

                            <!-- Exclusions -->
                            <div style="background: #FEF2F2; border: 1px solid rgba(220,38,38,0.3); border-radius: var(--radius-lg); padding: 1.5rem;">
                                <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-coral-700); margin-bottom: 0.75rem; display: flex; align-items: center; gap: 0.4rem;">
                                    <span>✕</span> Belum Termasuk (Eksklusi)
                                </h3>
                                <ul v-if="package.exclusions" style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.88rem; color: var(--color-navy-950);">
                                    <li v-for="(exc, eIdx) in (locale === 'en' ? package.exclusions.en || package.exclusions.id : package.exclusions.id)" :key="eIdx">
                                        {{ exc }}
                                    </li>
                                </ul>
                                <p v-else style="font-size: 0.85rem; color: var(--color-text-muted);">
                                    Pengeluaran pribadi, tiket penerbangan, asuransi tambahan.
                                </p>
                            </div>
                        </div>

                        <!-- Logistics: Meeting Point -->
                        <div v-if="package.meeting_point" class="turget-admin-card" style="box-shadow: var(--shadow-sm);">
                            <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                                📍 Titik Kumpul (Meeting Point)
                            </h3>
                            <p style="font-size: 0.95rem; color: var(--color-navy-950);">
                                {{ package.meeting_point }}
                            </p>
                        </div>
                    </div>

                    <!-- Right Sticky Booking Card -->
                    <div style="position: sticky; top: 5.5rem; display: flex; flex-direction: column; gap: 1.5rem;">
                        <!-- Pricing Box -->
                        <div class="turget-admin-card" style="box-shadow: var(--shadow-md); border-color: rgba(37,99,235,0.2);">
                            <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                                Harga Paket Wisata
                            </div>
                            <div style="font-size: 2rem; font-weight: 800; color: var(--color-navy-950); margin: 0.25rem 0 1rem;">
                                {{ formatPrice(package.base_price) }}
                                <span style="font-size: 0.85rem; font-weight: 400; color: var(--color-text-muted);">
                                    / {{ package.price_type === 'per_person' ? 'orang' : (package.price_type === 'per_group' ? 'grup' : 'hari') }}
                                </span>
                            </div>

                            <!-- Guide Snapshot inside card -->
                            <div style="display: flex; align-items: center; gap: 0.75rem; padding: 0.85rem; background: var(--color-surface-subtle); border-radius: var(--radius-md); margin-bottom: 1.25rem;">
                                <img
                                    :src="guide.avatar_url || '/images/destinations/bali_hero.jpg'"
                                    :alt="guide.display_name"
                                    style="width: 44px; height: 44px; border-radius: 50%; object-fit: cover;"
                                />
                                <div style="flex: 1;">
                                    <div style="font-weight: 700; font-size: 0.9rem; color: var(--color-navy-950);">
                                        {{ guide.display_name }}
                                    </div>
                                    <div style="font-size: 0.75rem; color: var(--color-blue-600); font-weight: 600;">
                                        🛡️ Pemandu Terverifikasi
                                    </div>
                                </div>
                            </div>

                            <Link
                                :href="`/guides/${guide.slug}`"
                                class="turget-btn turget-btn-primary"
                                style="width: 100%; justify-content: center; font-size: 1rem; padding: 0.85rem;"
                            >
                                Pesan / Konsultasi Paket Ini
                            </Link>
                            <div style="text-align: center; margin-top: 0.75rem; font-size: 0.75rem; color: var(--color-text-muted);">
                                🔒 Pembayaran aman dilindungi Rekening Escrow Turget
                            </div>
                        </div>

                        <!-- Trust Card -->
                        <div class="turget-admin-card" style="box-shadow: var(--shadow-xs);">
                            <h4 style="font-size: 0.92rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                                Keamanan & Kebijakan
                            </h4>
                            <ul style="font-size: 0.82rem; color: var(--color-text-muted); display: flex; flex-direction: column; gap: 0.4rem;">
                                <li>• Konfirmasi instan atau maksimal 12 jam</li>
                                <li>• Pembatalan fleksibel sesuai syarat & ketentuan</li>
                                <li>• Bimbingan langsung pemandu resmi lokal</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </PublicLayout>
</template>
