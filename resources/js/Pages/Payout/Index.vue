<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    entityType: String,
    entity: Object,
    balance: Object,
    payouts: Object,
});

const { t, locale } = useI18n();

const form = useForm({
    amount: Math.min(props.balance?.available || 0, 500000),
    bank_name: props.entity?.bank_name || 'BCA',
    bank_account_number: props.entity?.bank_account_number || '',
    bank_account_holder: props.entity?.bank_account_holder || '',
});

function submitPayout() {
    form.post('/payouts');
}
</script>

<template>
    <PublicLayout>
        <Head :title="locale === 'id' ? 'Dompet Escrow & Pencairan Dana — Turget.id' : 'Escrow Wallet & Payout — Turget.id'" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container">
                <!-- Top Breadcrumb & Navigation -->
                <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem; margin-bottom: 2.5rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <Link :href="entityType === 'guide' ? '/guide/dashboard' : '/vendor/dashboard'" style="color: var(--color-text-subtle); text-decoration: none; font-size: 0.88rem;">
                                ← {{ entityType === 'guide' ? 'Portal Guide' : 'Portal Vendor' }}
                            </Link>
                            <span style="color: var(--color-text-subtle);">/</span>
                            <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); margin: 0;">
                                💰 Escrow Wallet
                            </span>
                        </div>
                        <h1 style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: var(--color-navy-950);">
                            {{ locale === 'id' ? 'Dompet Escrow & Pencairan Dana' : 'Escrow Wallet & Payouts' }}
                        </h1>
                        <p style="color: var(--color-text-muted); font-size: 1rem;">
                            Mitra: <strong>{{ entity?.display_name || entity?.business_name }}</strong> • Pantau honor trip yang telah selesai dan ajukan pencairan ke rekening bank Anda.
                        </p>
                    </div>

                    <div v-if="entityType === 'guide'">
                        <Link href="/guide/inquiries" class="turget-btn turget-btn-secondary">
                            🎯 Peluang Bidding Trip
                        </Link>
                    </div>
                </div>

                <!-- Balance Stat Cards -->
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem; margin-bottom: 2.5rem;">
                    <!-- Available Balance -->
                    <div style="background: white; border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm); border: 2px solid var(--color-emerald-500);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-size: 0.82rem; text-transform: uppercase; font-weight: 700; color: var(--color-emerald-600);">
                                Saldo Tersedia (Dapat Ditarik)
                            </span>
                            <span style="font-size: 1.5rem;">💵</span>
                        </div>
                        <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-navy-950);">
                            Rp {{ Number(balance.available || 0).toLocaleString('id-ID') }}
                        </div>
                        <p style="font-size: 0.82rem; color: var(--color-text-muted); margin-top: 0.5rem;">
                            Honor dari trip dan layanan yang telah dikonfirmasi tuntas oleh wisatawan.
                        </p>
                    </div>

                    <!-- In Escrow -->
                    <div style="background: white; border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-size: 0.82rem; text-transform: uppercase; font-weight: 700; color: var(--color-amber-600);">
                                Tertahan di Escrow
                            </span>
                            <span style="font-size: 1.5rem;">🛡️</span>
                        </div>
                        <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-blue-600);">
                            Rp {{ Number(balance.held_in_escrow || 0).toLocaleString('id-ID') }}
                        </div>
                        <p style="font-size: 0.82rem; color: var(--color-text-muted); margin-top: 0.5rem;">
                            Pesanan aktif yang sedang berjalan dan akan dilepaskan begitu perjalanan selesai.
                        </p>
                    </div>

                    <!-- Total Withdrawn -->
                    <div style="background: white; border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle);">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <span style="font-size: 0.82rem; text-transform: uppercase; font-weight: 700; color: var(--color-text-subtle);">
                                Total Telah Dicairkan
                            </span>
                            <span style="font-size: 1.5rem;">🏦</span>
                        </div>
                        <div style="font-size: 2.25rem; font-weight: 800; color: var(--color-text-main);">
                            Rp {{ Number(balance.total_withdrawn || 0).toLocaleString('id-ID') }}
                        </div>
                        <p style="font-size: 0.82rem; color: var(--color-text-muted); margin-top: 0.5rem;">
                            Akumulasi seluruh transfer pencairan dana ke rekening bank Anda.
                        </p>
                    </div>
                </div>

                <!-- Main Layout: Withdrawal Form & History -->
                <div style="display: grid; grid-template-columns: 380px 1fr; gap: 2.5rem; align-items: start;">
                    
                    <!-- Withdrawal Request Box -->
                    <div style="background: white; border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle);">
                        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                            Tarik Saldo ke Rekening Bank
                        </h3>
                        <p style="font-size: 0.88rem; color: var(--color-text-muted); margin-bottom: 1.5rem;">
                            Pencairan diproses oleh Tim Keuangan Turget dalam 1x24 jam kerja.
                        </p>

                        <form @submit.prevent="submitPayout" style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <div>
                                <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.35rem;">
                                    Jumlah Penarikan (IDR) *
                                </label>
                                <input
                                    v-model="form.amount"
                                    type="number"
                                    min="50000"
                                    :max="balance.available"
                                    step="10000"
                                    class="turget-input"
                                    required
                                />
                                <span style="font-size: 0.78rem; color: var(--color-text-subtle);">
                                    Minimal Rp 50.000 • Maks: Rp {{ Number(balance.available || 0).toLocaleString('id-ID') }}
                                </span>
                            </div>

                            <div>
                                <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.35rem;">
                                    Bank Tujuan *
                                </label>
                                <select v-model="form.bank_name" class="turget-input">
                                    <option value="BCA">Bank BCA</option>
                                    <option value="Mandiri">Bank Mandiri</option>
                                    <option value="BNI">Bank BNI</option>
                                    <option value="BRI">Bank BRI</option>
                                    <option value="BSI">Bank Syariah Indonesia (BSI)</option>
                                    <option value="CIMB">CIMB Niaga</option>
                                    <option value="Jago">Bank Jago</option>
                                </select>
                            </div>

                            <div>
                                <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.35rem;">
                                    Nomor Rekening Bank *
                                </label>
                                <input
                                    v-model="form.bank_account_number"
                                    type="text"
                                    class="turget-input"
                                    placeholder="Contoh: 8201293847"
                                    required
                                />
                            </div>

                            <div>
                                <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.35rem;">
                                    Nama Pemilik Rekening *
                                </label>
                                <input
                                    v-model="form.bank_account_holder"
                                    type="text"
                                    class="turget-input"
                                    placeholder="Harus sesuai buku tabungan / KTP"
                                    required
                                />
                            </div>

                            <button
                                type="submit"
                                class="turget-btn turget-btn-primary"
                                style="width: 100%; justify-content: center; font-size: 1rem; padding: 0.85rem;"
                                :disabled="form.processing || balance.available < 50000"
                            >
                                {{ form.processing ? 'Mengajukan...' : 'Ajukan Pencairan Dana ➔' }}
                            </button>
                        </form>
                    </div>

                    <!-- Withdrawal History Table -->
                    <div style="background: white; border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle);">
                        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                            Riwayat Permintaan Pencairan Dana
                        </h3>

                        <div v-if="!payouts.data?.length" style="padding: 3rem 1rem; text-align: center; color: var(--color-text-subtle);">
                            Belum ada riwayat penarikan dana.
                        </div>

                        <div v-else style="display: flex; flex-direction: column; gap: 1rem;">
                            <div
                                v-for="item in payouts.data"
                                :key="item.id"
                                style="border: 1px solid var(--color-border-subtle); border-radius: var(--radius-lg); padding: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;"
                            >
                                <div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                        <strong style="font-size: 1.1rem; color: var(--color-navy-950);">
                                            Rp {{ Number(item.amount).toLocaleString('id-ID') }}
                                        </strong>
                                        <span
                                            class="turget-status-badge"
                                            :class="{
                                                'turget-status-in_review': item.status === 'pending' || item.status === 'processing',
                                                'turget-status-published': item.status === 'completed',
                                                'turget-status-rejected': item.status === 'rejected'
                                            }"
                                        >
                                            ● {{ item.status?.toUpperCase() }}
                                        </span>
                                    </div>

                                    <div style="font-size: 0.85rem; color: var(--color-text-muted);">
                                        Ke: <strong>{{ item.bank_name }}</strong> - {{ item.bank_account_number }} (a.n {{ item.bank_account_holder }})
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--color-text-subtle); margin-top: 0.25rem;">
                                        Tanggal: {{ new Date(item.created_at).toLocaleDateString() }}
                                    </div>
                                </div>

                                <div v-if="item.admin_notes" style="font-size: 0.82rem; color: var(--color-text-subtle); max-width: 280px; text-align: right;">
                                    Catatan: {{ item.admin_notes }}
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </PublicLayout>
</template>
