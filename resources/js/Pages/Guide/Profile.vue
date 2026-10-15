<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    guide: Object,
    countries: Array,
    regions: Array,
    destinations: Array,
});

const { t, tr } = useI18n();
const activeTab = ref('id');

const form = useForm({
    display_name: props.guide.display_name || '',
    phone: props.guide.phone || '',
    country_id: props.guide.country_id,
    region_id: props.guide.region_id,
    languages: props.guide.languages || ['id', 'en'],
    specializations: props.guide.specializations || ['cultural'],
    experience_years: props.guide.experience_years || 1,
    price_per_day: props.guide.price_per_day || 500000,
    tagline: {
        id: props.guide.tagline?.id || '',
        en: props.guide.tagline?.en || '',
    },
    bio: {
        id: props.guide.bio?.id || '',
        en: props.guide.bio?.en || '',
    },
    avatar_url: props.guide.avatar_url || '',
    cover_image_url: props.guide.cover_image_url || '',
    destination_ids: props.guide.destination_ids || [],
    bank_name: props.guide.bank_name || 'BCA',
    bank_account_number: props.guide.bank_account_number || '',
    bank_account_holder: props.guide.bank_account_holder || '',
});

function submit() {
    form.put('/guide/profile');
}
</script>

<template>
    <PublicLayout>
        <Head title="Edit Profil Tour Guide — Turget.id" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container" style="max-width: 900px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <div>
                        <Link href="/guide/dashboard" style="color: var(--color-blue-600); font-size: 0.88rem; font-weight: 600; text-decoration: none;">
                            ← Kembali ke Dashboard Guide
                        </Link>
                        <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950); margin-top: 0.25rem;">
                            Pengaturan Profil Tour Guide
                        </h1>
                    </div>

                    <button
                        type="button"
                        @click="submit"
                        :disabled="form.processing"
                        class="turget-btn turget-btn-primary"
                    >
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan' }}
                    </button>
                </div>

                <form @submit.prevent="submit" style="display: flex; flex-direction: column; gap: 2rem;">
                    <!-- Basic Information Card -->
                    <div class="turget-admin-card">
                        <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                            Informasi Profil Publik & Domisili
                        </h2>

                        <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <div>
                                <label class="turget-form-label">Nama Lengkap / Nama Profesional *</label>
                                <input type="text" v-model="form.display_name" required class="turget-form-input" />
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                                <div>
                                    <label class="turget-form-label">Nomor WhatsApp Aktif *</label>
                                    <input type="text" v-model="form.phone" required class="turget-form-input" />
                                </div>
                                <div>
                                    <label class="turget-form-label">Tarif Pandu per Hari (IDR) *</label>
                                    <input type="number" v-model="form.price_per_day" required class="turget-form-input" />
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                                <div>
                                    <label class="turget-form-label">Wilayah Domisili</label>
                                    <select v-model="form.region_id" required class="turget-form-select">
                                        <option v-for="r in regions" :key="r.id" :value="r.id">
                                            {{ tr(r.name) }}
                                        </option>
                                    </select>
                                </div>
                                <div>
                                    <label class="turget-form-label">Tahun Pengalaman</label>
                                    <input type="number" v-model="form.experience_years" min="0" class="turget-form-input" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bilingual Bio & Tagline -->
                    <div class="turget-admin-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                            <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950);">
                                Tagline & Cerita Perjalanan
                            </h2>

                            <div class="turget-tabs-nav" style="margin-bottom: 0;">
                                <button type="button" class="turget-tab-btn" :class="{ 'is-active': activeTab === 'id' }" @click="activeTab = 'id'">
                                    🇮🇩 Bahasa Indonesia
                                </button>
                                <button type="button" class="turget-tab-btn" :class="{ 'is-active': activeTab === 'en' }" @click="activeTab = 'en'">
                                    🇬🇧 English
                                </button>
                            </div>
                        </div>

                        <div v-show="activeTab === 'id'" style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <div>
                                <label class="turget-form-label">Tagline (ID) *</label>
                                <input type="text" v-model="form.tagline.id" required class="turget-form-input" />
                            </div>
                            <div>
                                <label class="turget-form-label">Biografi & Filosofi Panduan (ID) *</label>
                                <textarea v-model="form.bio.id" rows="5" required class="turget-form-textarea" />
                            </div>
                        </div>

                        <div v-show="activeTab === 'en'" style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <div>
                                <label class="turget-form-label">Tagline (EN)</label>
                                <input type="text" v-model="form.tagline.en" class="turget-form-input" />
                            </div>
                            <div>
                                <label class="turget-form-label">Biography (EN)</label>
                                <textarea v-model="form.bio.en" rows="5" class="turget-form-textarea" />
                            </div>
                        </div>
                    </div>

                    <!-- Bank Payout Info -->
                    <div class="turget-admin-card">
                        <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                            Informasi Rekening Pencairan Dana
                        </h2>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                            <div>
                                <label class="turget-form-label">Nama Bank *</label>
                                <input type="text" v-model="form.bank_name" required class="turget-form-input" />
                            </div>
                            <div>
                                <label class="turget-form-label">Nomor Rekening *</label>
                                <input type="text" v-model="form.bank_account_number" required class="turget-form-input" />
                            </div>
                        </div>

                        <div>
                            <label class="turget-form-label">Nama Pemilik Rekening *</label>
                            <input type="text" v-model="form.bank_account_holder" required class="turget-form-input" />
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </PublicLayout>
</template>
