<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    booking: Object,
});

const { t, locale } = useI18n();

const selectedChannel = ref('qris');
const isSimulating = ref(false);

function simulatePayment() {
    isSimulating.value = true;
    router.post(`/bookings/${props.booking.id}/pay-sandbox`, {
        payment_method: selectedChannel.value,
    }, {
        onFinish: () => {
            isSimulating.value = false;
        },
    });
}
</script>

<template>
    <PublicLayout>
        <Head :title="`Pembayaran Booking ${booking.booking_code} — Turget.id`" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container" style="max-width: 820px;">
                <!-- Header -->
                <div style="text-align: center; margin-bottom: 2.5rem;">
                    <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); margin-bottom: 0.5rem;">
                        💳 Payment Gateway Sandbox Simulator
                    </span>
                    <h1 style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        {{ locale === 'id' ? 'Selesaikan Pembayaran Aman' : 'Complete Secure Payment' }}
                    </h1>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                        Kode Booking: <strong style="color: var(--color-navy-950);">{{ booking.booking_code }}</strong>
                    </p>
                </div>

                <!-- Main Card -->
                <div style="background: white; border-radius: var(--radius-xl); padding: 2.5rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle); margin-bottom: 2rem;">
                    
                    <!-- Amount Bar -->
                    <div style="background: var(--color-navy-900); color: white; border-radius: var(--radius-lg); padding: 1.5rem 2rem; display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 1rem;">
                        <div>
                            <div style="font-size: 0.85rem; color: var(--color-sand-200); text-transform: uppercase; letter-spacing: 0.05em;">
                                Total Tagihan Escrow
                            </div>
                            <div style="font-size: 2rem; font-weight: 800; color: white;">
                                Rp {{ Number(booking.total_amount).toLocaleString('id-ID') }}
                            </div>
                        </div>

                        <div style="text-align: right; font-size: 0.85rem; color: var(--color-sand-200);">
                            <div>Status: <span style="color: var(--color-amber-500); font-weight: 700;">Menunggu Pembayaran</span></div>
                            <div style="font-size: 0.75rem; color: rgba(255,255,255,0.6); margin-top: 0.2rem;">Otomatis kedaluwarsa dlm 24 jam</div>
                        </div>
                    </div>

                    <!-- Payment Channel Selector -->
                    <div style="margin-bottom: 2rem;">
                        <label style="font-size: 0.95rem; font-weight: 700; color: var(--color-navy-950); display: block; margin-bottom: 1rem;">
                            Pilih Metode Pembayaran:
                        </label>

                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem;">
                            <!-- QRIS -->
                            <div
                                @click="selectedChannel = 'qris'"
                                style="border: 2px solid; border-radius: var(--radius-lg); padding: 1.25rem; text-align: center; cursor: pointer; transition: var(--transition-base);"
                                :style="{
                                    borderColor: selectedChannel === 'qris' ? 'var(--color-blue-600)' : 'var(--color-border-subtle)',
                                    background: selectedChannel === 'qris' ? 'var(--color-blue-50)' : 'white'
                                }"
                            >
                                <div style="font-size: 1.8rem; margin-bottom: 0.35rem;">📱</div>
                                <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.95rem;">QRIS</div>
                                <div style="font-size: 0.75rem; color: var(--color-text-muted);">BCA, GoPay, OVO, Shopee</div>
                            </div>

                            <!-- Virtual Account -->
                            <div
                                @click="selectedChannel = 'va'"
                                style="border: 2px solid; border-radius: var(--radius-lg); padding: 1.25rem; text-align: center; cursor: pointer; transition: var(--transition-base);"
                                :style="{
                                    borderColor: selectedChannel === 'va' ? 'var(--color-blue-600)' : 'var(--color-border-subtle)',
                                    background: selectedChannel === 'va' ? 'var(--color-blue-50)' : 'white'
                                }"
                            >
                                <div style="font-size: 1.8rem; margin-bottom: 0.35rem;">🏦</div>
                                <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.95rem;">Virtual Account</div>
                                <div style="font-size: 0.75rem; color: var(--color-text-muted);">BCA, Mandiri, BNI, BRI</div>
                            </div>

                            <!-- Credit Card -->
                            <div
                                @click="selectedChannel = 'card'"
                                style="border: 2px solid; border-radius: var(--radius-lg); padding: 1.25rem; text-align: center; cursor: pointer; transition: var(--transition-base);"
                                :style="{
                                    borderColor: selectedChannel === 'card' ? 'var(--color-blue-600)' : 'var(--color-border-subtle)',
                                    background: selectedChannel === 'card' ? 'var(--color-blue-50)' : 'white'
                                }"
                            >
                                <div style="font-size: 1.8rem; margin-bottom: 0.35rem;">💳</div>
                                <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.95rem;">Kartu Kredit</div>
                                <div style="font-size: 0.75rem; color: var(--color-text-muted);">Visa / Mastercard 3DS</div>
                            </div>
                        </div>
                    </div>

                    <!-- Channel Simulation Preview -->
                    <div style="background: var(--color-surface-subtle); border-radius: var(--radius-lg); padding: 2rem; border: 1px solid var(--color-border-subtle); margin-bottom: 2rem; text-align: center;">
                        
                        <!-- QRIS View -->
                        <div v-if="selectedChannel === 'qris'">
                            <div style="font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                                Pindai Kode QRIS Nasional
                            </div>
                            <div style="display: inline-block; background: white; padding: 1.25rem; border-radius: var(--radius-md); box-shadow: var(--shadow-sm); border: 2px solid var(--color-navy-900); margin-bottom: 1rem;">
                                <!-- QR Mockup SVG -->
                                <svg width="180" height="180" viewBox="0 0 100 100" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <rect width="100" height="100" fill="white"/>
                                    <path d="M10 10H35V35H10V10ZM15 15V30H30V15H15Z" fill="#0B1B2B"/>
                                    <rect x="20" y="20" width="5" height="5" fill="#0B1B2B"/>
                                    <path d="M65 10H90V35H65V10ZM70 15V30H85V15H70Z" fill="#0B1B2B"/>
                                    <rect x="75" y="20" width="5" height="5" fill="#0B1B2B"/>
                                    <path d="M10 65H35V90H10V65ZM15 70V85H30V70H15Z" fill="#0B1B2B"/>
                                    <rect x="20" y="75" width="5" height="5" fill="#0B1B2B"/>
                                    <rect x="45" y="10" width="10" height="15" fill="#0B1B2B"/>
                                    <rect x="45" y="35" width="10" height="20" fill="#0B1B2B"/>
                                    <rect x="65" y="45" width="20" height="10" fill="#0B1B2B"/>
                                    <rect x="45" y="65" width="15" height="15" fill="#0B1B2B"/>
                                    <rect x="70" y="70" width="15" height="15" fill="#0B1B2B"/>
                                    <circle cx="50" cy="50" r="8" fill="#2563EB"/>
                                </svg>
                            </div>
                            <div style="font-size: 0.85rem; color: var(--color-text-muted);">
                                Buka aplikasi m-Banking atau e-Wallet favorit Anda, lalu arahkan kamera ke kode QR di atas.
                            </div>
                        </div>

                        <!-- VA View -->
                        <div v-else-if="selectedChannel === 'va'">
                            <div style="font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                                Nomor Virtual Account (BCA / Mandiri / BNI)
                            </div>
                            <div style="font-family: monospace; font-size: 1.6rem; font-weight: 800; letter-spacing: 0.1em; color: var(--color-blue-600); background: white; padding: 0.75rem 1.5rem; border-radius: var(--radius-md); display: inline-block; border: 1px solid var(--color-border-subtle); margin-bottom: 0.75rem;">
                                8808 9201 4481 0029
                            </div>
                            <div style="font-size: 0.85rem; color: var(--color-text-muted);">
                                Nama Akun: <strong>TURGET ID ESCROW</strong>
                            </div>
                        </div>

                        <!-- Card View -->
                        <div v-else>
                            <div style="font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                                Kartu Kredit / Debit Online
                            </div>
                            <div style="max-width: 320px; margin: 0 auto; background: white; padding: 1rem; border-radius: var(--radius-md); border: 1px solid var(--color-border-subtle); font-size: 0.88rem; text-align: left;">
                                <div style="color: var(--color-text-muted); font-size: 0.75rem;">Simulasi Kartu Uji Coba:</div>
                                <div style="font-weight: 700; font-family: monospace; margin-top: 0.25rem;">4111 •••• •••• 1111</div>
                                <div style="font-size: 0.75rem; color: var(--color-text-subtle); margin-top: 0.25rem;">Exp: 12/28 • CVV: 123</div>
                            </div>
                        </div>
                    </div>

                    <!-- Sandbox Instant Payment Action -->
                    <div style="background: #ECFDF5; border: 1px solid var(--color-emerald-500); border-radius: var(--radius-lg); padding: 1.75rem; text-align: center;">
                        <h4 style="font-weight: 700; color: var(--color-emerald-600); font-size: 1.1rem; margin-bottom: 0.35rem;">
                            ⚡ Simulasi Pembayaran Sukses (Sandbox Testing)
                        </h4>
                        <p style="font-size: 0.88rem; color: var(--color-navy-950); max-width: 540px; margin: 0 auto 1.25rem;">
                            Klik tombol di bawah ini untuk mensimulasikan notifikasi sukses dari payment gateway. Sistem akan mengunci dana di rekening Escrow Turget.id dan menerbitkan e-voucher resmi Anda.
                        </p>

                        <button
                            type="button"
                            class="turget-btn turget-btn-primary"
                            style="background: var(--color-emerald-600); font-size: 1.05rem; padding: 0.85rem 2.5rem; justify-content: center;"
                            :disabled="isSimulating"
                            @click="simulatePayment"
                        >
                            {{ isSimulating ? 'Memverifikasi Pembayaran Sandbox...' : '✓ Bayar Sekarang (Simulasi Midtrans Sukses)' }}
                        </button>
                    </div>

                </div>

                <!-- Escrow Security Notice -->
                <div style="text-align: center; color: var(--color-text-muted); font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;">
                    <span>🔒 Enkripsi SSL 256-Bit</span>
                    <span>•</span>
                    <span>🛡️ 100% Escrow Protection</span>
                    <span>•</span>
                    <span>🇮🇩 Terdaftar di OJK & BI Sandbox</span>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
