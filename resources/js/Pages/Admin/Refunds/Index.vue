<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    refunds: Object,
    filters: Object,
});

const { t, locale } = useI18n();

const showProcessModal = ref(false);
const selectedRefund = ref(null);
const refundStatus = ref('approved');
const approvedAmount = ref(0);
const adminNotes = ref('');

function openModal(item) {
    selectedRefund.value = item;
    refundStatus.value = 'approved';
    approvedAmount.value = Number(item.calculated_policy_amount || item.requested_amount);
    adminNotes.value = 'Disetujui sesuai kalkulasi kebijakan pembatalan otomatis Turget.';
    showProcessModal.value = true;
}

function processRefund() {
    router.post(`/admin/refunds/${selectedRefund.value.id}/process`, {
        status: refundStatus.value,
        approved_amount: approvedAmount.value,
        notes: adminNotes.value,
    }, {
        onSuccess: () => {
            showProcessModal.value = false;
        },
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Sengketa & Pengajuan Refund — Admin Turget.id" />

        <div style="padding: 2rem;">
            <!-- Header -->
            <div style="margin-bottom: 2rem;">
                <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); margin-bottom: 0.5rem;">
                    ⚖️ Escrow Disputes & Refunds
                </span>
                <h1 style="font-size: 1.85rem; font-weight: 700; color: var(--color-navy-950);">
                    Sengketa & Pengajuan Pengembalian Dana (Refund)
                </h1>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Verifikasi permohonan pembatalan wisatawan dan sesuaikan nominal pengembalian dana escrow.
                </p>
            </div>

            <!-- Table -->
            <div style="background: white; border-radius: var(--radius-lg); border: 1px solid var(--color-border-subtle); overflow: hidden; box-shadow: var(--shadow-xs);">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                    <thead>
                        <tr style="background: var(--color-surface-subtle); border-bottom: 1px solid var(--color-border-subtle);">
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Kode Booking</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Wisatawan</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Total Booking</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Refund Kebijakan</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Alasan Wisatawan</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Status</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950); text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!refunds.data?.length">
                            <td colspan="7" style="padding: 3rem; text-align: center; color: var(--color-text-subtle);">
                                Tidak ada pengajuan refund saat ini.
                            </td>
                        </tr>
                        <tr
                            v-for="item in refunds.data"
                            :key="item.id"
                            style="border-bottom: 1px solid var(--color-border-subtle);"
                        >
                            <td style="padding: 1rem 1.25rem;">
                                <strong style="color: var(--color-blue-600);">{{ item.booking?.booking_code }}</strong>
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                <strong>{{ item.traveler?.name }}</strong>
                            </td>
                            <td style="padding: 1rem 1.25rem; color: var(--color-navy-950);">
                                Rp {{ Number(item.booking?.total_amount || 0).toLocaleString('id-ID') }}
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                <strong style="color: var(--color-coral-600);">
                                    Rp {{ Number(item.calculated_policy_amount).toLocaleString('id-ID') }}
                                </strong>
                            </td>
                            <td style="padding: 1rem 1.25rem; max-width: 260px; font-size: 0.82rem; color: var(--color-text-muted);">
                                "{{ item.reason }}"
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                <span
                                    class="turget-status-badge"
                                    :class="{
                                        'turget-status-in_review': item.status === 'pending',
                                        'turget-status-published': item.status === 'approved',
                                        'turget-status-rejected': item.status === 'rejected'
                                    }"
                                >
                                    ● {{ item.status?.toUpperCase() }}
                                </span>
                            </td>
                            <td style="padding: 1rem 1.25rem; text-align: right;">
                                <button
                                    v-if="item.status === 'pending'"
                                    type="button"
                                    class="turget-btn turget-btn-primary turget-btn-sm"
                                    style="font-size: 0.78rem; padding: 0.35rem 0.75rem;"
                                    @click="openModal(item)"
                                >
                                    ⚖️ Proses Dispute
                                </button>
                                <span v-else style="font-size: 0.78rem; color: var(--color-text-subtle);">
                                    Selesai
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Process Dispute Modal -->
            <div v-if="showProcessModal" style="position: fixed; inset: 0; background: rgba(7, 18, 29, 0.65); display: flex; align-items: center; justify-content: center; z-index: 999; padding: 1.5rem;">
                <div style="background: white; border-radius: var(--radius-xl); padding: 2rem; max-width: 500px; width: 100%; box-shadow: var(--shadow-xl);">
                    <h3 style="font-size: 1.35rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        Moderasi Pengajuan Refund
                    </h3>
                    <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: 1.25rem;">
                        Kode Booking: <strong>{{ selectedRefund?.booking?.booking_code }}</strong> oleh {{ selectedRefund?.traveler?.name }}
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem;">
                        <div>
                            <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem;">Keputusan *</label>
                            <select v-model="refundStatus" class="turget-input">
                                <option value="approved">Setujui Pengembalian Dana (Approved)</option>
                                <option value="rejected">Tolak Pengembalian Dana (Rejected)</option>
                            </select>
                        </div>

                        <div v-if="refundStatus === 'approved'">
                            <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem;">Nominal Refund Disetujui (IDR) *</label>
                            <input v-model="approvedAmount" type="number" class="turget-input" required />
                        </div>

                        <div>
                            <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem;">Catatan Moderator Admin</label>
                            <textarea v-model="adminNotes" rows="2" class="turget-input"></textarea>
                        </div>
                    </div>

                    <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                        <button type="button" class="turget-btn turget-btn-secondary" @click="showProcessModal = false">Batal</button>
                        <button type="button" class="turget-btn turget-btn-primary" @click="processRefund">Simpan Keputusan</button>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
