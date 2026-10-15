<script setup>
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    payouts: Object,
    filters: Object,
});

const { t, locale } = useI18n();

const activeModal = ref(null); // 'approve' | 'reject'
const selectedPayout = ref(null);
const approveNotes = ref('');
const approveProof = ref('');
const rejectReason = ref('');

function openApproveModal(item) {
    selectedPayout.value = item;
    approveNotes.value = 'Transfer bank telah diproses sukses via API perbankan.';
    approveProof.value = `TRF-${Date.now()}`;
    activeModal.value = 'approve';
}

function openRejectModal(item) {
    selectedPayout.value = item;
    rejectReason.value = '';
    activeModal.value = 'reject';
}

function confirmApprove() {
    router.post(`/admin/payouts/${selectedPayout.value.id}/approve`, {
        notes: approveNotes.value,
        proof_file_path: approveProof.value,
    }, {
        onSuccess: () => { activeModal.value = null; },
    });
}

function confirmReject() {
    if (!rejectReason.value) return;
    router.post(`/admin/payouts/${selectedPayout.value.id}/reject`, {
        reason: rejectReason.value,
    }, {
        onSuccess: () => { activeModal.value = null; },
    });
}
</script>

<template>
    <AdminLayout>
        <Head title="Antrean Pencairan Dana (Payouts) — Admin Turget.id" />

        <div style="padding: 2rem;">
            <!-- Header -->
            <div style="margin-bottom: 2rem;">
                <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); margin-bottom: 0.5rem;">
                    🏦 Financial Operations
                </span>
                <h1 style="font-size: 1.85rem; font-weight: 700; color: var(--color-navy-950);">
                    Antrean Permintaan Pencairan Dana (Payouts)
                </h1>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Moderasi dan validasi transfer honor tour guide dan vendor mitra ke rekening bank terdaftar.
                </p>
            </div>

            <!-- Table -->
            <div style="background: white; border-radius: var(--radius-lg); border: 1px solid var(--color-border-subtle); overflow: hidden; box-shadow: var(--shadow-xs);">
                <table style="width: 100%; border-collapse: collapse; text-align: left; font-size: 0.88rem;">
                    <thead>
                        <tr style="background: var(--color-surface-subtle); border-bottom: 1px solid var(--color-border-subtle);">
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Mitra Pemohon</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Peran</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Nominal Penarikan</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Rekening Tujuan</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950);">Status</th>
                            <th style="padding: 1rem 1.25rem; font-weight: 700; color: var(--color-navy-950); text-align: right;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr v-if="!payouts.data?.length">
                            <td colspan="6" style="padding: 3rem; text-align: center; color: var(--color-text-subtle);">
                                Tidak ada antrean permintaan pencairan dana saat ini.
                            </td>
                        </tr>
                        <tr
                            v-for="item in payouts.data"
                            :key="item.id"
                            style="border-bottom: 1px solid var(--color-border-subtle);"
                        >
                            <td style="padding: 1rem 1.25rem;">
                                <strong style="color: var(--color-navy-950);">{{ item.user?.name }}</strong>
                                <div style="font-size: 0.75rem; color: var(--color-text-subtle);">Email: {{ item.user?.email }}</div>
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                <span class="turget-badge" style="background: var(--color-sand-100); color: var(--color-navy-900); font-size: 0.72rem; margin: 0;">
                                    {{ item.guide_profile_id ? 'Tour Guide' : (item.vendor_profile_id ? 'Vendor Mitra' : 'User') }}
                                </span>
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                <strong style="font-size: 1.05rem; color: var(--color-blue-600);">
                                    Rp {{ Number(item.amount).toLocaleString('id-ID') }}
                                </strong>
                            </td>
                            <td style="padding: 1rem 1.25rem;">
                                <div><strong>{{ item.bank_name }}</strong> - {{ item.bank_account_number }}</div>
                                <div style="font-size: 0.75rem; color: var(--color-text-subtle);">a.n {{ item.bank_account_holder }}</div>
                            </td>
                            <td style="padding: 1rem 1.25rem;">
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
                            </td>
                            <td style="padding: 1rem 1.25rem; text-align: right;">
                                <div v-if="item.status === 'pending' || item.status === 'processing'" style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                                    <button
                                        type="button"
                                        class="turget-btn turget-btn-primary turget-btn-sm"
                                        style="background: var(--color-emerald-600); font-size: 0.78rem; padding: 0.35rem 0.75rem;"
                                        @click="openApproveModal(item)"
                                    >
                                        ✓ Setujui
                                    </button>
                                    <button
                                        type="button"
                                        class="turget-btn turget-btn-secondary turget-btn-sm"
                                        style="color: var(--color-coral-600); font-size: 0.78rem; padding: 0.35rem 0.75rem;"
                                        @click="openRejectModal(item)"
                                    >
                                        ✕ Tolak
                                    </button>
                                </div>
                                <span v-else style="font-size: 0.78rem; color: var(--color-text-subtle);">
                                    Diproses oleh Admin
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- MODALS -->
            <div v-if="activeModal" style="position: fixed; inset: 0; background: rgba(7, 18, 29, 0.65); display: flex; align-items: center; justify-content: center; z-index: 999; padding: 1.5rem;">
                <div style="background: white; border-radius: var(--radius-xl); padding: 2rem; max-width: 500px; width: 100%; box-shadow: var(--shadow-xl);">
                    
                    <template v-if="activeModal === 'approve'">
                        <h3 style="font-size: 1.35rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                            Konfirmasi Persetujuan Pencairan Dana
                        </h3>
                        <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: 1.25rem;">
                            Nominal: <strong>Rp {{ Number(selectedPayout?.amount).toLocaleString('id-ID') }}</strong> ke rekening <strong>{{ selectedPayout?.bank_name }} ({{ selectedPayout?.bank_account_number }})</strong>.
                        </p>

                        <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem;">
                            <div>
                                <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem;">Nomor Referensi Transfer Bank / Bukti</label>
                                <input v-model="approveProof" type="text" class="turget-input" required />
                            </div>
                            <div>
                                <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem;">Catatan Admin</label>
                                <textarea v-model="approveNotes" rows="2" class="turget-input"></textarea>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                            <button type="button" class="turget-btn turget-btn-secondary" @click="activeModal = null">Batal</button>
                            <button type="button" class="turget-btn turget-btn-primary" style="background: var(--color-emerald-600);" @click="confirmApprove">Setujui & Tandai Selesai</button>
                        </div>
                    </template>

                    <template v-else-if="activeModal === 'reject'">
                        <h3 style="font-size: 1.35rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                            Tolak Permintaan Pencairan Dana
                        </h3>
                        <p style="font-size: 0.9rem; color: var(--color-text-muted); margin-bottom: 1.25rem;">
                            Saldo penarikan akan dikembalikan ke dompet mitra.
                        </p>

                        <div style="margin-bottom: 1.5rem;">
                            <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem;">Alasan Penolakan *</label>
                            <textarea v-model="rejectReason" rows="3" class="turget-input" placeholder="Contoh: Nomor rekening tidak cocok dengan nama KTP..." required></textarea>
                        </div>

                        <div style="display: flex; justify-content: flex-end; gap: 0.75rem;">
                            <button type="button" class="turget-btn turget-btn-secondary" @click="activeModal = null">Batal</button>
                            <button type="button" class="turget-btn turget-btn-primary" style="background: var(--color-coral-600);" @click="confirmReject">Tolak Pencairan</button>
                        </div>
                    </template>

                </div>
            </div>
        </div>
    </AdminLayout>
</template>
