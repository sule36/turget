<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    vendor: Object,
    countries: Array,
    regions: Array,
});

const { t, tr, locale } = useI18n();

const form = useForm({
    business_name: props.vendor.business_name || '',
    owner_name: props.vendor.owner_name || '',
    phone: props.vendor.phone || '',
    email: props.vendor.email || '',
    region_id: props.vendor.region_id || props.regions[0]?.id,
    address: props.vendor.address || '',
    latitude: props.vendor.latitude || -8.8291,
    longitude: props.vendor.longitude || 115.0849,
    description: {
        id: props.vendor.description?.id || (typeof props.vendor.description === 'string' ? props.vendor.description : ''),
        en: props.vendor.description?.en || '',
    },
    avatar_url: props.vendor.avatar_url || '',
    cover_image_url: props.vendor.cover_image_url || '',
    bank_name: props.vendor.bank_name || '',
    bank_account_number: props.vendor.bank_account_number || '',
    bank_account_holder: props.vendor.bank_account_holder || '',
    opening_hours: {
        id: props.vendor.opening_hours?.id || 'Setiap Hari, 08:00 - 20:00 WITA',
        en: props.vendor.opening_hours?.en || 'Daily, 08:00 - 20:00 WITA',
    },
    cancellation_policy: {
        id: props.vendor.cancellation_policy?.id || '',
        en: props.vendor.cancellation_policy?.en || '',
    },
});

function submitProfile() {
    form.put('/vendor/profile');
}
</script>

<template>
    <PublicLayout>
        <Head title="Pengaturan Profil Vendor — Turget.id" />

        <section style="background: var(--color-surface-subtle); padding: 3rem 0 2rem; border-bottom: 1px solid var(--color-border-subtle);">
            <div class="turget-container">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <Link href="/vendor/dashboard" style="font-size: 0.88rem; color: var(--color-blue-600); text-decoration: none; margin-bottom: 0.5rem; display: inline-block;">
                            ← Kembali ke Dashboard
                        </Link>
                        <h1 style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: var(--color-navy-950); margin: 0;">
                            Pengaturan Profil & Operasional Usaha
                        </h1>
                    </div>
                    <button type="button" class="turget-btn turget-btn-primary" :disabled="form.processing" @click="submitProfile">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan 💾' }}
                    </button>
                </div>
            </div>
        </section>

        <main class="turget-container" style="max-width: 900px; padding: 3rem 0 6rem;">
            <form @submit.prevent="submitProfile" style="display: flex; flex-direction: column; gap: 2rem;">
                <!-- General Info Card -->
                <div class="turget-card" style="padding: 2rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        🏢 Data Utama Usaha
                    </h2>

                    <div style="display: grid; gap: 1.25rem;">
                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nama Usaha *</label>
                            <input v-model="form.business_name" type="text" class="turget-input" required />
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nama Pemilik / PIC *</label>
                                <input v-model="form.owner_name" type="text" class="turget-input" required />
                            </div>
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">No. WhatsApp *</label>
                                <input v-model="form.phone" type="tel" class="turget-input" required />
                            </div>
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Email Bisnis *</label>
                            <input v-model="form.email" type="email" class="turget-input" required />
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Deskripsi & Profil Usaha (ID) *</label>
                            <textarea v-model="form.description.id" class="turget-input" rows="4" required></textarea>
                        </div>
                    </div>
                </div>

                <!-- Location & GPS Card -->
                <div class="turget-card" style="padding: 2rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        📍 Lokasi & Titik GPS (Untuk Rekomendasi Destinasi)
                    </h2>

                    <div style="display: grid; gap: 1.25rem;">
                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Wilayah *</label>
                            <select v-model="form.region_id" class="turget-input" required>
                                <option v-for="r in regions" :key="r.id" :value="r.id">
                                    {{ r.parent ? `${r.parent.name} — ${r.name}` : r.name }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Alamat Lengkap *</label>
                            <textarea v-model="form.address" class="turget-input" rows="2" required></textarea>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Latitude GPS *</label>
                                <input v-model="form.latitude" type="number" step="0.0000001" class="turget-input" required />
                            </div>
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Longitude GPS *</label>
                                <input v-model="form.longitude" type="number" step="0.0000001" class="turget-input" required />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Financial & Operating Policies -->
                <div class="turget-card" style="padding: 2rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        💳 Rekening & Ketentuan Layanan
                    </h2>

                    <div style="display: grid; gap: 1.25rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Bank *</label>
                                <input v-model="form.bank_name" type="text" class="turget-input" required />
                            </div>
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nomor Rekening *</label>
                                <input v-model="form.bank_account_number" type="text" class="turget-input" required />
                            </div>
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Atas Nama *</label>
                                <input v-model="form.bank_account_holder" type="text" class="turget-input" required />
                            </div>
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Jam Buka / Layanan</label>
                            <input v-model="form.opening_hours.id" type="text" class="turget-input" />
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Kebijakan Pembatalan</label>
                            <textarea v-model="form.cancellation_policy.id" class="turget-input" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="turget-btn turget-btn-primary" :disabled="form.processing" style="padding: 0.75rem 2rem;">
                        {{ form.processing ? 'Menyimpan...' : 'Simpan Perubahan 💾' }}
                    </button>
                </div>
            </form>
        </main>
    </PublicLayout>
</template>
