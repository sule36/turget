<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    ledgers: Object,
    stats: Object,
    filters: Object,
});

const { t, locale } = useI18n();

const selectedStatus = ref(props.filters?.status || 'all');
const selectedBeneficiary = ref(props.filters?.beneficiary || 'all');

function applyFilter() {
    router.get('/admin/escrow', {
        status: selectedStatus.value,
        beneficiary: selectedBeneficiary.value,
    }, { preserveState: true });
}

function forceRelease(ledgerId) {
    if (confirm('Lepas dana escrow secara manual ke saldo mitra penerima?')) {
        router.post(`/admin/escrow/${ledgerId}/release`);
    }
}
</script>

<template>
    <AdminLayout>
        <Head title="Buku Besar Escrow & Pendapatan Komisi — Admin Turget.id" />

        <div style="padding: 2rem;">
            <!-- Header -->
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 2rem;">
                <div>
                    <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); margin-bottom: 0.5rem;">
                        🛡️ Financial & Escrow Oversight
                    </span>
                    <h1 style="font-size: 1.85rem; font-weight: 700; color: var(--color-navy-950);">
                        Buku Besar Escrow & Komisi Platform
                    </h1>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                        Pantau seluruh dana yang tertahan di rekening escrow, pelepasan honor mitra, dan akumulasi komisi 10% Turget.id.
                    </p>
                </div>
            </div>

            <!-- Stats Grid -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; margin-bottom: 2.5rem;">
                <div style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-xs);">
                    <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: var(--color-amber-600);">
                        Tertahan di Escrow (Held)
                    </div>
                    <div style="font-size: 1.75rem; font-weight: 800; color: var(--color-blue-600); margin-top: 0.35rem;">
                        Rp {{ Number(stats.total_held || 0).toLocaleString('id-ID') }}
                    </div>
                </div>

                <div style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-xs);">
                    <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: var(--color-emerald-600);">
                        Telah Dilepas ke Mitra
                    </div>
                    <div style="font-size: 1.75rem; font-weight: 800; color: var(--color-emerald-600); margin-top: 0.35rem;">
                        Rp {{ Number(stats.total_released || 0).toLocaleString('id-ID') }}
                    </div>
                </div>

                <div style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-xs);">
                    <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: var(--color-navy-950);">
                        Pendapatan Komisi (10%)
                    </div>
                    <div style="font-size: 1.75rem; font-weight: 800; color: var(--color-navy-950); margin-top: 0.35rem;">
                        Rp {{ Number(stats.total_platform_revenue || 0).toLocaleString('id-ID') }}
                    </div>
                </div>

                <div style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-xs);">
                    <div style="font-size: 0.78rem; text-transform: uppercase; font-weight: 700; color: var(--color-coral-600);">
                        Total Refund
                    </div>
                    <div style="font-size: 1.75rem; font-weight: 800; color: var(--color-coral-600); margin-top: 0.35rem;">
                        Rp {{ Number(stats.total_refunded || 0).toLocaleString('id-ID') }}
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <div style="background: white; border-radius: var(--radius-lg); padding: 1.25rem 1.5rem; border: 1px solid var(--color-border-subtle); margin-bottom: 1.5rem; display: flex; gap: 1.5rem; align-items: center; flex-wrap: wrap;">
                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; display: block; margin-bottom: 0.25rem;">Status Escrow</label>
                    <select v-model="selectedStatus" class="turget-input" style="padding: 0.4rem 0.8rem; font-size: 0.88rem;" @change="applyFilter">
                        <option value="all">Semua Status</option>
                        <option value="held">Tertahan (Held)</option>
                        <option value="released">Dilepas (Released)</option>
                        <option value="refunded">Dikembalikan (Refunded)</option>
                        <option value="disputed">Sengketa (Disputed)</option>
                    </select>
                </div>

                <div>
                    <label style="font-size: 0.8rem; font-weight: 700; display: block; margin-bottom: 0.25rem;">Penerima (Beneficiary)</label>
                    <select v-model="selectedBeneficiary" class="turget-input" style="padding: 0.4rem 0.8rem; font-size: 0.88rem;" @change="applyFilter">
                        <option value="all">Semua Penerima</option>
                        <option value="guide">Tour Guide</option>
                        <option value="vendor">Vendor Mitra</option>
                        <option value="platform">Platform Turget.id</option>
                    </select>
                </div>
            </div>

            <!-- Ledgers Table -->
            <div style="background: white; border-radius: var(--radius-lg); border: 1px solid var(--color-border-subtle); overflow: hidden; box-shadow: var(--shadow-xs);">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                    <thead>
                        <tr style="background: var(--color-surface-subtle); border-bottom: 1px solid var(--color-border-subtle);">
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Booking Code</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Penerima</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Jumlah Kotor</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Fee</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Jumlah Bersih</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Status</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950); text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!ledgers.data?.length">
                            <td colspan="7" style="padding: 3rem; text-align: center; color: var(--color-text-subtle);">
                                Tidak ada catatan escrow ledger yang sesuai filter.
                            </td>
                        </tr>
                        <tr
                            v-for="item in ledgers.data"
                            :key="item.id"
                            style="border-bottom: 1px solid var(--color-border-subtle);"
                        >
                            <td style="padding: 1rem 1.25rem;">
                                <strong style="color: var(--color-blue-600);">{{ item.booking?.booking_code || '-' }}</strong>
                                <div style="font-size: 0.75rem; color: var(--color-text-subtle);">Wisatawan: {{ item.booking?.traveler?.name }}</div>
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                <span class="turget-badge" style="background: var(--color-sand-100); color: var(--color-navy-900); font-size: 0.72rem; margin: 0; text-transform: uppercase;">
                                    {{ item.beneficiary_type }}
                                </span>
                            </td>
                            <td style="padding: 1rem 1.25rem; color: var(--color-text-muted);">
                                Rp {{ Number(item.gross_amount).toLocaleString('id-ID') }}
                            </td>
                            <td style="padding: 1rem 1.25rem; color: var(--color-coral-600);">
                                - Rp {{ Number(item.platform_fee_amount).toLocaleString('id-ID') }}
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                <strong style="color: var(--color-navy-950);">
                                    Rp {{ Number(item.net_amount).toLocaleString('id-ID') }}
                                </strong>
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                <span
                                    class="turget-status-badge"
                                    :class="{
                                        'turget-status-in_review': item.status === 'held',
                                        'turget-status-published': item.status === 'released',
                                        'turget-status-rejected': item.status === 'refunded' || item.status === 'disputed'
                                    }"
                                >
                                    ● {{ item.status?.toUpperCase() }}
                                </span>
                            </td>
                            <td style="padding: 1rem 1.25rem; text-align: right;">
                                <button
                                    v-if="item.status === 'held' && item.beneficiary_type !== 'platform'"
                                    type="button"
                                    class="turget-btn turget-btn-secondary turget-btn-sm"
                                    style="font-size: 0.78rem; padding: 0.3rem 0.65rem;"
                                    @click="forceRelease(item.id)"
                                >
                                    Lepas Manual
                                </button>
                                <span v-else style="font-size: 0.78rem; color: var(--color-text-subtle);">
                                    -
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
