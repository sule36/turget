<script setup>
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    guide: Object,
    user: Object,
    verifications: Array,
    destinations: Array,
    packages: Array,
});

const { t, tr } = useI18n();

const actionNote = ref('');
const isSubmitting = ref(false);

function performAction(actionName) {
    if (!confirm(`Konfirmasi tindakan verifikasi: "${actionName}"?`)) return;

    isSubmitting.value = true;
    router.post(`/admin/guides/${props.guide.id}/action`, {
        action: actionName,
        note: actionNote.value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            isSubmitting.value = false;
            actionNote.value = '';
        },
    });
}
</script>

<template>
    <AdminLayout>
        <Head :title="`Verifikasi KYC: ${guide.display_name} — Turget.id Admin`" />

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <Link href="/admin/guides" style="color: var(--color-blue-600); font-size: 0.88rem; font-weight: 600; text-decoration: none;">
                    ← Kembali ke Antrean Verifikasi Guide
                </Link>
                <div style="display: flex; align-items: center; gap: 0.75rem; margin-top: 0.35rem;">
                    <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950);">
                        Pemeriksaan KYC: {{ guide.display_name }}
                    </h1>
                    <span
                        class="turget-status-badge"
                        :class="guide.badge_verified ? 'turget-status-published' : 'turget-status-in_review'"
                    >
                        ● {{ guide.badge_verified ? 'Verified' : guide.verification_status }}
                    </span>
                </div>
            </div>

            <a :href="`/guides/${guide.slug}`" target="_blank" class="turget-btn turget-btn-secondary">
                Lihat Halaman Publik ↗
            </a>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
            <!-- Left: Profile & KYC Documents -->
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <!-- Profile Data -->
                <div class="turget-admin-card">
                    <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        Data Pendaftaran & Kontak
                    </h2>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                        <div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">Nama Lengkap</div>
                            <div style="font-weight: 700; color: var(--color-navy-950); font-size: 1rem;">{{ guide.display_name }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">Email Akun</div>
                            <div style="font-weight: 600; color: var(--color-navy-950);">{{ user?.email }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">Nomor WhatsApp (Internal KYC)</div>
                            <div style="font-weight: 700; color: var(--color-blue-600);">{{ guide.phone }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">Domisili & Wilayah</div>
                            <div style="font-weight: 600; color: var(--color-navy-950);">
                                {{ tr(guide.region?.name) }}, {{ tr(guide.country?.name) }}
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">Tahun Pengalaman</div>
                            <div style="font-weight: 600; color: var(--color-navy-950);">{{ guide.experience_years }} Tahun</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">Tarif Harian Dasar</div>
                            <div style="font-weight: 700; color: var(--color-emerald-600);">
                                {{ new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(guide.price_per_day) }}
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--color-border-subtle);">
                        <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.25rem;">Tagline Profil</div>
                        <div style="font-weight: 600; color: var(--color-navy-950); font-style: italic;">
                            "{{ tr(guide.tagline) }}"
                        </div>
                    </div>

                    <div style="margin-top: 1rem;">
                        <div style="font-size: 0.8rem; color: var(--color-text-muted); margin-bottom: 0.25rem;">Biografi Narasi</div>
                        <div style="font-size: 0.92rem; color: var(--color-navy-950); line-height: 1.6; white-space: pre-line;">
                            {{ tr(guide.bio) }}
                        </div>
                    </div>
                </div>

                <!-- KYC Documents Section -->
                <div class="turget-admin-card">
                    <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        Dokumen Verifikasi (KYC & Lisensi)
                    </h2>

                    <div v-if="verifications.length > 0" style="display: flex; flex-direction: column; gap: 1rem;">
                        <div
                            v-for="v in verifications"
                            :key="v.id"
                            style="border: 1px solid var(--color-border-subtle); border-radius: var(--radius-md); padding: 1.25rem; background: var(--color-surface-subtle); display: flex; justify-content: space-between; align-items: center;"
                        >
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                                    <strong style="text-transform: uppercase; font-size: 0.95rem; color: var(--color-navy-950);">
                                        📄 {{ v.document_type.replace('_', ' ') }}
                                    </strong>
                                    <span class="turget-badge" style="margin: 0; font-size: 0.7rem; background: white;">
                                        Status: {{ v.status }}
                                    </span>
                                </div>
                                <div style="font-size: 0.88rem; color: var(--color-navy-950);">
                                    Nomor Dokumen: <code>{{ v.document_number }}</code>
                                </div>
                                <div style="font-size: 0.78rem; color: var(--color-text-muted); margin-top: 0.2rem;">
                                    Jalur Berkas: <code>{{ v.document_file_path }}</code>
                                </div>
                            </div>

                            <a
                                :href="v.document_file_path"
                                target="_blank"
                                class="turget-btn turget-btn-secondary turget-btn-sm"
                            >
                                Buka Berkas ↗
                            </a>
                        </div>
                    </div>
                    <div v-else style="color: var(--color-text-muted); padding: 2rem 0; text-align: center;">
                        Tidak ada dokumen terlampir.
                    </div>
                </div>

                <!-- Bank Payout Info -->
                <div class="turget-admin-card">
                    <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        Rekening Payout Mitra
                    </h2>

                    <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem;">
                        <div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">Bank</div>
                            <div style="font-weight: 700; color: var(--color-navy-950);">{{ guide.bank_name || 'BCA' }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">Nomor Rekening</div>
                            <div style="font-weight: 700; color: var(--color-navy-950); font-family: monospace;">{{ guide.bank_account_number || '-' }}</div>
                        </div>
                        <div>
                            <div style="font-size: 0.8rem; color: var(--color-text-muted);">Nama Pemilik</div>
                            <div style="font-weight: 700; color: var(--color-navy-950);">{{ guide.bank_account_holder || '-' }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Verification Action Panel -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="turget-admin-card" style="border-top: 4px solid var(--color-blue-600);">
                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        Aksi Keputusan Verifikasi
                    </h3>
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 1.25rem;">
                        Pastikan keaslian KTP, nomor lisensi, serta kesesuaian area layanan sebelum menyetujui.
                    </p>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div>
                            <label class="turget-form-label">Catatan Verifikasi / Alasan Revisi</label>
                            <textarea
                                v-model="actionNote"
                                rows="3"
                                placeholder="Tuliskan catatan untuk tour guide atau alasan penolakan/revisi..."
                                class="turget-form-textarea"
                                style="font-size: 0.85rem;"
                            />
                        </div>

                        <!-- Action Buttons -->
                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <!-- Approve -->
                            <button
                                type="button"
                                @click="performAction('approve')"
                                :disabled="isSubmitting"
                                class="turget-btn turget-btn-primary"
                                style="background: var(--color-emerald-600); justify-content: center;"
                            >
                                ✓ Setujui & Berikan Lencana Verified
                            </button>

                            <!-- Start Review -->
                            <button
                                v-if="guide.verification_status === 'submitted'"
                                type="button"
                                @click="performAction('start_review')"
                                :disabled="isSubmitting"
                                class="turget-btn turget-btn-secondary"
                                style="justify-content: center;"
                            >
                                🔍 Tandai Sedang Ditinjau
                            </button>

                            <!-- Request Revision -->
                            <button
                                type="button"
                                @click="performAction('request_revision')"
                                :disabled="isSubmitting"
                                class="turget-btn turget-btn-secondary"
                                style="color: var(--color-amber-600); border-color: rgba(245,158,11,0.4); justify-content: center;"
                            >
                                ⚠️ Minta Revisi Dokumen
                            </button>

                            <!-- Reject -->
                            <button
                                v-if="guide.verification_status !== 'rejected'"
                                type="button"
                                @click="performAction('reject')"
                                :disabled="isSubmitting"
                                class="turget-btn turget-btn-secondary"
                                style="color: var(--color-coral-600); border-color: rgba(220,38,38,0.3); justify-content: center;"
                            >
                                ✕ Tolak Pendaftaran
                            </button>

                            <!-- Suspend / Reactivate -->
                            <button
                                v-if="guide.badge_verified"
                                type="button"
                                @click="performAction('suspend')"
                                :disabled="isSubmitting"
                                class="turget-btn turget-btn-secondary"
                                style="color: var(--color-coral-600); justify-content: center;"
                            >
                                ⛔ Tangguhkan Akun (Suspend)
                            </button>

                            <button
                                v-if="guide.verification_status === 'suspended'"
                                type="button"
                                @click="performAction('reactivate')"
                                :disabled="isSubmitting"
                                class="turget-btn turget-btn-primary"
                                style="justify-content: center;"
                            >
                                ♻️ Aktifkan Kembali Akun
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Verifier History Card -->
                <div v-if="guide.verified_at" class="turget-admin-card">
                    <h4 style="font-size: 0.9rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        Riwayat Verifikasi Terakhir
                    </h4>
                    <div style="font-size: 0.85rem; color: var(--color-text-muted);">
                        Disetujui oleh: <strong>{{ guide.verifier?.name || 'Administrator' }}</strong><br>
                        Tanggal: {{ new Date(guide.verified_at).toLocaleString() }}
                    </div>
                    <div v-if="guide.verification_note" style="margin-top: 0.5rem; font-size: 0.82rem; background: var(--color-surface-subtle); padding: 0.5rem; border-radius: var(--radius-sm);">
                        Catatan: {{ guide.verification_note }}
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
