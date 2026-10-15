<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    countries: Array,
    regions: Array,
    categories: Array,
});

const { t, tr, locale } = useI18n();

const step = ref(1); // 1: Profil Bisnis, 2: Lokasi & Deskripsi, 3: Rekening & Kebijakan, 4: Legalitas KYC

const form = useForm({
    business_name: '',
    business_type: 'individual',
    category: 'accommodation',
    owner_name: '',
    phone: '',
    email: '',
    country_id: props.countries[0]?.id || 1,
    region_id: props.regions[0]?.id || null,
    service_areas: [],
    address: '',
    latitude: -8.8291,
    longitude: 115.0849,
    description: {
        id: '',
        en: '',
    },
    avatar_url: 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=300&auto=format&fit=crop&q=80',
    cover_image_url: 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=1200&auto=format&fit=crop&q=80',
    bank_name: 'BCA',
    bank_account_number: '',
    bank_account_holder: '',
    opening_hours: {
        id: 'Senin - Minggu, 08:00 - 20:00 WITA',
        en: 'Monday - Sunday, 08:00 - 20:00 WITA',
    },
    cancellation_policy: {
        id: 'Pembatalan gratis hingga 48 jam sebelum jadwal penggunaan layanan.',
        en: 'Free cancellation up to 48 hours before service check-in.',
    },
    // KYC
    nib: '',
    nib_file_path: '/storage/kyc/nib_sample.pdf',
    siup: '',
    siup_file_path: '/storage/kyc/siup_sample.pdf',
    npwp: '',
    npwp_file_path: '/storage/kyc/npwp_sample.pdf',
    ktp_owner: '',
    ktp_file_path: '/storage/kyc/ktp_sample.jpg',
});

function submitOnboarding() {
    form.post('/become-a-vendor');
}
</script>

<template>
    <PublicLayout>
        <Head title="Pendaftaran Kemitraan Vendor Wisata — Turget.id" />

        <section style="background: var(--color-surface-subtle); padding: 3.5rem 0 2rem; border-bottom: 1px solid var(--color-border-subtle);">
            <div class="turget-container" style="max-width: 800px; text-align: center;">
                <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); border-color: rgba(37,99,235,0.2); margin-bottom: 0.75rem;">
                    🏨 Mitra Resmi Penyedia Layanan & Add-on
                </span>
                <h1 style="font-family: var(--font-serif); font-size: 2.5rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                    Bergabung Menjadi Vendor Terverifikasi Turget.id
                </h1>
                <p style="color: var(--color-text-muted); font-size: 1.05rem;">
                    Jangkau ribuan wisatawan nusantara dan mancanegara. Tawarkan villa, rental kendaraan, tiket aktivitas, kapal, dan perlengkapan wisata Anda.
                </p>

                <!-- Steps Progress Bar -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 2.5rem; position: relative;">
                    <div style="position: absolute; top: 50%; left: 10%; right: 10%; height: 2px; background: var(--color-border-subtle); z-index: 1;"></div>
                    <div
                        v-for="s in [1, 2, 3, 4]"
                        :key="s"
                        style="position: relative; z-index: 2; width: 2.5rem; height: 2.5rem; border-radius: var(--radius-full); display: flex; align-items: center; justify-content: center; font-weight: 700; transition: all 0.2s;"
                        :style="step >= s ? 'background: var(--color-blue-600); color: white; box-shadow: var(--shadow-sm);' : 'background: white; color: var(--color-text-muted); border: 2px solid var(--color-border-subtle);'"
                    >
                        {{ s }}
                    </div>
                </div>
                <div style="display: flex; justify-content: space-between; margin-top: 0.5rem; font-size: 0.8rem; font-weight: 600; color: var(--color-navy-900);">
                    <span>1. Profil Usaha</span>
                    <span>2. Lokasi & Narasi</span>
                    <span>3. Rekening & Operasional</span>
                    <span>4. Legalitas KYC</span>
                </div>
            </div>
        </section>

        <main class="turget-container" style="max-width: 800px; padding: 3rem 0 6rem;">
            <div class="turget-card" style="padding: 2.5rem; border-radius: var(--radius-2xl);">
                <!-- STEP 1: Profil Bisnis -->
                <div v-if="step === 1">
                    <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.5rem;">
                        🏢 Langkah 1: Informasi Dasar Badan Usaha
                    </h2>

                    <div style="display: grid; gap: 1.25rem;">
                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nama Usaha / Brand *</label>
                            <input v-model="form.business_name" type="text" class="turget-input" placeholder="Contoh: Uluwatu Cliffside Villa & Suites" required />
                            <span v-if="form.errors.business_name" class="turget-form-error" style="color: var(--color-red-600); font-size: 0.82rem;">{{ form.errors.business_name }}</span>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Bentuk Entitas Bisnis *</label>
                                <select v-model="form.business_type" class="turget-input">
                                    <option value="individual">Perorangan / UMKM</option>
                                    <option value="cv">CV (Commanditaire Vennootschap)</option>
                                    <option value="pt">PT (Perseroan Terbatas)</option>
                                    <option value="koperasi">Koperasi Wisata</option>
                                </select>
                            </div>

                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Kategori Layanan Utama *</label>
                                <select v-model="form.category" class="turget-input">
                                    <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                        {{ cat.icon }} {{ cat.label_id }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nama Lengkap Pemilik / Penanggung Jawab *</label>
                            <input v-model="form.owner_name" type="text" class="turget-input" placeholder="Nama sesuai KTP" required />
                            <span v-if="form.errors.owner_name" class="turget-form-error" style="color: var(--color-red-600); font-size: 0.82rem;">{{ form.errors.owner_name }}</span>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">No. WhatsApp Resmi Operasional *</label>
                                <input v-model="form.phone" type="tel" class="turget-input" placeholder="Contoh: 081234567890" required />
                                <span v-if="form.errors.phone" class="turget-form-error" style="color: var(--color-red-600); font-size: 0.82rem;">{{ form.errors.phone }}</span>
                            </div>

                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Email Bisnis *</label>
                                <input v-model="form.email" type="email" class="turget-input" placeholder="reservations@business.com" required />
                                <span v-if="form.errors.email" class="turget-form-error" style="color: var(--color-red-600); font-size: 0.82rem;">{{ form.errors.email }}</span>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                        <button
                            type="button"
                            class="turget-btn turget-btn-primary"
                            :disabled="!form.business_name || !form.owner_name || !form.phone || !form.email"
                            @click="step = 2"
                        >
                            Lanjut ke Lokasi & Narasi →
                        </button>
                    </div>
                </div>

                <!-- STEP 2: Lokasi & Narasi -->
                <div v-else-if="step === 2">
                    <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.5rem;">
                        📍 Langkah 2: Alamat, Koordinat GPS & Narasi
                    </h2>

                    <div style="display: grid; gap: 1.25rem;">
                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Provinsi / Wilayah Basis Operasional *</label>
                            <select v-model="form.region_id" class="turget-input" required>
                                <option v-for="r in regions" :key="r.id" :value="r.id">
                                    {{ r.parent ? `${r.parent.name} — ${r.name}` : r.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Alamat Lengkap Operasional *</label>
                            <textarea v-model="form.address" class="turget-input" rows="2" placeholder="Nama jalan, nomor, desa/kelurahan, kecamatan" required></textarea>
                            <span v-if="form.errors.address" class="turget-form-error" style="color: var(--color-red-600); font-size: 0.82rem;">{{ form.errors.address }}</span>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Latitude GPS (PRD §10) *</label>
                                <input v-model="form.latitude" type="number" step="0.0000001" class="turget-input" placeholder="-8.8291" required />
                            </div>
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Longitude GPS (PRD §10) *</label>
                                <input v-model="form.longitude" type="number" step="0.0000001" class="turget-input" placeholder="115.0849" required />
                            </div>
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Profil & Deskripsi Usaha (Bahasa Indonesia) *</label>
                            <textarea v-model="form.description.id" class="turget-input" rows="4" placeholder="Ceritakan keunggulan layanan, reputasi, fasilitas, dan armada Anda..." required></textarea>
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Profil & Deskripsi Usaha (English - Opsional)</label>
                            <textarea v-model="form.description.en" class="turget-input" rows="3" placeholder="Tell international travelers about your offerings and hospitality standards..."></textarea>
                        </div>
                    </div>

                    <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
                        <button type="button" class="turget-btn turget-btn-secondary" @click="step = 1">
                            ← Kembali
                        </button>
                        <button
                            type="button"
                            class="turget-btn turget-btn-primary"
                            :disabled="!form.address || !form.description.id"
                            @click="step = 3"
                        >
                            Lanjut ke Rekening & Kebijakan →
                        </button>
                    </div>
                </div>

                <!-- STEP 3: Rekening & Kebijakan -->
                <div v-else-if="step === 3">
                    <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.5rem;">
                        💳 Langkah 3: Rekening Pencairan & Kebijakan
                    </h2>

                    <div style="display: grid; gap: 1.25rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nama Bank *</label>
                                <input v-model="form.bank_name" type="text" class="turget-input" placeholder="BCA / Mandiri / BNI / BRI" required />
                            </div>
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nomor Rekening *</label>
                                <input v-model="form.bank_account_number" type="text" class="turget-input" placeholder="Contoh: 8830123456" required />
                            </div>
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Atas Nama Rekening *</label>
                                <input v-model="form.bank_account_holder" type="text" class="turget-input" placeholder="Nama Pemilik Rekening" required />
                            </div>
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Jam Operasional Layanan</label>
                            <input v-model="form.opening_hours.id" type="text" class="turget-input" placeholder="Setiap Hari, 08:00 - 20:00 WITA" />
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Kebijakan Pembatalan & Refund</label>
                            <textarea v-model="form.cancellation_policy.id" class="turget-input" rows="2" placeholder="Ketentuan refund jika tamu membatalkan jadwal..."></textarea>
                        </div>
                    </div>

                    <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
                        <button type="button" class="turget-btn turget-btn-secondary" @click="step = 2">
                            ← Kembali
                        </button>
                        <button
                            type="button"
                            class="turget-btn turget-btn-primary"
                            :disabled="!form.bank_account_number || !form.bank_account_holder"
                            @click="step = 4"
                        >
                            Lanjut ke Legalitas KYC →
                        </button>
                    </div>
                </div>

                <!-- STEP 4: Legalitas KYC -->
                <div v-else-if="step === 4">
                    <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        🛡️ Langkah 4: Dokumen Legalitas & Verifikasi (KYC)
                    </h2>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: 1.5rem;">
                        Turget.id mewajibkan verifikasi legalitas badan usaha/izin operasional untuk menjaga rasa aman wisatawan.
                    </p>

                    <div style="display: grid; gap: 1.25rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nomor NIB (Nomor Induk Berusaha)</label>
                                <input v-model="form.nib" type="text" class="turget-input" placeholder="13 digit nomor NIB OSS" />
                            </div>
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nomor NPWP Badan / Pemilik</label>
                                <input v-model="form.npwp" type="text" class="turget-input" placeholder="16 digit NPWP" />
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nomor KTP Penanggung Jawab *</label>
                                <input v-model="form.ktp_owner" type="text" class="turget-input" placeholder="16 digit NIK KTP" required />
                            </div>
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nomor Izin Operasional / SIUP</label>
                                <input v-model="form.siup" type="text" class="turget-input" placeholder="No. Izin Wisata / Transportasi" />
                            </div>
                        </div>

                        <div style="background: var(--color-blue-50); border: 1px solid rgba(37,99,235,0.2); padding: 1.25rem; border-radius: var(--radius-lg);">
                            <div style="font-weight: 700; color: var(--color-blue-900); margin-bottom: 0.35rem;">
                                🔒 Keamanan & Kerahasiaan Dokumen
                            </div>
                            <p style="font-size: 0.88rem; color: var(--color-navy-800); margin: 0;">
                                Dokumen legalitas Anda disimpan secara terenkripsi dan hanya diakses oleh Tim Compliance Turget.id untuk verifikasi status kemitraan.
                            </p>
                        </div>
                    </div>

                    <div style="margin-top: 2rem; display: flex; justify-content: space-between;">
                        <button type="button" class="turget-btn turget-btn-secondary" @click="step = 3">
                            ← Kembali
                        </button>
                        <button
                            type="button"
                            class="turget-btn turget-btn-primary"
                            :disabled="form.processing || !form.ktp_owner"
                            @click="submitOnboarding"
                        >
                            {{ form.processing ? 'Memproses Pendaftaran...' : 'Kirim Pengajuan Kemitraan 🚀' }}
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </PublicLayout>
</template>
