<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    region: Object,
    countries: Array,
    parentRegions: Array,
    types: Array,
});

const { t, tr } = useI18n();

const isEdit = computed(() => !!props.region?.id);
const activeTab = ref('id');

const form = useForm({
    country_id: props.region?.country_id || props.countries[0]?.id || null,
    parent_id: props.region?.parent_id || null,
    type: props.region?.type || 'province',
    slug: props.region?.slug || '',
    name: {
        id: props.region?.name?.id || '',
        en: props.region?.name?.en || '',
    },
    tagline: {
        id: props.region?.tagline?.id || '',
        en: props.region?.tagline?.en || '',
    },
    summary: {
        id: props.region?.summary?.id || '',
        en: props.region?.summary?.en || '',
    },
    cover_image: props.region?.cover_image || '',
    latitude: props.region?.latitude || null,
    longitude: props.region?.longitude || null,
});

function submit() {
    if (isEdit.value) {
        form.put(`/admin/regions/${props.region.id}`);
    } else {
        form.post('/admin/regions');
    }
}
</script>

<template>
    <AdminLayout>
        <Head :title="(isEdit ? 'Edit: ' + tr(region.name) : 'Tambah Wilayah') + ' — Turget.id Admin'" />

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                    <Link href="/admin/regions" style="color: var(--color-blue-600); font-size: 0.88rem; font-weight: 600; text-decoration: none;">
                        ← Kembali ke Daftar Wilayah
                    </Link>
                </div>
                <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950);">
                    {{ isEdit ? 'Edit Wilayah: ' + tr(region.name) : 'Tambah Wilayah Geografis Baru' }}
                </h1>
            </div>

            <button
                type="button"
                @click="submit"
                :disabled="form.processing"
                class="turget-btn turget-btn-primary"
            >
                {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Simpan Wilayah') }}
            </button>
        </div>

        <form @submit.prevent="submit" style="display: grid; grid-template-columns: 2fr 1fr; gap: 2rem; align-items: start;">
            <!-- Main Content Form -->
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <div class="turget-admin-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.25rem;">
                        <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950);">
                            Informasi Wilayah
                        </h2>

                        <!-- Tabs -->
                        <div class="turget-tabs-nav" style="margin-bottom: 0;">
                            <button
                                type="button"
                                class="turget-tab-btn"
                                :class="{ 'is-active': activeTab === 'id' }"
                                @click="activeTab = 'id'"
                            >
                                🇮🇩 Bahasa Indonesia
                            </button>
                            <button
                                type="button"
                                class="turget-tab-btn"
                                :class="{ 'is-active': activeTab === 'en' }"
                                @click="activeTab = 'en'"
                            >
                                🇬🇧 English
                            </button>
                        </div>
                    </div>

                    <!-- ID Fields -->
                    <div v-show="activeTab === 'id'" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div>
                            <label class="turget-form-label">Nama Wilayah (ID) *</label>
                            <input
                                type="text"
                                v-model="form.name.id"
                                required
                                placeholder="cth: Bali atau Badung"
                                class="turget-form-input"
                            />
                            <div v-if="form.errors['name.id']" class="turget-form-error">{{ form.errors['name.id'] }}</div>
                        </div>

                        <div>
                            <label class="turget-form-label">Tagline (ID)</label>
                            <input
                                type="text"
                                v-model="form.tagline.id"
                                placeholder="cth: Pulau Dewata dengan perpaduan budaya luhur & pesisir tropis"
                                class="turget-form-input"
                            />
                        </div>

                        <div>
                            <label class="turget-form-label">Ringkasan / Gambaran Wilayah (ID)</label>
                            <textarea
                                v-model="form.summary.id"
                                rows="5"
                                placeholder="Tuliskan daya tarik utama dan karakteristik geografis wilayah..."
                                class="turget-form-textarea"
                            />
                        </div>
                    </div>

                    <!-- EN Fields -->
                    <div v-show="activeTab === 'en'" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div>
                            <label class="turget-form-label">Region Name (EN)</label>
                            <input
                                type="text"
                                v-model="form.name.en"
                                placeholder="e.g. Bali or Badung Regency"
                                class="turget-form-input"
                            />
                        </div>

                        <div>
                            <label class="turget-form-label">Tagline (EN)</label>
                            <input
                                type="text"
                                v-model="form.tagline.en"
                                placeholder="e.g. Island of the Gods with sacred culture & tropical coasts"
                                class="turget-form-input"
                            />
                        </div>

                        <div>
                            <label class="turget-form-label">Summary (EN)</label>
                            <textarea
                                v-model="form.summary.en"
                                rows="5"
                                placeholder="Describe the region's top attractions and character in English..."
                                class="turget-form-textarea"
                            />
                        </div>
                    </div>
                </div>

                <!-- Coordinates -->
                <div class="turget-admin-card">
                    <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        Koordinat Pusat Wilayah
                    </h2>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                        <div>
                            <label class="turget-form-label">Latitude</label>
                            <input
                                type="number"
                                step="any"
                                v-model="form.latitude"
                                placeholder="-8.4095"
                                class="turget-form-input"
                            />
                        </div>
                        <div>
                            <label class="turget-form-label">Longitude</label>
                            <input
                                type="number"
                                step="any"
                                v-model="form.longitude"
                                placeholder="115.1889"
                                class="turget-form-input"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Settings Sidebar -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <div class="turget-admin-card">
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                        Hierarki Geografis
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div>
                            <label class="turget-form-label">Negara *</label>
                            <select v-model="form.country_id" required class="turget-form-select">
                                <option v-for="c in countries" :key="c.id" :value="c.id">
                                    {{ tr(c.name) }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="turget-form-label">Wilayah Induk (Parent)</label>
                            <select v-model="form.parent_id" class="turget-form-select">
                                <option :value="null">Tidak ada (Jadikan Level 1 / Provinsi Utama)</option>
                                <option v-for="p in parentRegions" :key="p.id" :value="p.id">
                                    {{ tr(p.name) }}
                                </option>
                            </select>
                            <div class="turget-form-hint">
                                Kosongkan jika ini adalah Provinsi/Wilayah Utama.
                            </div>
                        </div>

                        <div>
                            <label class="turget-form-label">Tipe Wilayah *</label>
                            <select v-model="form.type" required class="turget-form-select">
                                <option v-for="t in types" :key="t" :value="t">
                                    {{ t }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="turget-form-label">URL Slug</label>
                            <input
                                type="text"
                                v-model="form.slug"
                                placeholder="cth: bali atau badung"
                                class="turget-form-input"
                            />
                            <div v-if="form.errors.slug" class="turget-form-error">{{ form.errors.slug }}</div>
                        </div>
                    </div>
                </div>

                <div class="turget-admin-card">
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                        Foto Sampul Wilayah
                    </h3>

                    <div>
                        <input
                            type="text"
                            v-model="form.cover_image"
                            placeholder="/images/destinations/bali_hero.jpg"
                            class="turget-form-input"
                            style="margin-bottom: 0.75rem;"
                        />
                        <div v-if="form.cover_image" style="border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--color-border-subtle); height: 140px;">
                            <img :src="form.cover_image" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;" />
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
