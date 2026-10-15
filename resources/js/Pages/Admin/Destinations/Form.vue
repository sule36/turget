<script setup>
import { ref, computed } from 'vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    destination: Object,
    countries: Array,
    regions: Array,
    categories: Array,
});

const { t, tr } = useI18n();

const isEdit = computed(() => !!props.destination?.id);
const activeTab = ref('id'); // 'id' or 'en'
const workflowNote = ref('');
const isSubmittingWorkflow = ref(false);

const form = useForm({
    country_id: props.destination?.country_id || props.countries[0]?.id || null,
    region_id: props.destination?.region_id || null,
    slug: props.destination?.slug || '',
    name: {
        id: props.destination?.name?.id || '',
        en: props.destination?.name?.en || '',
    },
    tagline: {
        id: props.destination?.tagline?.id || '',
        en: props.destination?.tagline?.en || '',
    },
    summary: {
        id: props.destination?.summary?.id || '',
        en: props.destination?.summary?.en || '',
    },
    address: props.destination?.address || '',
    latitude: props.destination?.latitude || null,
    longitude: props.destination?.longitude || null,
    opening_hours: {
        id: props.destination?.opening_hours?.id || '',
        en: props.destination?.opening_hours?.en || '',
    },
    ticket_info: {
        id: props.destination?.ticket_info?.id || '',
        en: props.destination?.ticket_info?.en || '',
    },
    visit_duration_minutes: props.destination?.visit_duration_minutes || 120,
    cover_image: props.destination?.cover_image || '/images/destinations/bali_hero.jpg',
    category_ids: props.destination?.category_ids || [],
});

const filteredRegions = computed(() => {
    if (!form.country_id) return [];
    return props.regions.filter(r => r.country_id === form.country_id);
});

function toggleCategory(catId) {
    const idx = form.category_ids.indexOf(catId);
    if (idx === -1) {
        form.category_ids.push(catId);
    } else {
        form.category_ids.splice(idx, 1);
    }
}

function submit() {
    if (isEdit.value) {
        form.put(`/admin/destinations/${props.destination.id}`);
    } else {
        form.post('/admin/destinations');
    }
}

function handleWorkflow(action) {
    if (!confirm(`Konfirmasi tindakan alur kerja: "${action}"?`)) return;

    isSubmittingWorkflow.value = true;
    router.post(`/admin/workflow/destination/${props.destination.id}`, {
        action: action,
        note: workflowNote.value,
    }, {
        preserveScroll: true,
        onFinish: () => {
            isSubmittingWorkflow.value = false;
            workflowNote.value = '';
        },
    });
}

const actionLabels = {
    submit: { label: 'Ajukan Review', class: 'turget-btn-primary' },
    approve: { label: 'Setujui Konten', class: 'turget-btn-primary' },
    request_changes: { label: 'Minta Revisi', class: 'turget-btn-secondary' },
    publish: { label: 'Terbitkan ke Publik', class: 'turget-btn-primary' },
    unpublish: { label: 'Tarik ke Draf', class: 'turget-btn-secondary' },
    archive: { label: 'Arsipkan', class: 'turget-btn-secondary' },
};
</script>

<template>
    <AdminLayout>
        <Head :title="(isEdit ? 'Edit: ' + tr(destination.name) : 'Tambah Destinasi') + ' — Turget.id Admin'" />

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <div>
                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                    <Link href="/admin/destinations" style="color: var(--color-blue-600); font-size: 0.88rem; font-weight: 600; text-decoration: none;">
                        ← Kembali ke Daftar Destinasi
                    </Link>
                </div>
                <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950);">
                    {{ isEdit ? 'Edit Destinasi: ' + tr(destination.name) : 'Tambah Destinasi Wisata Baru' }}
                </h1>
            </div>

            <div style="display: flex; gap: 0.75rem; align-items: center;">
                <a
                    v-if="isEdit && destination.status === 'published'"
                    :href="`/places/${destination.slug}`"
                    target="_blank"
                    class="turget-btn turget-btn-secondary"
                >
                    Lihat Publik ↗
                </a>
                <button
                    type="button"
                    @click="submit"
                    :disabled="form.processing"
                    class="turget-btn turget-btn-primary"
                >
                    {{ form.processing ? 'Menyimpan...' : (isEdit ? 'Simpan Perubahan' : 'Simpan Draf Destinasi') }}
                </button>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2.2fr 1fr; gap: 2rem; align-items: start;">
            <!-- Main Content Area -->
            <form @submit.prevent="submit" style="display: flex; flex-direction: column; gap: 2rem;">
                <!-- Bilingual Tabs -->
                <div class="turget-admin-card">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                        <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950);">
                            Informasi & Deskripsi Editorial
                        </h2>

                        <!-- Tab buttons -->
                        <div class="turget-tabs-nav" style="margin-bottom: 0;">
                            <button
                                type="button"
                                class="turget-tab-btn"
                                :class="{ 'is-active': activeTab === 'id' }"
                                @click="activeTab = 'id'"
                            >
                                🇮🇩 Bahasa Indonesia (Utama)
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

                    <!-- Indonesian Fields -->
                    <div v-show="activeTab === 'id'" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div>
                            <label class="turget-form-label">Nama Destinasi (ID) *</label>
                            <input
                                type="text"
                                v-model="form.name.id"
                                required
                                placeholder="cth: Pura Luhur Uluwatu"
                                class="turget-form-input"
                            />
                            <div v-if="form.errors['name.id']" class="turget-form-error">{{ form.errors['name.id'] }}</div>
                        </div>

                        <div>
                            <label class="turget-form-label">Tagline / Subjudul Singkat (ID)</label>
                            <input
                                type="text"
                                v-model="form.tagline.id"
                                placeholder="cth: Kuil megah di tebing karang 70 meter menghadap Samudra Hindia"
                                class="turget-form-input"
                            />
                        </div>

                        <div>
                            <label class="turget-form-label">Ringkasan Narasi & Sejarah (ID)</label>
                            <textarea
                                v-model="form.summary.id"
                                rows="6"
                                placeholder="Tuliskan sejarah, makna spiritual/kebudayaan, dan gambaran umum destinasi..."
                                class="turget-form-textarea"
                            />
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label class="turget-form-label">Jam Operasional (ID)</label>
                                <input
                                    type="text"
                                    v-model="form.opening_hours.id"
                                    placeholder="cth: Setiap Hari: 07.00 - 19.00 WITA"
                                    class="turget-form-input"
                                />
                            </div>
                            <div>
                                <label class="turget-form-label">Informasi Tiket Masuk (ID)</label>
                                <input
                                    type="text"
                                    v-model="form.ticket_info.id"
                                    placeholder="cth: Dewasa: Rp 50.000, Anak-anak: Rp 30.000"
                                    class="turget-form-input"
                                />
                            </div>
                        </div>
                    </div>

                    <!-- English Fields -->
                    <div v-show="activeTab === 'en'" style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div>
                            <label class="turget-form-label">Destination Name (EN)</label>
                            <input
                                type="text"
                                v-model="form.name.en"
                                placeholder="e.g. Uluwatu Temple"
                                class="turget-form-input"
                            />
                        </div>

                        <div>
                            <label class="turget-form-label">Tagline (EN)</label>
                            <input
                                type="text"
                                v-model="form.tagline.en"
                                placeholder="e.g. Majestic cliffside sanctuary 70 meters above the Indian Ocean"
                                class="turget-form-input"
                            />
                        </div>

                        <div>
                            <label class="turget-form-label">Summary & Cultural Context (EN)</label>
                            <textarea
                                v-model="form.summary.en"
                                rows="6"
                                placeholder="Write the history, cultural essence, and visitor overview in English..."
                                class="turget-form-textarea"
                            />
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                            <div>
                                <label class="turget-form-label">Opening Hours (EN)</label>
                                <input
                                    type="text"
                                    v-model="form.opening_hours.en"
                                    placeholder="e.g. Daily: 07:00 AM - 07:00 PM WITA"
                                    class="turget-form-input"
                                />
                            </div>
                            <div>
                                <label class="turget-form-label">Admission Fee (EN)</label>
                                <input
                                    type="text"
                                    v-model="form.ticket_info.en"
                                    placeholder="e.g. Adult: IDR 50,000, Child: IDR 30,000"
                                    class="turget-form-input"
                                />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Geolocation & Operational Meta -->
                <div class="turget-admin-card">
                    <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        Lokasi & Geospasial (Haversine Near Radius)
                    </h2>

                    <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                        <div>
                            <label class="turget-form-label">Alamat Lengkap</label>
                            <input
                                type="text"
                                v-model="form.address"
                                placeholder="Alamat jalan, desa, kecamatan..."
                                class="turget-form-input"
                            />
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1rem;">
                            <div>
                                <label class="turget-form-label">Latitude (Lintang)</label>
                                <input
                                    type="number"
                                    step="any"
                                    v-model="form.latitude"
                                    placeholder="-8.8291"
                                    class="turget-form-input"
                                />
                            </div>
                            <div>
                                <label class="turget-form-label">Longitude (Bujur)</label>
                                <input
                                    type="number"
                                    step="any"
                                    v-model="form.longitude"
                                    placeholder="115.0849"
                                    class="turget-form-input"
                                />
                            </div>
                            <div>
                                <label class="turget-form-label">Estimasi Durasi (Menit)</label>
                                <input
                                    type="number"
                                    v-model="form.visit_duration_minutes"
                                    placeholder="120"
                                    class="turget-form-input"
                                />
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <!-- Sidebar / Settings Column -->
            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                <!-- Workflow State Action Card (Edit Mode) -->
                <div v-if="isEdit" class="turget-admin-card" style="border-left: 4px solid var(--color-blue-600);">
                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase; margin-bottom: 0.35rem;">
                        Alur Status Editorial
                    </div>
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                        <span class="turget-status-badge" :class="`turget-status-${destination.status}`" style="font-size: 0.85rem; padding: 0.35rem 0.85rem;">
                            ● Status: {{ destination.status }}
                        </span>
                    </div>

                    <div v-if="destination.available_actions && destination.available_actions.length > 0">
                        <label class="turget-form-label">Catatan Alur Kerja (Opsional)</label>
                        <input
                            type="text"
                            v-model="workflowNote"
                            placeholder="Tulis alasan atau catatan review..."
                            class="turget-form-input"
                            style="margin-bottom: 0.75rem; font-size: 0.85rem;"
                        />

                        <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                            <button
                                v-for="action in destination.available_actions"
                                :key="action"
                                type="button"
                                @click="handleWorkflow(action)"
                                :disabled="isSubmittingWorkflow"
                                class="turget-btn"
                                :class="actionLabels[action]?.class || 'turget-btn-secondary'"
                                style="width: 100%; justify-content: center;"
                            >
                                {{ actionLabels[action]?.label || action }}
                            </button>
                        </div>
                    </div>
                    <div v-else style="font-size: 0.85rem; color: var(--color-text-muted);">
                        Tidak ada tindakan alur kerja yang tersedia pada status ini.
                    </div>
                </div>

                <!-- Hierarchy & URL Slug Card -->
                <div class="turget-admin-card">
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                        Hierarki Wilayah & URL
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 1rem;">
                        <div>
                            <label class="turget-form-label">Negara *</label>
                            <select v-model="form.country_id" class="turget-form-select">
                                <option v-for="c in countries" :key="c.id" :value="c.id">
                                    {{ tr(c.name) }}
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="turget-form-label">Wilayah / Kabupaten / Kota *</label>
                            <select v-model="form.region_id" required class="turget-form-select">
                                <option :value="null" disabled>Pilih Wilayah</option>
                                <option v-for="r in filteredRegions" :key="r.id" :value="r.id">
                                    {{ tr(r.name) }} ({{ r.parent ? tr(r.parent.name) : r.type }})
                                </option>
                            </select>
                            <div v-if="form.errors.region_id" class="turget-form-error">{{ form.errors.region_id }}</div>
                        </div>

                        <div>
                            <label class="turget-form-label">URL Slug</label>
                            <input
                                type="text"
                                v-model="form.slug"
                                placeholder="cth: pura-luhur-uluwatu"
                                class="turget-form-input"
                            />
                            <div class="turget-form-hint">Otomatis dialihkan 301 jika slug diubah.</div>
                            <div v-if="form.errors.slug" class="turget-form-error">{{ form.errors.slug }}</div>
                        </div>
                    </div>
                </div>

                <!-- Media & Cover Image -->
                <div class="turget-admin-card">
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                        Foto Sampul Utama
                    </h3>

                    <div>
                        <input
                            type="text"
                            v-model="form.cover_image"
                            placeholder="/images/destinations/uluwatu.jpg"
                            class="turget-form-input"
                            style="margin-bottom: 0.75rem;"
                        />
                        <div v-if="form.cover_image" style="border-radius: var(--radius-md); overflow: hidden; border: 1px solid var(--color-border-subtle); height: 140px;">
                            <img :src="form.cover_image" alt="Preview" style="width: 100%; height: 100%; object-fit: cover;" />
                        </div>
                    </div>
                </div>

                <!-- Categories Multi-Check -->
                <div class="turget-admin-card">
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                        Kategori Wisata
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 0.5rem; max-height: 220px; overflow-y: auto;">
                        <label
                            v-for="cat in categories"
                            :key="cat.id"
                            style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.88rem; cursor: pointer;"
                        >
                            <input
                                type="checkbox"
                                :checked="form.category_ids.includes(cat.id)"
                                @change="toggleCategory(cat.id)"
                                style="width: 16px; height: 16px; accent-color: var(--color-blue-600);"
                            />
                            <span>{{ tr(cat.name) }}</span>
                        </label>
                    </div>
                </div>

                <!-- Revisions History (Edit Mode) -->
                <div v-if="isEdit && destination.revisions && destination.revisions.length > 0" class="turget-admin-card">
                    <h3 style="font-size: 1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                        Riwayat Revisi ({{ destination.revisions.length }})
                    </h3>

                    <div style="display: flex; flex-direction: column; gap: 0.65rem; max-height: 200px; overflow-y: auto;">
                        <div
                            v-for="rev in destination.revisions"
                            :key="rev.id"
                            style="padding: 0.5rem; background: var(--color-surface-subtle); border-radius: var(--radius-sm); font-size: 0.8rem;"
                        >
                            <div style="display: flex; justify-content: space-between; font-weight: 600;">
                                <span>{{ rev.action }}</span>
                                <span style="color: var(--color-text-subtle);">{{ new Date(rev.created_at).toLocaleDateString() }}</span>
                            </div>
                            <div style="color: var(--color-text-muted);">
                                Oleh: {{ rev.user?.name || 'Sistem' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
