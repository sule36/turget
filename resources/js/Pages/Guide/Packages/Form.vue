<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    guide: Object,
    package: Object,
    destinations: Array,
});

const { t, tr } = useI18n();
const isEdit = computed(() => !!props.package?.id);
const activeTab = ref('id');

const form = useForm({
    destination_id: props.package?.destination_id || null,
    title: {
        id: props.package?.title?.id || '',
        en: props.package?.title?.en || '',
    },
    tagline: {
        id: props.package?.tagline?.id || '',
        en: props.package?.tagline?.en || '',
    },
    description: {
        id: props.package?.description?.id || '',
        en: props.package?.description?.en || '',
    },
    tour_type: props.package?.tour_type || 'cultural',
    price_type: props.package?.price_type || 'per_person',
    base_price: props.package?.base_price || 350000,
    duration_hours: props.package?.duration_hours || 8,
    duration_days: props.package?.duration_days || 1,
    min_participants: props.package?.min_participants || 1,
    max_participants: props.package?.max_participants || 10,
    meeting_point: props.package?.meeting_point || '',
    end_point: props.package?.end_point || '',
    cover_image: props.package?.cover_image || '/images/destinations/bali_hero.jpg',
    itinerary: props.package?.itinerary || [
        { time: '08:00 - 09:00', title: 'Penjemputan & Briefing', description: 'Bertemu di titik kumpul dan pengenalan rute' },
        { time: '09:00 - 12:00', title: 'Eksplorasi Destinasi Utama', description: 'Tur budaya mendalam bersama tour guide' },
        { time: '12:00 - 13:30', title: 'Makan Siang Kuliner Lokal', description: 'Menikmati hidangan otentik khas daerah' },
    ],
    inclusions: {
        id: props.package?.inclusions?.id || ['Jasa pemandu wisata berlisensi', 'Tiket masuk destinasi', 'Air mineral'],
        en: props.package?.inclusions?.en || ['Certified local tour guide service', 'Destination admission tickets', 'Mineral water'],
    },
    exclusions: {
        id: props.package?.exclusions?.id || ['Pengeluaran pribadi & belanja oleh-oleh', 'Tipping sukarela'],
        en: props.package?.exclusions?.en || ['Personal expenses & souvenirs', 'Voluntary tips'],
    },
    submit_for_review: false,
});

function addItineraryStep() {
    form.itinerary.push({ time: '', title: '', description: '' });
}

function removeItineraryStep(idx) {
    form.itinerary.splice(idx, 1);
}

function submit(submitReview = false) {
    form.submit_for_review = submitReview;
    if (isEdit.value) {
        form.put(`/guide/packages/${props.package.id}`);
    } else {
        form.post('/guide/packages');
    }
}
</script>

<template>
    <PublicLayout>
        <Head :title="(isEdit ? 'Edit Paket: ' + tr(package.title) : 'Buat Paket Wisata Baru') + ' — Turget.id'" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container" style="max-width: 960px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <div>
                        <Link href="/guide/packages" style="color: var(--color-blue-600); font-size: 0.88rem; font-weight: 600; text-decoration: none;">
                            ← Kembali ke Daftar Paket
                        </Link>
                        <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950); margin-top: 0.25rem;">
                            {{ isEdit ? 'Edit Paket Wisata: ' + tr(package.title) : 'Buat Paket Wisata Baru' }}
                        </h1>
                    </div>

                    <div style="display: flex; gap: 0.75rem;">
                        <button
                            type="button"
                            @click="submit(false)"
                            :disabled="form.processing"
                            class="turget-btn turget-btn-secondary"
                        >
                            Simpan Draf
                        </button>
                        <button
                            type="button"
                            @click="submit(true)"
                            :disabled="form.processing"
                            class="turget-btn turget-btn-primary"
                        >
                            Ajukan untuk Moderasi Publik →
                        </button>
                    </div>
                </div>

                <form @submit.prevent="submit(true)" style="display: flex; flex-direction: column; gap: 2rem;">
                    <!-- Bilingual Info -->
                    <div class="turget-admin-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                            <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950);">
                                Informasi & Judul Paket
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
                                <label class="turget-form-label">Nama Paket Wisata (ID) *</label>
                                <input type="text" v-model="form.title.id" required placeholder="cth: Tur Sunset Pura Uluwatu & Tari Kecak" class="turget-form-input" />
                            </div>
                            <div>
                                <label class="turget-form-label">Tagline Singkat (ID)</label>
                                <input type="text" v-model="form.tagline.id" placeholder="cth: Pengalaman sakral tari kecak di tebing Samudra Hindia" class="turget-form-input" />
                            </div>
                            <div>
                                <label class="turget-form-label">Deskripsi Lengkap Paket (ID) *</label>
                                <textarea v-model="form.description.id" rows="5" required placeholder="Tuliskan pengalaman yang akan didapatkan wisatawan secara mendalam..." class="turget-form-textarea" />
                            </div>
                        </div>

                        <div v-show="activeTab === 'en'" style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <div>
                                <label class="turget-form-label">Tour Package Title (EN)</label>
                                <input type="text" v-model="form.title.en" placeholder="e.g. Uluwatu Sunset Temple & Kecak Fire Dance Tour" class="turget-form-input" />
                            </div>
                            <div>
                                <label class="turget-form-label">Tagline (EN)</label>
                                <input type="text" v-model="form.tagline.en" class="turget-form-input" />
                            </div>
                            <div>
                                <label class="turget-form-label">Full Description (EN)</label>
                                <textarea v-model="form.description.en" rows="5" class="turget-form-textarea" />
                            </div>
                        </div>
                    </div>

                    <!-- Configuration & Pricing -->
                    <div class="turget-admin-card">
                        <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                            Konfigurasi Harga, Kapasitas & Durasi
                        </h2>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem; margin-bottom: 1.25rem;">
                            <div>
                                <label class="turget-form-label">Tipe Tur</label>
                                <select v-model="form.tour_type" class="turget-form-select">
                                    <option value="cultural">Budaya & Sejarah</option>
                                    <option value="nature_adventure">Alam & Petualangan</option>
                                    <option value="photography">Fotografi</option>
                                    <option value="culinary">Kuliner</option>
                                    <option value="private_city">Private City Tour</option>
                                </select>
                            </div>
                            <div>
                                <label class="turget-form-label">Skema Harga *</label>
                                <select v-model="form.price_type" class="turget-form-select">
                                    <option value="per_person">Per Orang (Per Pax)</option>
                                    <option value="per_group">Per Grup</option>
                                    <option value="per_day">Per Hari</option>
                                </select>
                            </div>
                            <div>
                                <label class="turget-form-label">Harga Dasar (IDR) *</label>
                                <input type="number" v-model="form.base_price" required min="10000" step="10000" class="turget-form-input" />
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-form-label">Durasi (Jam) *</label>
                                <input type="number" v-model="form.duration_hours" min="1" required class="turget-form-input" />
                            </div>
                            <div>
                                <label class="turget-form-label">Durasi (Hari)</label>
                                <input type="number" v-model="form.duration_days" min="1" class="turget-form-input" />
                            </div>
                            <div>
                                <label class="turget-form-label">Min Peserta</label>
                                <input type="number" v-model="form.min_participants" min="1" class="turget-form-input" />
                            </div>
                            <div>
                                <label class="turget-form-label">Maks Peserta</label>
                                <input type="number" v-model="form.max_participants" min="1" class="turget-form-input" />
                            </div>
                        </div>
                    </div>

                    <!-- Itinerary Builder -->
                    <div class="turget-admin-card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                            <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950);">
                                Jadwal Perjalanan (Itinerary Step-by-Step)
                            </h2>
                            <button type="button" @click="addItineraryStep" class="turget-btn turget-btn-secondary turget-btn-sm">
                                + Tambah Agenda
                            </button>
                        </div>

                        <div style="display: flex; flex-direction: column; gap: 1rem;">
                            <div
                                v-for="(step, sIdx) in form.itinerary"
                                :key="sIdx"
                                style="border: 1px solid var(--color-border-subtle); border-radius: var(--radius-md); padding: 1rem; background: var(--color-surface-subtle);"
                            >
                                <div style="display: flex; gap: 1rem; margin-bottom: 0.5rem;">
                                    <input
                                        type="text"
                                        v-model="step.time"
                                        placeholder="cth: 08:00 - 10:00"
                                        class="turget-form-input"
                                        style="width: 180px;"
                                    />
                                    <input
                                        type="text"
                                        v-model="step.title"
                                        placeholder="Judul agenda (cth: Penjemputan di Hotel)"
                                        class="turget-form-input"
                                        style="flex: 1;"
                                    />
                                    <button
                                        type="button"
                                        @click="removeItineraryStep(sIdx)"
                                        class="turget-btn turget-btn-secondary turget-btn-sm"
                                        style="color: var(--color-coral-600);"
                                    >
                                        ✕
                                    </button>
                                </div>
                                <input
                                    type="text"
                                    v-model="step.description"
                                    placeholder="Rincian aktivitas / tempat yang dikunjungi..."
                                    class="turget-form-input"
                                />
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </PublicLayout>
</template>
