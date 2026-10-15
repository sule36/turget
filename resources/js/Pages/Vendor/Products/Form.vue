<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    vendor: Object,
    product: Object,
    categories: Array,
    destinations: Array,
    priceUnits: Array,
    bookingModels: Array,
});

const { t, tr, locale } = useI18n();

const isEditing = !!props.product;

const inclusionsText = ref(
    props.product?.inclusions?.id ? props.product.inclusions.id.join('\n') : (Array.isArray(props.product?.inclusions) ? props.product.inclusions.join('\n') : '')
);
const exclusionsText = ref(
    props.product?.exclusions?.id ? props.product.exclusions.id.join('\n') : (Array.isArray(props.product?.exclusions) ? props.product.exclusions.join('\n') : '')
);

const form = useForm({
    name: {
        id: props.product?.name?.id || (typeof props.product?.name === 'string' ? props.product.name : ''),
        en: props.product?.name?.en || '',
    },
    description: {
        id: props.product?.description?.id || (typeof props.product?.description === 'string' ? props.product.description : ''),
        en: props.product?.description?.en || '',
    },
    category: props.product?.category?.value || props.product?.category || props.vendor.category?.value || props.vendor.category || 'accommodation',
    destination_id: props.product?.destination_id || null,
    price: props.product?.price || 500000,
    price_unit: props.product?.price_unit || 'per_day',
    capacity: props.product?.capacity || 2,
    stock_quantity: props.product?.stock_quantity || 1,
    booking_model: props.product?.booking_model || 'instant',
    inclusions: { id: [] },
    exclusions: { id: [] },
    terms: {
        id: props.product?.terms?.id || '',
        en: props.product?.terms?.en || '',
    },
    cover_image: props.product?.cover_image || 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=1000&auto=format&fit=crop&q=80',
    latitude: props.product?.latitude || props.vendor.latitude,
    longitude: props.product?.longitude || props.vendor.longitude,
    address: props.product?.address || props.vendor.address,
    is_published: props.product ? props.product.status === 'published' : true,
});

function submit() {
    form.inclusions = {
        id: inclusionsText.value.split('\n').map(s => s.trim()).filter(Boolean),
    };
    form.exclusions = {
        id: exclusionsText.value.split('\n').map(s => s.trim()).filter(Boolean),
    };

    if (isEditing) {
        form.put(`/vendor/products/${props.product.id}`);
    } else {
        form.post('/vendor/products');
    }
}
</script>

<template>
    <PublicLayout>
        <Head :title="isEditing ? 'Edit Layanan — Turget.id' : 'Tambah Layanan Baru — Turget.id'" />

        <section style="background: var(--color-surface-subtle); padding: 3rem 0 2rem; border-bottom: 1px solid var(--color-border-subtle);">
            <div class="turget-container">
                <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <Link href="/vendor/products" style="font-size: 0.88rem; color: var(--color-blue-600); text-decoration: none; margin-bottom: 0.5rem; display: inline-block;">
                            ← Kembali ke Daftar Layanan
                        </Link>
                        <h1 style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: var(--color-navy-950); margin: 0;">
                            {{ isEditing ? 'Edit Layanan / Add-on' : 'Tambah Layanan / Add-on Baru' }}
                        </h1>
                    </div>

                    <button type="button" class="turget-btn turget-btn-primary" :disabled="form.processing" @click="submit">
                        {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan 💾' : 'Publikasikan Layanan 🚀') }}
                    </button>
                </div>
            </div>
        </section>

        <main class="turget-container" style="max-width: 900px; padding: 3rem 0 6rem;">
            <form @submit.prevent="submit" style="display: flex; flex-direction: column; gap: 2rem;">
                <!-- Main Title & Categorization -->
                <div class="turget-card" style="padding: 2rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        🏷️ Informasi & Klasifikasi Layanan
                    </h2>

                    <div style="display: grid; gap: 1.25rem;">
                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nama Layanan / Unit (Bahasa Indonesia) *</label>
                            <input v-model="form.name.id" type="text" class="turget-input" placeholder="Contoh: Deluxe Oceanview Villa with Private Pool" required />
                            <span v-if="form.errors['name.id']" class="turget-form-error" style="color: var(--color-red-600); font-size: 0.82rem;">{{ form.errors['name.id'] }}</span>
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Nama Layanan (English - Opsional)</label>
                            <input v-model="form.name.en" type="text" class="turget-input" placeholder="Example: Deluxe Oceanview Villa with Private Pool" />
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Kategori Layanan *</label>
                                <select v-model="form.category" class="turget-input" required>
                                    <option v-for="cat in categories" :key="cat.value" :value="cat.value">
                                        {{ cat.icon }} {{ cat.label_id }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Destinasi Terdekat yang Dilayani (Opsional)</label>
                                <select v-model="form.destination_id" class="turget-input">
                                    <option :value="null">-- Tidak Terikat Destinasi Tertentu --</option>
                                    <option v-for="dest in destinations" :key="dest.id" :value="dest.id">
                                        📍 {{ tr(dest.name) }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Deskripsi Lengkap Layanan *</label>
                            <textarea v-model="form.description.id" class="turget-input" rows="4" placeholder="Jelaskan spesifikasi, fasilitas, keunggulan, serta panduan check-in/serah terima..." required></textarea>
                            <span v-if="form.errors['description.id']" class="turget-form-error" style="color: var(--color-red-600); font-size: 0.82rem;">{{ form.errors['description.id'] }}</span>
                        </div>
                    </div>
                </div>

                <!-- Pricing, Capacity & Booking Model -->
                <div class="turget-card" style="padding: 2rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        💰 Tarif, Kapasitas & Model Pemesanan
                    </h2>

                    <div style="display: grid; gap: 1.25rem;">
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Harga / Tarif Dasar (IDR) *</label>
                                <input v-model="form.price" type="number" step="1000" min="0" class="turget-input" required />
                                <span v-if="form.errors.price" class="turget-form-error" style="color: var(--color-red-600); font-size: 0.82rem;">{{ form.errors.price }}</span>
                            </div>

                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Satuan Tarif *</label>
                                <select v-model="form.price_unit" class="turget-input" required>
                                    <option v-for="unit in priceUnits" :key="unit.value" :value="unit.value">
                                        {{ unit.label_id }}
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 1.25rem;">
                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Model Pemesanan *</label>
                                <select v-model="form.booking_model" class="turget-input" required>
                                    <option v-for="model in bookingModels" :key="model.value" :value="model.value">
                                        {{ model.label_id }}
                                    </option>
                                </select>
                            </div>

                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Kapasitas Tamu (Pax)</label>
                                <input v-model="form.capacity" type="number" min="1" class="turget-input" placeholder="2" />
                            </div>

                            <div>
                                <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Stok / Unit Tersedia *</label>
                                <input v-model="form.stock_quantity" type="number" min="1" class="turget-input" required />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Inclusions & Exclusions -->
                <div class="turget-card" style="padding: 2rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        📋 Fasilitas & Kelengkapan (Satu per baris)
                    </h2>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;">
                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem; color: var(--color-green-700);">
                                ✅ Sudah Termasuk (Inclusions)
                            </label>
                            <textarea
                                v-model="inclusionsText"
                                class="turget-input"
                                rows="5"
                                placeholder="Sarapan pagi untuk 2 orang&#10;Private Infinity Pool&#10;WiFi berkecepatan tinggi&#10;Free airport shuttle"
                            ></textarea>
                        </div>

                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem; color: var(--color-red-600);">
                                ❌ Tidak Termasuk (Exclusions)
                            </label>
                            <textarea
                                v-model="exclusionsText"
                                class="turget-input"
                                rows="5"
                                placeholder="Pengeluaran pribadi & laundry&#10;Makanan di luar paket&#10;Biaya asuransi opsional"
                            ></textarea>
                        </div>
                    </div>
                </div>

                <!-- Photos & Publishing Status -->
                <div class="turget-card" style="padding: 2rem;">
                    <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        🖼️ Foto Cover & Status Publikasi
                    </h2>

                    <div style="display: grid; gap: 1.25rem;">
                        <div>
                            <label class="turget-label" style="display: block; font-weight: 600; margin-bottom: 0.4rem;">Foto Cover URL</label>
                            <input v-model="form.cover_image" type="url" class="turget-input" placeholder="https://..." />
                        </div>

                        <div style="display: flex; align-items: center; gap: 0.75rem; padding: 1rem; background: var(--color-surface-subtle); border-radius: var(--radius-lg);">
                            <input id="publish-switch" v-model="form.is_published" type="checkbox" style="width: 1.25rem; height: 1.25rem; cursor: pointer;" />
                            <label for="publish-switch" style="cursor: pointer; font-weight: 600; color: var(--color-navy-950);">
                                Publikasikan Layanan Ini Segera di Marketplace
                            </label>
                        </div>
                    </div>
                </div>

                <div style="display: flex; justify-content: flex-end; gap: 1rem;">
                    <Link href="/vendor/products" class="turget-btn turget-btn-secondary">
                        Batal
                    </Link>
                    <button type="submit" class="turget-btn turget-btn-primary" :disabled="form.processing" style="padding: 0.75rem 2.25rem;">
                        {{ form.processing ? 'Menyimpan...' : (isEditing ? 'Simpan Perubahan 💾' : 'Publikasikan Layanan 🚀') }}
                    </button>
                </div>
            </form>
        </main>
    </PublicLayout>
</template>
