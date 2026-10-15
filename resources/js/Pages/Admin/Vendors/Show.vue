<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    vendor: Object,
    auditLogs: Array,
});

const { t, tr, locale } = useI18n();

const actionNote = ref('');
const isSubmitting = ref(false);

function startReview() {
    isSubmitting.value = true;
    router.post(`/admin/vendors/${props.vendor.id}/review`, {}, {
        onFinish: () => { isSubmitting.value = false; },
    });
}

function approveVendor() {
    if (!confirm(`Konfirmasi setujui kemitraan dan sematkan Verified Badge untuk ${props.vendor.business_name}?`)) return;
    isSubmitting.value = true;
    router.post(`/admin/vendors/${props.vendor.id}/approve`, {
        notes: actionNote.value || 'Dokumen legalitas terverifikasi lengkap.',
    }, {
        onFinish: () => { isSubmitting.value = false; actionNote.value = ''; },
    });
}

function requestRevision() {
    if (!actionNote.value) {
        alert('Wajib mengisi catatan perbaikan dokumen yang harus direvisi oleh vendor.');
        return;
    }
    isSubmitting.value = true;
    router.post(`/admin/vendors/${props.vendor.id}/revision`, {
        notes: actionNote.value,
    }, {
        onFinish: () => { isSubmitting.value = false; actionNote.value = ''; },
    });
}

function rejectVendor() {
    if (!actionNote.value) {
        alert('Wajib mengisi alasan penolakan pengajuan kemitraan.');
        return;
    }
    if (!confirm('Yakin ingin menolak pengajuan kemitraan ini?')) return;
    isSubmitting.value = true;
    router.post(`/admin/vendors/${props.vendor.id}/reject`, {
        notes: actionNote.value,
    }, {
        onFinish: () => { isSubmitting.value = false; actionNote.value = ''; },
    });
}

function suspendVendor() {
    const reason = prompt('Masukkan alasan penangguhan kemitraan vendor:');
    if (!reason) return;
    isSubmitting.value = true;
    router.post(`/admin/vendors/${props.vendor.id}/suspend`, {
        reason: reason,
    }, {
        onFinish: () => { isSubmitting.value = false; },
    });
}

function reactivateVendor() {
    if (!confirm('Aktifkan kembali kemitraan vendor ini?')) return;
    isSubmitting.value = true;
    router.post(`/admin/vendors/${props.vendor.id}/reactivate`, {}, {
        onFinish: () => { isSubmitting.value = false; },
    });
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
    <AdminLayout>
        <Head :title="`Verifikasi Vendor: ${vendor.business_name} — Turget.id Admin`" />

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <Link href="/admin/vendors" style="color: var(--color-blue-600); font-size: 0.88rem; font-weight: 600; text-decoration: none;">
                    ← Kembali ke Antrean Vendor
                </Link>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.35rem;">
                    <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950); margin: 0;">
                        {{ vendor.business_name }}
                    </h1>
                    <span v-if="vendor.badge_verified" class="turget-badge turget-status-published">
                        🛡️ Terverifikasi
                    </span>
                    <span v-else class="turget-badge" style="background: var(--color-amber-50); color: var(--color-amber-800); border-color: rgba(245,158,11,0.3);">
                        Status: {{ vendor.verification_status }}
                    </span>
                </div>
            </div>

            <a :href="`/vendors/${vendor.slug}`" target="_blank" class="turget-btn turget-btn-secondary">
                Lihat Storefront Publik ↗
            </a>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1.15fr; gap: 2rem; align-items: start;">
            <!-- Left Column: Business Info & KYC Documents -->
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <!-- Business Identity Card -->
                <div class="turget-admin-card">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        🏢 Profil Badan Usaha
                    </h2>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; font-size: 0.92rem;">
                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle);">Bentuk Usaha</div>
                            <div style="font-weight: 700; color: var(--color-navy-950); text-transform: uppercase;">{{ vendor.business_type }}</div>
                        </div>

                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle);">Kategori Layanan</div>
                            <div style="font-weight: 700; color: var(--color-blue-600);">{{ vendor.category }}</div>
                        </div>

                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle);">Nama Pemilik / PIC</div>
                            <div style="font-weight: 600; color: var(--color-navy-900);">{{ vendor.owner_name }}</div>
                        </div>

                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle);">Kontak WhatsApp & Email</div>
                            <div style="color: var(--color-navy-900);">📞 {{ vendor.phone }}<br>✉️ {{ vendor.email }}</div>
                        </div>

                        <div style="grid-column: span 2;">
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle);">Alamat Operasional & Titik GPS</div>
                            <div style="color: var(--color-navy-900);">{{ vendor.address }}, {{ vendor.region?.name }}</div>
                            <div style="font-size: 0.82rem; color: var(--color-blue-600); font-weight: 600; margin-top: 0.25rem;">
                                Koordinat: {{ vendor.latitude }}, {{ vendor.longitude }}
                            </div>
                        </div>

                        <div style="grid-column: span 2;">
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle);">Deskripsi Profil</div>
                            <div style="color: var(--color-text-body); line-height: 1.6; margin-top: 0.25rem;">
                                {{ tr(vendor.description) }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- KYC Documents Review Card -->
                <div class="turget-admin-card">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        📋 Dokumen Legalitas & Kepatuhan (KYC)
                    </h2>

                    <div v-if="vendor.verifications?.length > 0" style="display: flex; flex-direction: column; gap: 1rem;">
                        <div v-for="verif in vendor.verifications" :key="verif.id" style="background: var(--color-surface-subtle); padding: 1.25rem; border-radius: var(--radius-lg); border: 1px solid var(--color-border-subtle);">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; font-size: 0.9rem;">
                                <div>
                                    <span style="font-weight: 700;">NIB (OSS):</span>
                                    <div>{{ verif.nib || 'Tidak dilampirkan' }}</div>
                                </div>
                                <div>
                                    <span style="font-weight: 700;">NPWP Usaha:</span>
                                    <div>{{ verif.npwp || 'Tidak dilampirkan' }}</div>
                                </div>
                                <div>
                                    <span style="font-weight: 700;">SIUP / Izin Wisata:</span>
                                    <div>{{ verif.siup || 'Tidak dilampirkan' }}</div>
                                </div>
                                <div>
                                    <span style="font-weight: 700;">NIK KTP Pemilik:</span>
                                    <div>{{ verif.ktp_owner || 'Tidak dilampirkan' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else style="color: var(--color-text-muted); font-size: 0.9rem;">
                        Belum ada berkas KYC tersimpan.
                    </div>
                </div>

                <!-- Bank & Financial Payout Info -->
                <div class="turget-admin-card">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        💳 Rekening Pencairan & Pembayaran
                    </h2>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem; font-size: 0.92rem;">
                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle);">Bank</div>
                            <div style="font-weight: 700;">{{ vendor.bank_name || '-' }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle);">Nomor Rekening</div>
                            <div style="font-weight: 700; color: var(--color-blue-600);">{{ vendor.bank_account_number || '-' }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle);">Atas Nama</div>
                            <div style="font-weight: 700;">{{ vendor.bank_account_holder || '-' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Vendor Products Offered -->
                <div class="turget-admin-card">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        📦 Produk / Layanan yang Ditawarkan ({{ vendor.products?.length || 0 }})
                    </h2>

                    <div v-if="vendor.products?.length > 0" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div v-for="p in vendor.products" :key="p.id" style="display: flex; justify-content: space-between; align-items: center; padding: 0.75rem 1rem; background: var(--color-surface-subtle); border-radius: var(--radius-md);">
                            <div>
                                <span style="font-weight: 700; color: var(--color-navy-950);">{{ tr(p.name) }}</span>
                                <span class="turget-badge" style="font-size: 0.72rem; margin-left: 0.5rem;">{{ p.category }}</span>
                            </div>
                            <div style="font-weight: 800; color: var(--color-blue-600);">
                                {{ formatPrice(p.price) }} / {{ p.price_unit }}
                            </div>
                        </div>
                    </div>
                    <div v-else style="color: var(--color-text-muted); font-size: 0.9rem;">
                        Vendor belum mengunggah produk.
                    </div>
                </div>
            </div>

            <!-- Right Column: Verification Actions & History -->
            <div>
                <div class="turget-admin-card" style="padding: 1.75rem; position: sticky; top: 1.5rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                        ⚖️ Keputusan Moderasi & KYC
                    </h2>

                    <div style="margin-bottom: 1.25rem;">
                        <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Catatan Review / Revisi:</label>
                        <textarea
                            v-model="actionNote"
                            class="turget-input"
                            rows="3"
                            placeholder="Tuliskan catatan verifikasi atau alasan perbaikan jika meminta revisi..."
                        ></textarea>
                    </div>

                    <!-- Action Buttons based on status -->
                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <button
                            v-if="vendor.verification_status === 'submitted'"
                            type="button"
                            class="turget-btn turget-btn-secondary"
                            :disabled="isSubmitting"
                            @click="startReview"
                        >
                            🔍 Mulai Verifikasi Dokumen
                        </button>

                        <button
                            type="button"
                            class="turget-btn turget-btn-primary"
                            style="background: var(--color-green-600); border-color: var(--color-green-600); justify-content: center;"
                            :disabled="isSubmitting"
                            @click="approveVendor"
                        >
                            ✅ Setujui & Terbitkan Verified Badge
                        </button>

                        <button
                            type="button"
                            class="turget-btn turget-btn-secondary"
                            style="color: var(--color-amber-800); border-color: rgba(245,158,11,0.5); justify-content: center;"
                            :disabled="isSubmitting"
                            @click="requestRevision"
                        >
                            ✏️ Minta Perbaikan Dokumen (Revisi)
                        </button>

                        <button
                            type="button"
                            class="turget-btn"
                            style="color: var(--color-red-600); border-color: rgba(220,38,38,0.3); justify-content: center;"
                            :disabled="isSubmitting"
                            @click="rejectVendor"
                        >
                            ❌ Tolak Pengajuan
                        </button>

                        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-border-subtle);">
                            <button
                                v-if="vendor.is_active && vendor.badge_verified"
                                type="button"
                                class="turget-btn"
                                style="width: 100%; justify-content: center; font-size: 0.82rem; color: var(--color-text-muted);"
                                :disabled="isSubmitting"
                                @click="suspendVendor"
                            >
                                ⏸️ Tangguhkan Kemitraan
                            </button>
                            <button
                                v-else-if="!vendor.is_active"
                                type="button"
                                class="turget-btn turget-btn-secondary"
                                style="width: 100%; justify-content: center; font-size: 0.82rem;"
                                :disabled="isSubmitting"
                                @click="reactivateVendor"
                            >
                                ▶️ Aktifkan Kembali Kemitraan
                            </button>
                        </div>
                    </div>

                    <!-- Audit Log Timeline -->
                    <div v-if="auditLogs?.length > 0" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-subtle);">
                        <div style="font-size: 0.82rem; font-weight: 700; text-transform: uppercase; color: var(--color-text-subtle); margin-bottom: 0.75rem;">
                            Riwayat Audit & Aktivitas:
                        </div>
                        <div style="display: flex; flex-direction: column; gap: 0.6rem; font-size: 0.82rem;">
                            <div v-for="log in auditLogs" :key="log.id" style="color: var(--color-navy-900);">
                                <span style="font-weight: 600;">{{ log.action }}</span>
                                <div style="color: var(--color-text-subtle); font-size: 0.75rem;">
                                    {{ new Date(log.created_at).toLocaleString('id-ID') }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
