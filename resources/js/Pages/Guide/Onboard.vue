<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    countries: Array,
    regions: Array,
    destinations: Array,
});

const { t, tr } = useI18n();

const step = ref(1); // 1: Profil & Area, 2: Narasi & Keahlian, 3: KYC & Payout

const form = useForm({
    display_name: '',
    phone: '',
    country_id: props.countries[0]?.id || 1,
    region_id: props.regions[0]?.id || null,
    service_areas: [],
    languages: ['id', 'en'],
    specializations: ['cultural'],
    experience_years: 3,
    price_per_day: 500000,
    tagline: {
        id: '',
        en: '',
    },
    bio: {
        id: '',
        en: '',
    },
    avatar_url: '/images/destinations/bali_hero.jpg',
    cover_image_url: '/images/destinations/bali_hero.jpg',
    destination_ids: [],
    // KYC
    id_card_number: '',
    id_card_file: '/storage/kyc/ktp_sample.jpg',
    guide_license_number: '',
    guide_license_file: '/storage/kyc/license_sample.jpg',
    // Payout
    bank_name: 'BCA',
    bank_account_number: '',
    bank_account_holder: '',
});

const availableLanguages = [
    { code: 'id', label: 'Bahasa Indonesia' },
    { code: 'en', label: 'English' },
    { code: 'ja', label: '日本語 (Japanese)' },
    { code: 'fr', label: 'Français (French)' },
    { code: 'nl', label: 'Nederlands (Dutch)' },
    { code: 'zh', label: '中文 (Chinese)' },
];

const availableSpecializations = [
    { code: 'cultural', label: 'Budaya & Sejarah' },
    { code: 'nature_adventure', label: 'Alam & Petualangan' },
    { code: 'photography', label: 'Fotografi' },
    { code: 'culinary', label: 'Kuliner Lokal' },
    { code: 'trekking', label: 'Trekking & Gunung' },
    { code: 'marine', label: 'Bahari & Snorkeling' },
];

function toggleLanguage(code) {
    const idx = form.languages.indexOf(code);
    if (idx === -1) {
        form.languages.push(code);
    } else if (form.languages.length > 1) {
        form.languages.splice(idx, 1);
    }
}

function toggleSpecialization(code) {
    const idx = form.specializations.indexOf(code);
    if (idx === -1) {
        form.specializations.push(code);
    } else if (form.specializations.length > 1) {
        form.specializations.splice(idx, 1);
    }
}

function submit() {
    form.post('/become-a-guide');
}
</script>

<template>
    <PublicLayout>
        <Head title="Pendaftaran Tour Guide — Turget.id" />

        <div class="turget-container" style="padding: 4rem 0 6rem; max-width: 800px;">
            <div style="text-align: center; margin-bottom: 2.5rem;">
                <span class="turget-badge" style="margin-bottom: 0.75rem; background: var(--color-blue-50); color: var(--color-blue-600);">
                    🧭 Mitra Pemandu Wisata
                </span>
                <h1 style="font-family: var(--font-serif); font-size: 2.4rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                    Bergabung sebagai Tour Guide Resmi Turget.id
                </h1>
                <p style="font-size: 1rem; color: var(--color-text-muted); max-width: 600px; margin: 0 auto;">
                    Dapatkan akses wisatawan nasional dan internasional, sistem pembayaran transparan, serta kebebasan mengatur paket dan tarif jasa Anda.
                </p>
            </div>

            <!-- Steps Header -->
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2.5rem; position: relative;">
                <div style="position: absolute; top: 18px; left: 10%; right: 10%; height: 2px; background: var(--color-border-subtle); z-index: 1;" />

                <div
                    v-for="s in [1, 2, 3]"
                    :key="s"
                    style="position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; gap: 0.4rem; background: white; padding: 0 0.5rem;"
                >
                    <div
                        :style="{
                            width: '36px', height: '36px', borderRadius: '50%',
                            display: 'flex', alignItems: 'center', justifyContent: 'center',
                            fontWeight: '700', fontSize: '0.9rem',
                            background: step >= s ? 'var(--color-blue-600)' : 'var(--color-surface-muted)',
                            color: step >= s ? 'white' : 'var(--color-text-muted)',
                            transition: 'all 0.2s',
                        }"
                    >
                        {{ s }}
                    </div>
                    <span :style="{ fontSize: '0.8rem', fontWeight: step === s ? '700' : '500', color: step === s ? 'var(--color-navy-950)' : 'var(--color-text-muted)' }">
                        {{ s === 1 ? 'Profil & Area' : (s === 2 ? 'Narasi & Keahlian' : 'Verifikasi KYC') }}
                    </span>
                </div>
            </div>

            <!-- Form Body -->
            <div class="turget-admin-card" style="box-shadow: var(--shadow-md); padding: 2.5rem;">
                <form @submit.prevent="submit">
                    <!-- STEP 1: Profil & Area Layanan -->
                    <div v-show="step === 1" style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                            1. Informasi Profil & Area Domisili
                        </h2>

                        <div>
                            <label class="turget-form-label">Nama Lengkap / Nama Profesional *</label>
                            <input
                                type="text"
                                v-model="form.display_name"
                                required
                                placeholder="cth: I Wayan Sukadana"
                                class="turget-form-input"
                            />
                            <div v-if="form.errors.display_name" class="turget-form-error">{{ form.errors.display_name }}</div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-form-label">Nomor WhatsApp Aktif *</label>
                                <input
                                    type="text"
                                    v-model="form.phone"
                                    required
                                    placeholder="+62 812-3456-7890"
                                    class="turget-form-input"
                                />
                                <div class="turget-form-hint">Hanya untuk verifikasi internal Turget.id, tidak ditampilkan ke publik.</div>
                            </div>
                            <div>
                                <label class="turget-form-label">Tahun Pengalaman Memandu *</label>
                                <input
                                    type="number"
                                    v-model="form.experience_years"
                                    min="0"
                                    required
                                    class="turget-form-input"
                                />
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-form-label">Negara Domisili *</label>
                                <select v-model="form.country_id" required class="turget-form-select">
                                    <option v-for="c in countries" :key="c.id" :value="c.id">
                                        {{ tr(c.name) }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="turget-form-label">Wilayah / Kota Tempat Tinggal *</label>
                                <select v-model="form.region_id" required class="turget-form-select">
                                    <option v-for="r in regions" :key="r.id" :value="r.id">
                                        {{ tr(r.name) }} ({{ r.parent ? tr(r.parent.name) : r.type }})
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="turget-form-label">Bahasa yang Dikuasai *</label>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.35rem;">
                                <button
                                    v-for="lang in availableLanguages"
                                    :key="lang.code"
                                    type="button"
                                    class="turget-pill"
                                    :class="{ 'is-active': form.languages.includes(lang.code) }"
                                    @click="toggleLanguage(lang.code)"
                                >
                                    {{ lang.label }}
                                </button>
                            </div>
                        </div>

                        <div>
                            <label class="turget-form-label">Spesialisasi Tur Wisata *</label>
                            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem; margin-top: 0.35rem;">
                                <button
                                    v-for="spec in availableSpecializations"
                                    :key="spec.code"
                                    type="button"
                                    class="turget-pill"
                                    :class="{ 'is-active': form.specializations.includes(spec.code) }"
                                    @click="toggleSpecialization(spec.code)"
                                >
                                    {{ spec.label }}
                                </button>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                            <button
                                type="button"
                                @click="step = 2"
                                :disabled="!form.display_name || !form.phone || !form.region_id"
                                class="turget-btn turget-btn-primary"
                            >
                                Lanjut: Narasi & Keahlian →
                            </button>
                        </div>
                    </div>

                    <!-- STEP 2: Narasi, Tagline & Bio -->
                    <div v-show="step === 2" style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                            2. Narasi Profil & Tarif Jasa
                        </h2>

                        <div>
                            <label class="turget-form-label">Tagline Profil Singkat (ID) *</label>
                            <input
                                type="text"
                                v-model="form.tagline.id"
                                required
                                placeholder="cth: Pemandu budaya Bali bersertifikat & penjelajah pura sakral"
                                class="turget-form-input"
                            />
                        </div>

                        <div>
                            <label class="turget-form-label">Cerita / Bio Diri & Pendekatan Wisata (ID) *</label>
                            <textarea
                                v-model="form.bio.id"
                                required
                                rows="5"
                                placeholder="Ceritakan latar belakang Anda, mengapa Anda bersemangat memandu wisatawan, dan pengalaman berkesan yang Anda tawarkan..."
                                class="turget-form-textarea"
                            />
                        </div>

                        <div>
                            <label class="turget-form-label">Tarif Dasar Jasa Pandu per Hari (IDR) *</label>
                            <input
                                type="number"
                                v-model="form.price_per_day"
                                min="100000"
                                step="50000"
                                required
                                class="turget-form-input"
                            />
                            <div class="turget-form-hint">Tarif jasa pribadi di luar tiket destinasi atau sewa kendaraan vendor.</div>
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                            <button type="button" @click="step = 1" class="turget-btn turget-btn-secondary">
                                ← Kembali
                            </button>
                            <button
                                type="button"
                                @click="step = 3"
                                :disabled="!form.tagline.id || !form.bio.id"
                                class="turget-btn turget-btn-primary"
                            >
                                Lanjut: Verifikasi Dokumen & Rekening →
                            </button>
                        </div>
                    </div>

                    <!-- STEP 3: Dokumen KYC & Rekening Payout -->
                    <div v-show="step === 3" style="display: flex; flex-direction: column; gap: 1.5rem;">
                        <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                            3. Verifikasi Identitas (KYC) & Rekening Payout
                        </h2>

                        <div style="background: var(--color-blue-50); border: 1px solid rgba(37,99,235,0.2); border-radius: var(--radius-md); padding: 1rem; font-size: 0.85rem; color: var(--color-navy-900);">
                            🔒 <strong>Keamanan Data & Privasi (PRD §7.1):</strong> Data identitas hanya diperiksa oleh tim kepatuhan Turget.id untuk mencegah penipuan dan menjamin keselamatan wisatawan. Nomor identitas dienkripsi secara aman.
                        </div>

                        <div>
                            <label class="turget-form-label">Nomor KTP / NIK / Paspor *</label>
                            <input
                                type="text"
                                v-model="form.id_card_number"
                                required
                                placeholder="3201xxxxxxxxxxxx"
                                class="turget-form-input"
                            />
                        </div>

                        <div>
                            <label class="turget-form-label">Nomor Lisensi Pemandu Wisata / HPI (Opsional)</label>
                            <input
                                type="text"
                                v-model="form.guide_license_number"
                                placeholder="HPI-BALI-12345"
                                class="turget-form-input"
                            />
                            <div class="turget-form-hint">Menambahkan nomor lisensi resmi mempercepat verifikasi lencana prioritas.</div>
                        </div>

                        <h3 style="font-size: 1.05rem; font-weight: 700; color: var(--color-navy-950); margin-top: 0.75rem;">
                            Rekening Pencairan Dana (Payout)
                        </h3>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-form-label">Nama Bank *</label>
                                <select v-model="form.bank_name" required class="turget-form-select">
                                    <option value="BCA">BCA (Bank Central Asia)</option>
                                    <option value="Mandiri">Bank Mandiri</option>
                                    <option value="BNI">BNI (Bank Negara Indonesia)</option>
                                    <option value="BRI">BRI (Bank Rakyat Indonesia)</option>
                                    <option value="BSI">BSI (Bank Syariah Indonesia)</option>
                                    <option value="CIMB">CIMB Niaga</option>
                                </select>
                            </div>
                            <div>
                                <label class="turget-form-label">Nomor Rekening *</label>
                                <input
                                    type="text"
                                    v-model="form.bank_account_number"
                                    required
                                    placeholder="1234567890"
                                    class="turget-form-input"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="turget-form-label">Nama Pemilik Rekening Sesuai Buku Tabungan *</label>
                            <input
                                type="text"
                                v-model="form.bank_account_holder"
                                required
                                placeholder="I Wayan Sukadana"
                                class="turget-form-input"
                            />
                        </div>

                        <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                            <button type="button" @click="step = 2" class="turget-btn turget-btn-secondary">
                                ← Kembali
                            </button>
                            <button
                                type="submit"
                                :disabled="form.processing || !form.id_card_number || !form.bank_account_number"
                                class="turget-btn turget-btn-primary"
                            >
                                {{ form.processing ? 'Mengirim Data...' : 'Kirim Pendaftaran & Ajukan Verifikasi ✓' }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </PublicLayout>
</template>
