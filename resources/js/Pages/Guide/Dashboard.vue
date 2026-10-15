<script setup>
import { computed } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    guide: Object,
    stats: Object,
    packages: Array,
    verifications: Array,
});

const { t, tr } = useI18n();

const isVerified = computed(() => props.guide.verification_status === 'verified' && props.guide.badge_verified);
</script>

<template>
    <PublicLayout>
        <Head :title="`Portal Tour Guide: ${guide.display_name} — Turget.id`" />

        <div style="background: var(--color-surface-subtle); padding: 2.5rem 0 5rem;">
            <div class="turget-container">
                <!-- Top Welcome Bar -->
                <div style="display: flex; flex-wrap: wrap; justify-content: space-between; align-items: flex-end; gap: 1rem; margin-bottom: 2rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.65rem; margin-bottom: 0.35rem;">
                            <span class="turget-badge" style="margin: 0; background: var(--color-blue-50); color: var(--color-blue-600);">
                                🧭 Portal Mitra Pemandu
                            </span>
                            <span
                                class="turget-status-badge"
                                :class="guide.badge_verified ? 'turget-status-published' : 'turget-status-in_review'"
                            >
                                ● {{ guide.badge_verified ? 'Terverifikasi Resmi' : 'Status: ' + guide.verification_status }}
                            </span>
                        </div>
                        <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950);">
                            Halo, {{ guide.display_name }}!
                        </h1>
                        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                            Kelola profil kreator, paket perjalanan, ketersediaan jadwal, dan pantau status transaksi Anda.
                        </p>
                    </div>

                    <div style="display: flex; gap: 0.75rem; align-items: center; flex-wrap: wrap;">
                        <Link href="/guide/inquiries" class="turget-btn turget-btn-secondary" style="border-color: var(--color-blue-600); color: var(--color-blue-600); font-weight: 700;">
                            🎯 Peluang Bidding Trip
                        </Link>
                        <Link href="/payouts" class="turget-btn turget-btn-secondary" style="font-weight: 700;">
                            💰 Dompet Payout
                        </Link>
                        <Link :href="`/guides/${guide.slug}`" class="turget-btn turget-btn-secondary">
                            Lihat Profil ↗
                        </Link>
                        <Link href="/guide/packages/create" class="turget-btn turget-btn-primary">
                            + Buat Paket Wisata
                        </Link>
                    </div>
                </div>

                <!-- Verification Status Notification Banner -->
                <div v-if="!isVerified" style="background: white; border-left: 4px solid #F59E0B; border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; margin-bottom: 2rem; box-shadow: var(--shadow-xs); display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem;">
                    <div>
                        <div style="font-weight: 700; color: #B45309; font-size: 1rem; margin-bottom: 0.25rem;">
                            {{ guide.verification_status === 'submitted' ? '⏳ Berkas Pendaftaran Sedang Diverifikasi' : (guide.verification_status === 'under_review' ? '🔍 Berkas Sedang Ditinjau Tim Kepatuhan' : (guide.verification_status === 'need_revision' ? '⚠️ Perlu Revisi Dokumen' : 'Status: ' + guide.verification_status)) }}
                        </div>
                        <p style="font-size: 0.88rem; color: var(--color-navy-950); margin: 0; line-height: 1.5;">
                            {{ guide.verification_note || 'Tim kurasi Turget.id sedang memeriksa dokumen identitas dan lisensi Anda. Proses verifikasi biasanya memakan waktu maksimal 1x24 jam kerja.' }}
                        </p>
                    </div>
                    <Link href="/guide/profile" class="turget-btn turget-btn-secondary turget-btn-sm" style="white-space: nowrap;">
                        Periksa Berkas
                    </Link>
                </div>

                <div v-else style="background: #F0FDF4; border: 1px solid rgba(16,185,129,0.3); border-radius: var(--radius-lg); padding: 1rem 1.5rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span style="font-size: 1.5rem; color: var(--color-emerald-600);">🛡️</span>
                        <div>
                            <div style="font-weight: 700; color: var(--color-emerald-800); font-size: 0.95rem;">
                                Akun Anda Telah Terverifikasi Penuh
                            </div>
                            <div style="font-size: 0.82rem; color: var(--color-emerald-700);">
                                Profil Anda menampilkan lencana "Terverifikasi" dan siap menerima pesanan langsung dari wisatawan.
                            </div>
                        </div>
                    </div>
                    <span class="turget-badge" style="background: white; color: var(--color-emerald-700); border-color: rgba(16,185,129,0.3);">
                        Verified Member
                    </span>
                </div>

                <!-- Stats Cards Grid -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 2.5rem;">
                    <div class="turget-admin-card">
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                            Total Paket Wisata
                        </div>
                        <div style="font-size: 2rem; font-weight: 800; color: var(--color-navy-950); margin: 0.25rem 0;">
                            {{ stats.total_packages }}
                        </div>
                        <div style="font-size: 0.82rem; color: var(--color-emerald-600); font-weight: 600;">
                            {{ stats.active_packages }} Paket Tayang Publik
                        </div>
                    </div>

                    <div class="turget-admin-card">
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                            Perjalanan Selesai
                        </div>
                        <div style="font-size: 2rem; font-weight: 800; color: var(--color-navy-950); margin: 0.25rem 0;">
                            {{ stats.trips_completed }}
                        </div>
                        <div style="font-size: 0.82rem; color: var(--color-text-muted);">
                            Wisatawan terpandu
                        </div>
                    </div>

                    <div class="turget-admin-card">
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                            Rating & Reputasi
                        </div>
                        <div style="font-size: 2rem; font-weight: 800; color: var(--color-navy-950); margin: 0.25rem 0;">
                            ⭐ {{ stats.rating_avg || '5.0' }}
                        </div>
                        <div style="font-size: 0.82rem; color: var(--color-text-muted);">
                            Dari {{ stats.reviews_count }} ulasan
                        </div>
                    </div>

                    <div class="turget-admin-card">
                        <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                            Tarif Jasa Pribadi
                        </div>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--color-blue-600); margin: 0.5rem 0 0.25rem;">
                            {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(guide.price_per_day) }}
                        </div>
                        <div style="font-size: 0.82rem; color: var(--color-text-muted);">
                            per hari
                        </div>
                    </div>
                </div>

                <!-- Main Section: Package Management & Verification Documents -->
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
                    <!-- Packages Table Card -->
                    <div class="turget-admin-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                            <h2 style="font-family: var(--font-serif); font-size: 1.35rem; font-weight: 700; color: var(--color-navy-950);">
                                Paket Wisata Saya
                            </h2>
                            <Link href="/guide/packages" style="color: var(--color-blue-600); font-size: 0.88rem; font-weight: 600; text-decoration: none;">
                                Kelola Semua Paket →
                            </Link>
                        </div>

                        <div v-if="packages.length > 0" style="display: flex; flex-direction: column; gap: 1rem;">
                            <div
                                v-for="pkg in packages"
                                :key="pkg.id"
                                style="border: 1px solid var(--color-border-subtle); border-radius: var(--radius-md); padding: 1rem; display: flex; justify-content: space-between; align-items: center; background: white;"
                            >
                                <div style="display: flex; align-items: center; gap: 1rem;">
                                    <img
                                        :src="pkg.cover_image || '/images/destinations/bali_hero.jpg'"
                                        :alt="tr(pkg.title)"
                                        style="width: 54px; height: 54px; border-radius: var(--radius-sm); object-fit: cover;"
                                    />
                                    <div>
                                        <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.95rem;">
                                            {{ tr(pkg.title) }}
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--color-text-muted);">
                                            ⏱ {{ pkg.duration_hours }} Jam • {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(pkg.base_price) }}
                                        </div>
                                    </div>
                                </div>

                                <div style="display: flex; align-items: center; gap: 0.75rem;">
                                    <span class="turget-status-badge" :class="pkg.status === 'published' ? 'turget-status-published' : 'turget-status-draft'">
                                        ● {{ pkg.status }}
                                    </span>
                                    <Link :href="`/guide/packages/${pkg.id}/edit`" class="turget-btn turget-btn-secondary turget-btn-sm">
                                        Edit
                                    </Link>
                                </div>
                            </div>
                        </div>
                        <div v-else style="text-align: center; padding: 3rem 1rem; color: var(--color-text-muted);">
                            Belum ada paket wisata. Buat paket pertama Anda untuk mulai menerima pemesanan!
                        </div>
                    </div>

                    <!-- KYC Documents Status Card -->
                    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <div class="turget-admin-card">
                            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                                Status Dokumen Verifikasi (KYC)
                            </h3>

                            <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                <div
                                    v-for="v in verifications"
                                    :key="v.id"
                                    style="padding: 0.75rem; background: var(--color-surface-subtle); border-radius: var(--radius-md); font-size: 0.85rem;"
                                >
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                        <strong style="text-transform: uppercase;">{{ v.document_type.replace('_', ' ') }}</strong>
                                        <span class="turget-status-badge" :class="v.status === 'verified' ? 'turget-status-published' : 'turget-status-in_review'" style="font-size: 0.7rem; padding: 0.15rem 0.45rem;">
                                            {{ v.status }}
                                        </span>
                                    </div>
                                    <div style="color: var(--color-text-muted); font-size: 0.8rem;">
                                        No: {{ v.document_number || 'Tersimpan aman' }}
                                    </div>
                                </div>

                                <div v-if="verifications.length === 0" style="font-size: 0.85rem; color: var(--color-text-muted);">
                                    Belum ada berkas dokumen yang terunggah.
                                </div>
                            </div>
                        </div>

                        <!-- Quick Profile Settings Card -->
                        <div class="turget-admin-card">
                            <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                                Pengaturan Profil Kreator
                            </h3>
                            <p style="font-size: 0.85rem; color: var(--color-text-muted); line-height: 1.4; margin-bottom: 1rem;">
                                Perbarui cerita biografi, foto sampul, dan informasi rekening pencairan dana.
                            </p>
                            <Link href="/guide/profile" class="turget-btn turget-btn-secondary" style="width: 100%; justify-content: center;">
                                Edit Profil & Rekening →
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
