<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    booking: Object,
});

const { t, locale } = useI18n();

const showRefundModal = ref(false);
const refundReason = ref('');
const isSubmitting = ref(false);

const reviewRating = ref(5);
const reviewComment = ref('');
const isSubmittingReview = ref(false);

function submitReview() {
    if (!reviewRating.value) {
        alert(locale.value === 'id' ? 'Silakan pilih rating bintang 1-5.' : 'Please select star rating.');
        return;
    }
    isSubmittingReview.value = true;
    router.post(`/bookings/${props.booking.id}/review`, {
        rating: reviewRating.value,
        comment: reviewComment.value,
    }, {
        onFinish: () => {
            isSubmittingReview.value = false;
        },
    });
}

function completeTrip() {
    if (confirm(locale.value === 'id' ? 'Konfirmasi bahwa perjalanan telah selesai dengan baik? Dana akan langsung dilepaskan dari escrow ke saldo pemandu wisata.' : 'Confirm trip completed? Escrow funds will be released to the guide.')) {
        router.post(`/bookings/${props.booking.id}/complete`);
    }
}

function submitRefund() {
    if (!refundReason.value || refundReason.value.length < 10) {
        alert(locale.value === 'id' ? 'Mohon sertakan alasan pembatalan minimal 10 karakter.' : 'Please provide a valid cancellation reason.');
        return;
    }

    isSubmitting.value = true;
    router.post(`/bookings/${props.booking.id}/refund`, {
        reason: refundReason.value,
    }, {
        onFinish: () => {
            isSubmitting.value = false;
            showRefundModal.value = false;
        },
    });
}
</script>

<template>
    <PublicLayout>
        <Head :title="`Voucher Pemesanan ${booking.booking_code} — Turget.id`" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container" style="max-width: 920px;">
                <!-- Breadcrumb & Top Actions -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <Link href="/my-bookings" class="turget-btn turget-btn-secondary" style="font-size: 0.88rem;">
                            ← {{ locale === 'id' ? 'Pesanan Saya' : 'My Bookings' }}
                        </Link>
                        <Link
                            :href="`/bookings/${booking.id}/voucher`"
                            class="turget-btn"
                            style="font-size: 0.88rem; background: var(--color-blue-50); color: var(--color-blue-600); border: 1px solid var(--color-blue-200); font-weight: 600;"
                        >
                            📄 {{ locale === 'id' ? 'Cetak E-Voucher Resmi' : 'Official E-Voucher' }}
                        </Link>
                    </div>

                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); font-weight: 700; margin: 0;">
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
                </div>

                <!-- REVIEW & RATING CARD (When Trip is Completed) -->
                <div v-if="booking.status === 'completed'" style="background: white; border-radius: var(--radius-xl); padding: 2rem 2.5rem; box-shadow: var(--shadow-sm); border: 2px solid var(--color-blue-100); margin-bottom: 2rem;">
                    <!-- If already reviewed -->
                    <div v-if="booking.review">
                        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem; margin-bottom: 0.75rem;">
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <span class="turget-badge" style="background: #ECFDF5; color: #065F46; border-color: #A7F3D0; margin: 0;">
                                    ✓ Ulasan Anda Telah Terbit
                                </span>
                                <span style="color: #F59E0B; font-size: 1.1rem; font-weight: 700;">
                                    {{ '★'.repeat(booking.review.rating) }}{{ '☆'.repeat(5 - booking.review.rating) }}
                                </span>
                                <span style="font-weight: 700; color: var(--color-navy-950);">
                                    ({{ booking.review.rating }}/5)
                                </span>
                            </div>
                            <span style="font-size: 0.8rem; color: var(--color-text-subtle);">
                                Diterbitkan pada {{ new Date(booking.review.created_at).toLocaleDateString() }}
                            </span>
                        </div>
                        <p v-if="booking.review.comment" style="font-size: 0.95rem; color: var(--color-navy-950); line-height: 1.6; margin: 0; background: var(--color-surface-subtle); padding: 1rem 1.25rem; border-radius: var(--radius-lg); font-style: italic;">
                            "{{ booking.review.comment }}"
                        </p>
                    </div>

                    <!-- If not yet reviewed -->
                    <div v-else>
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                            <span style="font-size: 1.5rem;">⭐</span>
                            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--color-navy-950); margin: 0;">
                                {{ locale === 'id' ? 'Beri Ulasan & Rating Pemandu / Vendor' : 'Rate & Review Your Experience' }}
                            </h3>
                        </div>
                        <p style="font-size: 0.88rem; color: var(--color-text-muted); margin-bottom: 1.25rem;">
                            {{ locale === 'id'
                                ? 'Bagikan pengalaman autentik Anda. Ulasan terverifikasi Anda sangat berharga bagi pemandu lokal dan wisatawan lainnya.'
                                : 'Share your authentic experience. Verified feedback helps local guides and travelers.' }}
                        </p>

                        <!-- Star Rating Selector -->
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 1.25rem;">
                            <span style="font-size: 0.88rem; font-weight: 600; color: var(--color-navy-950);">Rating Bintang:</span>
                            <div style="display: flex; gap: 0.25rem;">
                                <button
                                    v-for="star in 5"
                                    :key="star"
                                    type="button"
                                    @click="reviewRating = star"
                                    style="background: none; border: none; font-size: 1.75rem; cursor: pointer; color: star <= reviewRating ? '#F59E0B' : '#D1D5DB'; transition: transform 0.15s;"
                                    :title="`${star} Bintang`"
                                >
                                    ★
                                </button>
                            </div>
                            <span style="font-weight: 700; color: #D97706; margin-left: 0.5rem; font-size: 1rem;">
                                {{ reviewRating }} / 5 Bintang
                            </span>
                        </div>

                        <div style="margin-bottom: 1.25rem;">
                            <label class="turget-label" style="font-weight: 700; margin-bottom: 0.4rem; display: block;">
                                {{ locale === 'id' ? 'Tulis Ulasan / Testimoni Anda (Opsional):' : 'Write Your Review (Optional):' }}
                            </label>
                            <textarea
                                v-model="reviewComment"
                                rows="3"
                                class="turget-input"
                                :placeholder="locale === 'id' ? 'Ceritakan keramahan pemandu, ketepatan waktu, kebersihan fasilitas, atau momen terbaik perjalanan Anda...' : 'Share details about guide hospitality, punctuality, or memorable moments...'"
                                style="border-radius: var(--radius-lg); padding: 0.85rem 1.15rem;"
                            ></textarea>
                        </div>

                        <button
                            type="button"
                            class="turget-btn turget-btn-primary"
                            :disabled="isSubmittingReview"
                            @click="submitReview"
                            style="font-weight: 700; padding: 0.75rem 1.5rem;"
                        >
                            {{ isSubmittingReview ? 'Mengirim...' : (locale === 'id' ? 'Kirim Ulasan Terverifikasi ➔' : 'Submit Review ➔') }}
                        </button>
                    </div>
                </div>

                <!-- ESCROW STATUS BANNER -->
                <div
                    v-if="booking.status === 'paid_escrow' || booking.status === 'confirmed' || booking.status === 'in_progress'"
                    style="background: #ECFDF5; border: 2px solid var(--color-emerald-500); border-radius: var(--radius-xl); padding: 1.5rem 2rem; margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;"
                >
                    <div style="display: flex; gap: 1rem; align-items: center;">
                        <div style="width: 3.5rem; height: 3.5rem; border-radius: 50%; background: var(--color-emerald-500); color: white; display: flex; align-items: center; justify-content: center; font-size: 1.75rem; flex-shrink: 0;">
                            🛡️
                        </div>
                        <div>
                            <div style="font-weight: 800; color: var(--color-emerald-600); font-size: 1.15rem;">
                                Dana Anda 100% Aman Tersimpan di Escrow Turget.id
                            </div>
                            <p style="font-size: 0.88rem; color: var(--color-navy-950); margin-top: 0.2rem;">
                                Pemandu dan vendor baru dapat mencairkan dana setelah Anda menekan tombol "Konfirmasi Trip Selesai" di bawah.
                            </p>
                        </div>
                    </div>

                    <div>
                        <button
                            type="button"
                            class="turget-btn turget-btn-primary"
                            style="background: var(--color-emerald-600); font-weight: 700;"
                            @click="completeTrip"
                        >
                            ✓ Konfirmasi Trip Selesai
                        </button>
                    </div>
                </div>

                <!-- Main E-Voucher Card -->
                <div style="background: white; border-radius: var(--radius-xl); padding: 2.5rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle); margin-bottom: 2rem;">
                    
                    <!-- Voucher Header -->
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; border-bottom: 2px dashed var(--color-border-subtle); padding-bottom: 2rem; margin-bottom: 2rem; flex-wrap: wrap; gap: 1.5rem;">
                        <div>
                            <div style="font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-text-subtle); font-weight: 700;">
                                Official Turget.id Travel Voucher
                            </div>
                            <h1 style="font-family: var(--font-serif); font-size: 1.75rem; font-weight: 700; color: var(--color-navy-950); margin-top: 0.25rem;">
                                {{ booking.tour_package?.title || booking.vendor_product?.title || booking.trip_proposal?.title || 'Bespoke Travel Experience' }}
                            </h1>
                            <div style="font-size: 0.92rem; color: var(--color-text-muted); margin-top: 0.5rem;">
                                Dipesan oleh: <strong>{{ booking.traveler?.name }}</strong> • Kode Voucher: <strong style="color: var(--color-blue-600);">{{ booking.booking_code }}</strong>
                            </div>
                        </div>

                        <!-- Mock QR Check-in Code -->
                        <div style="text-align: center; background: var(--color-surface-subtle); padding: 1rem; border-radius: var(--radius-lg); border: 1px solid var(--color-border-subtle);">
                            <svg width="90" height="90" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <rect width="100" height="100" fill="white"/>
                                <path d="M10 10H35V35H10V10ZM15 15V30H30V15H15Z" fill="#0B1B2B"/>
                                <rect x="20" y="20" width="5" height="5" fill="#0B1B2B"/>
                                <path d="M65 10H90V35H65V10ZM70 15V30H85V15H70Z" fill="#0B1B2B"/>
                                <rect x="75" y="20" width="5" height="5" fill="#0B1B2B"/>
                                <path d="M10 65H35V90H10V65ZM15 70V85H30V70H15Z" fill="#0B1B2B"/>
                                <rect x="20" y="75" width="5" height="5" fill="#0B1B2B"/>
                                <circle cx="50" cy="50" r="6" fill="#2563EB"/>
                            </svg>
                            <div style="font-size: 0.7rem; color: var(--color-text-subtle); margin-top: 0.25rem; font-weight: 700;">
                                SCAN CHECK-IN
                            </div>
                        </div>
                    </div>

                    <!-- Details Grid -->
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem;">
                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700;">
                                Tanggal Pelaksanaan
                            </div>
                            <div style="font-weight: 700; color: var(--color-navy-950); margin-top: 0.2rem;">
                                {{ booking.start_date }} <span v-if="booking.end_date">s/d {{ booking.end_date }}</span>
                            </div>
                        </div>

                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700;">
                                Jumlah Peserta
                            </div>
                            <div style="font-weight: 700; color: var(--color-navy-950); margin-top: 0.2rem;">
                                {{ booking.participants_count }} Orang
                            </div>
                        </div>

                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700;">
                                Mitra Penyedia
                            </div>
                            <div style="font-weight: 700; color: var(--color-navy-950); margin-top: 0.2rem;">
                                {{ booking.guide?.user?.name || booking.vendor?.business_name || 'Mitra Turget.id' }}
                            </div>
                        </div>

                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700;">
                                Total Pembayaran
                            </div>
                            <div style="font-weight: 800; color: var(--color-blue-600); font-size: 1.15rem; margin-top: 0.2rem;">
                                Rp {{ Number(booking.total_amount).toLocaleString('id-ID') }}
                            </div>
                        </div>
                    </div>

                    <!-- Vendor Addons Section -->
                    <div v-if="booking.addons?.length" style="background: var(--color-surface-subtle); border-radius: var(--radius-lg); padding: 1.25rem; margin-bottom: 2rem; border: 1px solid var(--color-border-subtle);">
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                            ➕ Layanan Tambahan (Vendor Add-ons):
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <div v-for="addon in booking.addons" :key="addon.id" style="display: flex; justify-content: space-between; font-size: 0.9rem;">
                                <span>{{ addon.product_name }} (x{{ addon.quantity }})</span>
                                <strong>Rp {{ Number(addon.subtotal).toLocaleString('id-ID') }}</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Escrow Ledger Tracking -->
                    <div v-if="booking.escrow_ledgers?.length" style="border-top: 1px solid var(--color-border-subtle); padding-top: 1.5rem; margin-bottom: 2rem;">
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                            📑 Transparansi Distribusi Dana Escrow:
                        </h4>
                        <div style="display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.88rem;">
                            <div v-for="ledger in booking.escrow_ledgers" :key="ledger.id" style="display: flex; justify-content: space-between; padding: 0.6rem 0.85rem; background: var(--color-surface-subtle); border-radius: var(--radius-md);">
                                <div>
                                    <strong style="text-transform: capitalize;">{{ ledger.beneficiary_type }}:</strong>
                                    <span style="color: var(--color-text-muted); margin-left: 0.35rem;">
                                        {{ ledger.beneficiary_type === 'platform' ? 'Fee Layanan Turget.id (10%)' : (ledger.beneficiary_type === 'guide' ? 'Honor Pemandu Wisata' : 'Pembayaran Vendor Layanan') }}
                                    </span>
                                </div>
                                <div style="display: flex; gap: 1rem; align-items: center;">
                                    <strong style="color: var(--color-navy-950);">Rp {{ Number(ledger.net_amount).toLocaleString('id-ID') }}</strong>
                                    <span class="turget-badge" style="font-size: 0.7rem; margin: 0;" :style="{ background: ledger.status === 'released' ? 'var(--color-emerald-50)' : 'var(--color-sand-100)', color: ledger.status === 'released' ? 'var(--color-emerald-600)' : 'var(--color-navy-900)' }">
                                        {{ ledger.status?.toUpperCase() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Cancellation & Refund Policy -->
                    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; border-top: 1px solid var(--color-border-subtle); padding-top: 1.5rem;">
                        <div style="font-size: 0.85rem; color: var(--color-text-muted); max-width: 500px;">
                            Kebijakan Pembatalan: Pembatalan ≥48 jam sebelum trip berhak atas refund 95% (dipotong 5% gateway/admin fee).
                        </div>

                        <div v-if="booking.status === 'paid_escrow' || booking.status === 'confirmed'">
                            <button
                                type="button"
                                class="turget-btn turget-btn-secondary"
                                style="color: var(--color-coral-600); font-size: 0.88rem;"
                                @click="showRefundModal = true"
                            >
                                Batalkan & Ajukan Refund
                            </button>
                        </div>
                    </div>

                </div>

                <!-- REFUND MODAL -->
                <div v-if="showRefundModal" style="position: fixed; inset: 0; background: rgba(7, 18, 29, 0.65); display: flex; align-items: center; justify-content: center; z-index: 999; padding: 1.5rem;">
                    <div style="background: white; border-radius: var(--radius-xl); padding: 2rem; max-width: 500px; width: 100%; box-shadow: var(--shadow-xl);">
                        <h3 style="font-size: 1.35rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                            Pengajuan Pembatalan & Refund
                        </h3>
                        <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: 1.25rem;">
                            Sistem akan secara otomatis menghitung persentase pengembalian dana berdasarkan sisa waktu menuju jadwal pelaksanaan trip.
                        </p>

                        <div style="margin-bottom: 1.5rem;">
                            <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.4rem;">
                                Alasan Pembatalan *
                            </label>
                            <textarea
                                v-model="refundReason"
                                rows="4"
                                class="turget-input"
                                placeholder="Jelaskan alasan pembatalan Anda..."
                                required
                            ></textarea>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                            <button
                                type="button"
                                class="turget-btn turget-btn-secondary"
                                @click="showRefundModal = false"
                            >
                                Batal
                            </button>
                            <button
                                type="button"
                                class="turget-btn turget-btn-primary"
                                style="background: var(--color-coral-600);"
                                :disabled="isSubmitting"
                                @click="submitRefund"
                            >
                                {{ isSubmitting ? 'Memproses...' : 'Ajukan Refund' }}
                            </button>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </PublicLayout>
</template>
