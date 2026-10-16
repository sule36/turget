<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    booking: Object,
});

const { t, locale } = useI18n();

function printVoucher() {
    window.print();
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
    <div>
        <Head :title="`Official E-Voucher ${booking.booking_code} — Turget.id`" />

        <!-- Print-hidden Control Bar -->
        <div class="turget-voucher-toolbar no-print">
            <div class="turget-container" style="max-width: 860px; display: flex; justify-content: space-between; align-items: center;">
                <Link :href="`/bookings/${booking.id}`" class="turget-btn turget-btn-secondary" style="font-size: 0.88rem;">
                    ← {{ locale === 'id' ? 'Kembali ke Rincian Pesanan' : 'Back to Booking' }}
                </Link>
                <div style="display: flex; gap: 0.75rem;">
                    <button
                        type="button"
                        class="turget-btn turget-btn-primary"
                        @click="printVoucher"
                        style="font-weight: 700; padding: 0.5rem 1.25rem;"
                    >
                        🖨️ {{ locale === 'id' ? 'Cetak / Simpan PDF' : 'Print / Save PDF' }}
                    </button>
                </div>
            </div>
        </div>

        <!-- Voucher Sheet Container -->
        <div style="background: #F1F5F9; padding: 2.5rem 1rem; min-height: 100vh;">
            <div class="turget-voucher-card">
                <!-- Header with Brand & Official Stamp -->
                <div class="turget-voucher-header">
                    <div>
                        <div class="turget-brand" style="margin-bottom: 0.25rem;">
                            <span class="turget-brand-icon">T</span>
                            <span>turget<span class="turget-brand-dot">.id</span></span>
                        </div>
                        <div style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-text-subtle); font-weight: 700;">
                            Official Electronic Travel Voucher & Escrow Receipt
                        </div>
                    </div>

                    <div style="text-align: right;">
                        <div class="turget-voucher-code">
                            {{ booking.booking_code }}
                        </div>
                        <div class="turget-voucher-status">
                            ✓ ESCROW PAID & SECURED
                        </div>
                    </div>
                </div>

                <!-- Main Experience Title -->
                <div style="padding: 1.75rem 2.25rem; border-bottom: 1px solid var(--color-border-subtle); background: var(--color-surface-subtle);">
                    <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--color-blue-600); font-weight: 800; letter-spacing: 0.05em; margin-bottom: 0.35rem;">
                        {{ booking.booking_type?.toUpperCase() || 'TOUR PACKAGE' }}
                    </div>
                    <h1 style="font-size: 1.6rem; font-weight: 800; color: var(--color-navy-950); margin: 0; line-height: 1.3;">
                        {{ booking.tour_package?.title || booking.vendor_product?.title || booking.trip_proposal?.title || 'Perjalanan Wisata Terverifikasi' }}
                    </h1>
                    <div style="font-size: 0.9rem; color: var(--color-text-muted); margin-top: 0.5rem;">
                        📍 {{ booking.tour_package?.destination?.name || booking.guide?.region?.name || booking.vendor?.region?.name || 'Indonesia' }}
                    </div>
                </div>

                <!-- Two Column Details Grid -->
                <div class="turget-voucher-grid">
                    <!-- Left: Traveler & Schedule -->
                    <div>
                        <h4 class="turget-voucher-section-title">Informasi Wisatawan</h4>
                        <div class="turget-voucher-data-row">
                            <span class="turget-voucher-data-label">Nama Pemesan</span>
                            <span class="turget-voucher-data-val">{{ booking.traveler?.name }}</span>
                        </div>
                        <div class="turget-voucher-data-row">
                            <span class="turget-voucher-data-label">Email</span>
                            <span class="turget-voucher-data-val">{{ booking.traveler?.email }}</span>
                        </div>
                        <div class="turget-voucher-data-row">
                            <span class="turget-voucher-data-label">Jumlah Peserta</span>
                            <span class="turget-voucher-data-val">{{ booking.participants_count }} Orang</span>
                        </div>
                        <div class="turget-voucher-data-row">
                            <span class="turget-voucher-data-label">Jadwal Perjalanan</span>
                            <span class="turget-voucher-data-val">{{ booking.start_date }} s/d {{ booking.end_date }}</span>
                        </div>
                    </div>

                    <!-- Right: Guide / Vendor Partner -->
                    <div>
                        <h4 class="turget-voucher-section-title">Mitra Resmi Penyedia</h4>
                        <div class="turget-voucher-data-row">
                            <span class="turget-voucher-data-label">Nama Pemandu / Vendor</span>
                            <span class="turget-voucher-data-val">
                                {{ booking.guide?.display_name || booking.vendor?.business_name || 'Mitra Turget.id' }}
                            </span>
                        </div>
                        <div class="turget-voucher-data-row">
                            <span class="turget-voucher-data-label">Status Verifikasi</span>
                            <span class="turget-voucher-data-val" style="color: var(--color-emerald-600); font-weight: 700;">
                                🛡️ Terverifikasi & Berlisensi
                            </span>
                        </div>
                        <div class="turget-voucher-data-row">
                            <span class="turget-voucher-data-label">Wilayah Operasional</span>
                            <span class="turget-voucher-data-val">
                                {{ booking.guide?.region?.name || booking.vendor?.region?.name || 'Indonesia' }}
                            </span>
                        </div>
                        <div class="turget-voucher-data-row">
                            <span class="turget-voucher-data-label">Kanal Komunikasi</span>
                            <span class="turget-voucher-data-val" style="color: var(--color-blue-600);">
                                Chat Resmi Turget.id
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Payment & Escrow Guarantee Summary -->
                <div style="padding: 1.75rem 2.25rem; background: #FAFBFD; border-top: 1px dashed var(--color-border-subtle); border-bottom: 1px dashed var(--color-border-subtle);">
                    <h4 class="turget-voucher-section-title">Rincian Pembayaran Escrow</h4>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem;">
                        <span style="color: var(--color-text-muted);">Biaya Pokok Paket / Layanan</span>
                        <span style="font-weight: 600; color: var(--color-navy-950);">{{ formatPrice(booking.subtotal) }}</span>
                    </div>
                    <div v-if="Number(booking.platform_fee) > 0" style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem;">
                        <span style="color: var(--color-text-muted);">Biaya Proteksi Escrow & Garansi Layanan (10%)</span>
                        <span style="font-weight: 600; color: var(--color-navy-950);">{{ formatPrice(booking.platform_fee) }}</span>
                    </div>
                    <div v-if="Number(booking.discount) > 0" style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.9rem;">
                        <span style="color: var(--color-emerald-600);">Diskon Promo</span>
                        <span style="font-weight: 600; color: var(--color-emerald-600);">-{{ formatPrice(booking.discount) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-top: 1rem; padding-top: 0.85rem; border-top: 1px solid var(--color-border-subtle); font-size: 1.25rem;">
                        <strong style="color: var(--color-navy-950);">Total Dibayar (Lunas):</strong>
                        <strong style="color: var(--color-blue-600);">{{ formatPrice(booking.total_amount) }}</strong>
                    </div>
                </div>

                <!-- Footer Security & QR Verification -->
                <div style="padding: 1.75rem 2.25rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1.5rem;">
                    <div style="max-width: 500px;">
                        <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.88rem; display: flex; align-items: center; gap: 0.4rem;">
                            <span>🛡️</span> Garansi Rekening Bersama Escrow Turget.id
                        </div>
                        <p style="font-size: 0.78rem; color: var(--color-text-muted); line-height: 1.45; margin-top: 0.35rem;">
                            Dokumen ini adalah bukti reservasi elektronik resmi. Dana tersimpan aman di rekening bersama Turget.id dan baru diteruskan ke mitra setelah Anda mengonfirmasi kelancaran perjalanan. Harap tunjukkan voucher ini kepada pemandu saat penjemputan.
                        </p>
                    </div>

                    <!-- Barcode Representation -->
                    <div style="text-align: right;">
                        <div style="font-family: monospace; font-size: 1.5rem; letter-spacing: 0.15em; color: var(--color-navy-950); font-weight: 800;">
                            ||| | |||| | ||||| || |
                        </div>
                        <div style="font-size: 0.72rem; color: var(--color-text-subtle); margin-top: 0.2rem;">
                            AUTORISASI SISTEM TURGET.ID
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.turget-voucher-toolbar {
    background: white;
    border-bottom: 1px solid var(--color-border-subtle);
    padding: 0.85rem 1rem;
    position: sticky;
    top: 0;
    z-index: 50;
    box-shadow: 0 1px 3px rgba(0,0,0,0.05);
}

.turget-voucher-card {
    max-width: 860px;
    margin: 0 auto;
    background: white;
    border-radius: var(--radius-xl);
    box-shadow: 0 4px 25px rgba(0,0,0,0.07);
    border: 1px solid var(--color-border-subtle);
    overflow: hidden;
}

.turget-voucher-header {
    padding: 2rem 2.25rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid var(--color-border-subtle);
    background: white;
}

.turget-voucher-code {
    font-family: var(--font-sans);
    font-size: 1.4rem;
    font-weight: 800;
    color: var(--color-navy-950);
    letter-spacing: 0.05em;
}

.turget-voucher-status {
    font-size: 0.73rem;
    font-weight: 700;
    color: var(--color-emerald-600);
    letter-spacing: 0.05em;
    margin-top: 0.2rem;
}

.turget-voucher-grid {
    padding: 1.75rem 2.25rem;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
}

.turget-voucher-section-title {
    font-size: 0.95rem;
    font-weight: 700;
    color: var(--color-navy-950);
    margin-bottom: 1rem;
    border-bottom: 1px solid var(--color-border-subtle);
    padding-bottom: 0.4rem;
}

.turget-voucher-data-row {
    display: flex;
    justify-content: space-between;
    margin-bottom: 0.6rem;
    font-size: 0.85rem;
}

.turget-voucher-data-label {
    color: var(--color-text-subtle);
    font-weight: 500;
}

.turget-voucher-data-val {
    font-weight: 700;
    color: var(--color-navy-950);
    text-align: right;
}

@media print {
    .no-print {
        display: none !important;
    }
    body {
        background: white !important;
    }
    .turget-voucher-card {
        box-shadow: none !important;
        border: 1px solid #ccc !important;
        margin: 0 !important;
        max-width: 100% !important;
    }
}

@media (max-width: 640px) {
    .turget-voucher-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }
    .turget-voucher-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    .turget-voucher-header > div:last-child {
        text-align: left;
    }
}
</style>
