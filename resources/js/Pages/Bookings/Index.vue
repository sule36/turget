<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    bookings: Object,
});

const { t, locale } = useI18n();
</script>

<template>
    <PublicLayout>
        <Head :title="locale === 'id' ? 'Pesanan Saya — Turget.id' : 'My Bookings — Turget.id'" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container" style="max-width: 960px;">
                <!-- Header -->
                <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem; margin-bottom: 2.5rem;">
                    <div>
                        <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); margin-bottom: 0.5rem;">
                            🛡️ {{ locale === 'id' ? 'Escrow Protected Bookings' : 'Escrow Protected Bookings' }}
                        </span>
                        <h1 style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: var(--color-navy-950);">
                            {{ locale === 'id' ? 'Riwayat Pesanan & E-Voucher' : 'My Bookings & Vouchers' }}
                        </h1>
                        <p style="color: var(--color-text-muted); font-size: 1rem;">
                            {{ locale === 'id'
                                ? 'Kelola status voucher, verifikasi check-in, dan pelepasan dana escrow untuk pemandu wisata Anda.'
                                : 'Manage your vouchers, check-in QR codes, and escrow releases.' }}
                        </p>
                    </div>

                    <Link href="/destinations" class="turget-btn turget-btn-primary">
                        + {{ locale === 'id' ? 'Jelajah Wisata Baru' : 'Explore More' }}
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-if="!bookings.data?.length" style="background: white; border-radius: var(--radius-xl); padding: 5rem 2rem; text-align: center; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-sm);">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">🎒</div>
                    <h3 style="font-size: 1.35rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        {{ locale === 'id' ? 'Belum Ada Riwayat Pemesanan' : 'No Bookings Yet' }}
                    </h3>
                    <p style="color: var(--color-text-muted); max-width: 480px; margin: 0 auto 2rem; font-size: 0.95rem;">
                        {{ locale === 'id'
                            ? 'Mulai petualangan Anda! Pilih paket wisata dari tour guide lokal terverifikasi atau sewa kendaraan dan penginapan favorit.'
                            : 'Start your journey. Browse curated tour packages or local vendor services with 100% escrow protection.' }}
                    </p>
                    <Link href="/destinations" class="turget-btn turget-btn-primary">
                        {{ locale === 'id' ? 'Cari Destinasi Sekarang' : 'Browse Destinations' }}
                    </Link>
                </div>

                <!-- Bookings List -->
                <div v-else style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div
                        v-for="booking in bookings.data"
                        :key="booking.id"
                        style="background: white; border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;"
                    >
                        <div style="max-width: 620px;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); font-weight: 700; font-size: 0.75rem; margin: 0;">
                                    {{ booking.booking_code }}
                                </span>
                                <span
                                    class="turget-status-badge"
                                    :class="{
                                        'turget-status-in_review': booking.status === 'pending_payment',
                                        'turget-status-verified': booking.status === 'paid_escrow' || booking.status === 'confirmed',
                                        'turget-status-published': booking.status === 'in_progress',
                                        'turget-status-draft': booking.status === 'completed',
                                        'turget-status-rejected': booking.status === 'cancelled' || booking.status === 'refunded'
                                    }"
                                >
                                    ● {{ booking.status?.toUpperCase() }}
                                </span>
                            </div>

                            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.35rem;">
                                {{ booking.tour_package?.title || booking.vendor_product?.title || 'Bespoke Experience' }}
                            </h3>

                            <div style="display: flex; flex-wrap: wrap; gap: 1.25rem; font-size: 0.88rem; color: var(--color-text-muted);">
                                <span>📅 Tanggal: {{ booking.start_date }}</span>
                                <span>👥 {{ booking.participants_count }} Peserta</span>
                                <span>Mitra: <strong>{{ booking.guide?.user?.name || booking.vendor?.business_name || 'Mitra Turget' }}</strong></span>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 1.5rem;">
                            <div style="text-align: right;">
                                <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700;">
                                    Total Biaya
                                </div>
                                <div style="font-size: 1.35rem; font-weight: 800; color: var(--color-blue-600);">
                                    Rp {{ Number(booking.total_amount).toLocaleString('id-ID') }}
                                </div>
                            </div>

                            <div style="display: flex; gap: 0.5rem;">
                                <Link
                                    v-if="booking.status === 'pending_payment'"
                                    :href="`/bookings/${booking.id}/payment`"
                                    class="turget-btn turget-btn-primary"
                                    style="background: var(--color-amber-500);"
                                >
                                    Bayar Sekarang ➔
                                </Link>

                                <Link :href="`/bookings/${booking.id}`" class="turget-btn turget-btn-secondary">
                                    Lihat Voucher ➔
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
