<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    itemType: String,
    item: Object,
    defaultPrice: Number,
    platformFeeRate: Number,
});

const { t, locale } = useI18n();

const selectedAddons = ref({});

const form = useForm({
    start_date: new Date(Date.now() + 2 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
    end_date: props.itemType === 'package' && props.item.duration_days > 1
        ? new Date(Date.now() + (props.item.duration_days + 1) * 24 * 60 * 60 * 1000).toISOString().split('T')[0]
        : '',
    participants_count: props.item.min_participants || 1,
    quantity: 1,
    addons: [],
    special_notes: '',
});

function toggleAddon(addon) {
    if (selectedAddons.value[addon.id]) {
        delete selectedAddons.value[addon.id];
    } else {
        selectedAddons.value[addon.id] = {
            product_id: addon.id,
            name: addon.title,
            price: Number(addon.price),
            quantity: 1,
        };
    }
}

const baseAmount = computed(() => {
    if (props.itemType === 'package') {
        return Number(props.defaultPrice) * Number(form.participants_count || 1);
    }
    return Number(props.defaultPrice) * Number(form.quantity || 1);
});

const addonsTotal = computed(() => {
    return Object.values(selectedAddons.value).reduce((sum, item) => sum + (item.price * item.quantity), 0);
});

const grossTotal = computed(() => {
    return baseAmount.value + addonsTotal.value;
});

const platformFeeAmount = computed(() => {
    return grossTotal.value * (props.platformFeeRate || 0.10);
});

function submitCheckout() {
    form.addons = Object.values(selectedAddons.value);
    if (props.itemType === 'package') {
        form.post(`/checkout/package/${props.item.slug}`);
    } else {
        form.post(`/checkout/vendor-product/${props.item.slug}`);
    }
}
</script>

<template>
    <PublicLayout>
        <Head :title="`Checkout: ${item.title || item.name} — Turget.id`" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container">
                <!-- Breadcrumb -->
                <div style="margin-bottom: 2rem;">
                    <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); margin-bottom: 0.5rem;">
                        🛡️ {{ locale === 'id' ? 'Escrow Protected Checkout' : 'Escrow Protected Checkout' }}
                    </span>
                    <h1 style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: var(--color-navy-950);">
                        {{ locale === 'id' ? 'Pemesanan & Rincian Pembayaran' : 'Booking & Order Summary' }}
                    </h1>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 380px; gap: 2.5rem; align-items: start;">
                    <!-- Left Form Area -->
                    <div style="display: flex; flex-direction: column; gap: 2rem;">
                        
                        <!-- Item Summary Box -->
                        <div style="background: white; border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle);">
                            <div style="display: flex; gap: 1.5rem; align-items: center;">
                                <div style="width: 5rem; height: 5rem; border-radius: var(--radius-lg); background: var(--color-sand-100); display: flex; align-items: center; justify-content: center; font-size: 2rem; flex-shrink: 0;">
                                    {{ itemType === 'package' ? '🎒' : '🏨' }}
                                </div>
                                <div style="flex: 1;">
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                        <span class="turget-badge" style="background: var(--color-sand-100); color: var(--color-navy-900); font-size: 0.72rem; margin: 0;">
                                            {{ itemType === 'package' ? 'Paket Wisata Terverifikasi' : 'Layanan Vendor Lokal' }}
                                        </span>
                                        <span v-if="item.destination" style="font-size: 0.8rem; color: var(--color-text-subtle);">
                                            📍 {{ item.destination.name }}
                                        </span>
                                    </div>

                                    <h2 style="font-size: 1.35rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.25rem;">
                                        {{ item.title || item.name }}
                                    </h2>

                                    <div style="font-size: 0.88rem; color: var(--color-text-muted);">
                                        Mitra: <strong>{{ item.guide?.user?.name || item.vendor?.business_name || 'Mitra Turget' }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule & Group Form -->
                        <div style="background: white; border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle);">
                            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.5rem;">
                                📅 {{ locale === 'id' ? 'Jadwal & Jumlah Peserta' : 'Schedule & Participants' }}
                            </h3>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.5rem;">
                                <div>
                                    <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.4rem;">
                                        {{ locale === 'id' ? 'Tanggal Pelaksanaan' : 'Trip Date' }} *
                                    </label>
                                    <input v-model="form.start_date" type="date" class="turget-input" required />
                                </div>

                                <div v-if="itemType === 'package'">
                                    <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.4rem;">
                                        {{ locale === 'id' ? 'Jumlah Peserta (Orang)' : 'Number of Guests' }} *
                                    </label>
                                    <input v-model="form.participants_count" type="number" min="1" max="50" class="turget-input" required />
                                </div>

                                <div v-else>
                                    <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.4rem;">
                                        {{ locale === 'id' ? 'Kuantitas Unit / Malam' : 'Quantity / Nights' }} *
                                    </label>
                                    <input v-model="form.quantity" type="number" min="1" max="30" class="turget-input" required />
                                </div>
                            </div>

                            <div>
                                <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.4rem;">
                                    {{ locale === 'id' ? 'Catatan Khusus ke Pemandu / Vendor' : 'Special Notes for Guide / Vendor' }}
                                </label>
                                <textarea
                                    v-model="form.special_notes"
                                    rows="3"
                                    class="turget-input"
                                    :placeholder="locale === 'id' ? 'Titik jemput hotel, preferensi makanan, atau jam kedatangan...' : 'Pickup hotel address, dietary notes...'"
                                ></textarea>
                            </div>
                        </div>

                        <!-- Add-ons Selection (Vendor marketplace add-ons for packages) -->
                        <div v-if="itemType === 'package' && item.vendor_addons?.length" style="background: white; border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle);">
                            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                                ➕ {{ locale === 'id' ? 'Lengkapi Perjalanan dengan Add-on Vendor Lokal' : 'Enhance with Local Vendor Add-ons' }}
                            </h3>
                            <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">
                                Pilih layanan tambahan resmi dari vendor mitra (mobil car rental, penginapan, kapal, peralatan) yang otomatis terintegrasi.
                            </p>

                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div
                                    v-for="addon in item.vendor_addons"
                                    :key="addon.id"
                                    @click="toggleAddon(addon)"
                                    style="border: 2px solid; border-radius: var(--radius-lg); padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; cursor: pointer; transition: var(--transition-base);"
                                    :style="{
                                        borderColor: selectedAddons[addon.id] ? 'var(--color-blue-600)' : 'var(--color-border-subtle)',
                                        background: selectedAddons[addon.id] ? 'var(--color-blue-50)' : 'white'
                                    }"
                                >
                                    <div>
                                        <div style="font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.25rem;">
                                            {{ addon.title }}
                                        </div>
                                        <div style="font-size: 0.85rem; color: var(--color-text-muted);">
                                            Oleh: {{ addon.vendor?.business_name }} • {{ addon.category?.toUpperCase() }}
                                        </div>
                                    </div>

                                    <div style="text-align: right;">
                                        <div style="font-weight: 800; color: var(--color-blue-600); font-size: 1.05rem;">
                                            + Rp {{ Number(addon.price).toLocaleString('id-ID') }}
                                        </div>
                                        <span style="font-size: 0.78rem; font-weight: 700;" :style="{ color: selectedAddons[addon.id] ? 'var(--color-blue-600)' : 'var(--color-text-subtle)' }">
                                            {{ selectedAddons[addon.id] ? '✓ Ditambahkan' : '+ Tambah' }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Right Sticky Price Breakdown -->
                    <div style="position: sticky; top: 6rem; background: white; border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle);">
                        <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.75rem;">
                            🧾 {{ locale === 'id' ? 'Ringkasan Biaya' : 'Price Summary' }}
                        </h3>

                        <div style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.95rem; margin-bottom: 1.5rem;">
                            <div style="display: flex; justify-content: space-between;">
                                <span style="color: var(--color-text-muted);">
                                    {{ itemType === 'package' ? `Paket Utama (${form.participants_count} pax)` : `Layanan Utama (${form.quantity} unit)` }}
                                </span>
                                <strong style="color: var(--color-navy-950);">
                                    Rp {{ baseAmount.toLocaleString('id-ID') }}
                                </strong>
                            </div>

                            <div v-if="addonsTotal > 0" style="display: flex; justify-content: space-between;">
                                <span style="color: var(--color-text-muted);">Add-on Vendor ({{ Object.keys(selectedAddons).length }} item)</span>
                                <strong style="color: var(--color-navy-950);">+ Rp {{ addonsTotal.toLocaleString('id-ID') }}</strong>
                            </div>

                            <div style="display: flex; justify-content: space-between; font-size: 0.85rem; color: var(--color-emerald-600); background: #F0FDF4; padding: 0.5rem 0.75rem; border-radius: var(--radius-md);">
                                <span>Platform Fee Turget.id (10%)</span>
                                <span>Sudah Termasuk</span>
                            </div>

                            <div style="border-top: 2px dashed var(--color-border-subtle); padding-top: 1rem; display: flex; justify-content: space-between; align-items: flex-end;">
                                <span style="font-size: 1.05rem; font-weight: 800; color: var(--color-navy-950);">TOTAL BAYAR:</span>
                                <span style="font-size: 1.65rem; font-weight: 800; color: var(--color-blue-600);">
                                    Rp {{ grossTotal.toLocaleString('id-ID') }}
                                </span>
                            </div>
                        </div>

                        <!-- Escrow Guarantee Badge -->
                        <div style="background: var(--color-surface-subtle); border-radius: var(--radius-lg); padding: 1.15rem; margin-bottom: 1.5rem; border: 1px solid var(--color-border-subtle);">
                            <div style="display: flex; gap: 0.75rem; align-items: flex-start;">
                                <span style="font-size: 1.35rem;">🛡️</span>
                                <div style="font-size: 0.82rem; color: var(--color-navy-900); line-height: 1.45;">
                                    <strong>Garansi 100% Escrow Turget.id</strong>
                                    <p style="color: var(--color-text-muted); margin-top: 0.25rem;">
                                        Dana Anda disimpan di rekening penampung resmi. Pemandu dan vendor baru dibayar setelah Anda konfirmasi trip selesai.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <button
                            type="button"
                            class="turget-btn turget-btn-primary"
                            style="width: 100%; justify-content: center; font-size: 1.05rem; padding: 0.9rem 1.5rem;"
                            :disabled="form.processing"
                            @click="submitCheckout"
                        >
                            {{ form.processing ? 'Memproses...' : 'Lanjut ke Pembayaran ➔' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
