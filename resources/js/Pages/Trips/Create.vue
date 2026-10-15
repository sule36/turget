<script setup>
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    regions: Array,
    destinations: Array,
    travelStyles: Array,
});

const { t, locale } = useI18n();

const currentStep = ref(1);

const form = useForm({
    title: '',
    region_id: props.regions?.[0]?.id || '',
    destination_id: '',
    start_date: '',
    end_date: '',
    duration_days: 1,
    adults_count: 2,
    children_count: 0,
    budget_min: 1500000,
    budget_max: 5000000,
    travel_style: 'cultural',
    preferences: {
        interests: [],
        transport: 'private_car',
        accommodation: 'hotel',
    },
    special_requests: '',
});

const availableDestinations = computed(() => {
    if (!form.region_id) return props.destinations || [];
    return (props.destinations || []).filter(d => d.region_id === Number(form.region_id));
});

function calculateDuration() {
    if (form.start_date && form.end_date) {
        const start = new Date(form.start_date);
        const end = new Date(form.end_date);
        const diffTime = end.getTime() - start.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
        form.duration_days = diffDays > 0 ? diffDays : 1;
    }
}

function toggleInterest(interest) {
    const idx = form.preferences.interests.indexOf(interest);
    if (idx > -1) {
        form.preferences.interests.splice(idx, 1);
    } else {
        form.preferences.interests.push(interest);
    }
}

const commonInterests = [
    { id: 'temple', label_id: 'Candi & Sejarah', label_en: 'Temples & History' },
    { id: 'trekking', label_id: 'Hiking & Air Terjun', label_en: 'Hiking & Waterfalls' },
    { id: 'beach', label_id: 'Pantai & Snorkeling', label_en: 'Beaches & Snorkeling' },
    { id: 'culinary', label_id: 'Kuliner Lokal Otentik', label_en: 'Local Street Food' },
    { id: 'sunset', label_id: 'Spot Sunset Terbaik', label_en: 'Best Sunset Spots' },
    { id: 'handicraft', label_id: 'Kerajinan Tradisional', label_en: 'Local Crafts' },
];

function submit() {
    if (!form.title) {
        const selectedRegion = props.regions.find(r => r.id === Number(form.region_id));
        form.title = `Trip ke ${selectedRegion?.name || 'Indonesia'} (${form.duration_days} Hari)`;
    }
    form.post('/trips/plan');
}
</script>

<template>
    <PublicLayout>
        <Head :title="locale === 'id' ? 'Rancang Trip Kustom — Turget.id' : 'Custom Trip Planner — Turget.id'" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container">
                <!-- Header Breadcrumb & Title -->
                <div style="text-align: center; max-width: 680px; margin: 0 auto 3rem;">
                    <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); margin-bottom: 0.75rem;">
                        ✨ {{ locale === 'id' ? 'Custom Trip Builder' : 'Custom Trip Builder' }}
                    </span>
                    <h1 style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                        {{ locale === 'id' ? 'Wujudkan Perjalanan Impian Anda' : 'Design Your Bespoke Journey' }}
                    </h1>
                    <p style="color: var(--color-text-muted); font-size: 1.05rem;">
                        {{ locale === 'id'
                            ? 'Isi rencana trip Anda dalam 3 langkah mudah. Tour guide lokal terverifikasi kami akan bersaing memberikan proposal & penawaran terbaik.'
                            : 'Specify your travel desires. Verified local tour guides will pitch personalized itineraries and competitive quotes.' }}
                    </p>
                </div>

                <!-- Wizard Progress Indicator -->
                <div style="display: flex; justify-content: center; margin-bottom: 3rem;">
                    <div style="display: flex; align-items: center; gap: 1rem; background: white; padding: 0.75rem 1.75rem; border-radius: var(--radius-pill); box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle);">
                        <button
                            type="button"
                            @click="currentStep = 1"
                            style="background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; font-size: 0.9rem;"
                            :style="{ color: currentStep === 1 ? 'var(--color-blue-600)' : 'var(--color-text-subtle)' }"
                        >
                            <span style="width: 1.6rem; height: 1.6rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;"
                                  :style="{ background: currentStep === 1 ? 'var(--color-blue-600)' : 'var(--color-surface-muted)', color: currentStep === 1 ? 'white' : 'var(--color-text-muted)' }">
                                1
                            </span>
                            {{ locale === 'id' ? 'Destinasi & Jadwal' : 'Where & When' }}
                        </button>

                        <span style="color: var(--color-border-hover);">—</span>

                        <button
                            type="button"
                            @click="currentStep = 2"
                            style="background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; font-size: 0.9rem;"
                            :style="{ color: currentStep === 2 ? 'var(--color-blue-600)' : 'var(--color-text-subtle)' }"
                        >
                            <span style="width: 1.6rem; height: 1.6rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;"
                                  :style="{ background: currentStep === 2 ? 'var(--color-blue-600)' : 'var(--color-surface-muted)', color: currentStep === 2 ? 'white' : 'var(--color-text-muted)' }">
                                2
                            </span>
                            {{ locale === 'id' ? 'Gaya & Aktivitas' : 'Style & Interests' }}
                        </button>

                        <span style="color: var(--color-border-hover);">—</span>

                        <button
                            type="button"
                            @click="currentStep = 3"
                            style="background: none; border: none; cursor: pointer; display: flex; align-items: center; gap: 0.5rem; font-weight: 700; font-size: 0.9rem;"
                            :style="{ color: currentStep === 3 ? 'var(--color-blue-600)' : 'var(--color-text-subtle)' }"
                        >
                            <span style="width: 1.6rem; height: 1.6rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.8rem;"
                                  :style="{ background: currentStep === 3 ? 'var(--color-blue-600)' : 'var(--color-surface-muted)', color: currentStep === 3 ? 'white' : 'var(--color-text-muted)' }">
                                3
                            </span>
                            {{ locale === 'id' ? 'Anggaran & Review' : 'Budget & Review' }}
                        </button>
                    </div>
                </div>

                <!-- Main Grid Layout -->
                <div style="display: grid; grid-template-columns: 1fr 360px; gap: 2.5rem; align-items: start;">
                    <!-- Step Forms -->
                    <div style="background: white; border-radius: var(--radius-xl); padding: 2.5rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle);">
                        
                        <!-- STEP 1: DESTINASI & JADWAL -->
                        <div v-show="currentStep === 1">
                            <h2 style="font-family: var(--font-serif); font-size: 1.5rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                                📍 {{ locale === 'id' ? 'Ke Mana Anda Ingin Bepergian?' : 'Where do you want to explore?' }}
                            </h2>
                            <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: 2rem;">
                                {{ locale === 'id' ? 'Pilih destinasi utama, tanggal keberangkatan, dan jumlah rombongan Anda.' : 'Select target region, travel dates, and group size.' }}
                            </p>

                            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                                <div>
                                    <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                        {{ locale === 'id' ? 'Judul Singkat Rencana Perjalanan' : 'Trip Title / Headline' }}
                                    </label>
                                    <input
                                        v-model="form.title"
                                        type="text"
                                        class="turget-input"
                                        :placeholder="locale === 'id' ? 'Contoh: Liburan Eksplorasi Budaya & Snorkeling 4 Hari di Bali' : 'e.g. 4-Day Cultural & Snorkeling Exploration in Bali'"
                                    />
                                    <span v-if="form.errors.title" style="color: var(--color-coral-600); font-size: 0.85rem;">{{ form.errors.title }}</span>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                                    <div>
                                        <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                            {{ locale === 'id' ? 'Wilayah / Provinsi Utama' : 'Main Region / Province' }} *
                                        </label>
                                        <select v-model="form.region_id" class="turget-input">
                                            <option v-for="r in regions" :key="r.id" :value="r.id">
                                                {{ r.name }}
                                            </option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                            {{ locale === 'id' ? 'Destinasi Tertentu (Opsional)' : 'Specific Destination (Optional)' }}
                                        </label>
                                        <select v-model="form.destination_id" class="turget-input">
                                            <option value="">{{ locale === 'id' ? '-- Semua Tempat / Jelajah Bebas --' : '-- All Destinations / Flexible --' }}</option>
                                            <option v-for="d in availableDestinations" :key="d.id" :value="d.id">
                                                {{ d.name }}
                                            </option>
                                        </select>
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr 120px; gap: 1.25rem;">
                                    <div>
                                        <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                            {{ locale === 'id' ? 'Tanggal Mulai' : 'Start Date' }} *
                                        </label>
                                        <input
                                            v-model="form.start_date"
                                            type="date"
                                            class="turget-input"
                                            @change="calculateDuration"
                                        />
                                    </div>
                                    <div>
                                        <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                            {{ locale === 'id' ? 'Tanggal Selesai' : 'End Date' }} *
                                        </label>
                                        <input
                                            v-model="form.end_date"
                                            type="date"
                                            class="turget-input"
                                            @change="calculateDuration"
                                        />
                                    </div>
                                    <div>
                                        <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                            {{ locale === 'id' ? 'Durasi' : 'Duration' }}
                                        </label>
                                        <input
                                            v-model="form.duration_days"
                                            type="number"
                                            min="1"
                                            max="30"
                                            class="turget-input"
                                            style="font-weight: 700; text-align: center;"
                                        />
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                                    <div>
                                        <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                            {{ locale === 'id' ? 'Dewasa (12+ thn)' : 'Adults (12+ yrs)' }} *
                                        </label>
                                        <input
                                            v-model="form.adults_count"
                                            type="number"
                                            min="1"
                                            max="50"
                                            class="turget-input"
                                        />
                                    </div>
                                    <div>
                                        <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                            {{ locale === 'id' ? 'Anak-anak (< 12 thn)' : 'Children (< 12 yrs)' }}
                                        </label>
                                        <input
                                            v-model="form.children_count"
                                            type="number"
                                            min="0"
                                            max="20"
                                            class="turget-input"
                                        />
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: flex-end; margin-top: 1rem;">
                                    <button
                                        type="button"
                                        class="turget-btn turget-btn-primary"
                                        @click="currentStep = 2"
                                    >
                                        {{ locale === 'id' ? 'Lanjut: Gaya Perjalanan →' : 'Next: Travel Style →' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2: GAYA & AKTIVITAS -->
                        <div v-show="currentStep === 2">
                            <h2 style="font-family: var(--font-serif); font-size: 1.5rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                                🧭 {{ locale === 'id' ? 'Bagaimana Karakter Perjalanan Anda?' : 'What Style Suits You Best?' }}
                            </h2>
                            <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: 2rem;">
                                {{ locale === 'id' ? 'Pilih gaya perjalanan utama agar tour guide menyusun ritme itinerary yang tepat.' : 'Help guides tailor the pace and focus of the itinerary.' }}
                            </p>

                            <!-- Style Cards Grid -->
                            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                                <div
                                    v-for="style in travelStyles"
                                    :key="style.value"
                                    @click="form.travel_style = style.value"
                                    style="border: 2px solid; border-radius: var(--radius-lg); padding: 1.25rem; cursor: pointer; transition: var(--transition-base);"
                                    :style="{
                                        borderColor: form.travel_style === style.value ? 'var(--color-blue-600)' : 'var(--color-border-subtle)',
                                        background: form.travel_style === style.value ? 'var(--color-blue-50)' : 'white'
                                    }"
                                >
                                    <div style="font-size: 2rem; margin-bottom: 0.5rem;">{{ style.icon }}</div>
                                    <div style="font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.25rem;">
                                        {{ locale === 'id' ? style.label_id : style.label_en }}
                                    </div>
                                    <span v-if="form.travel_style === style.value" style="font-size: 0.75rem; font-weight: 700; color: var(--color-blue-600);">
                                        ✓ {{ locale === 'id' ? 'Terpilih' : 'Selected' }}
                                    </span>
                                </div>
                            </div>

                            <!-- Specific Interests Tags -->
                            <div style="margin-bottom: 2rem;">
                                <label class="turget-label" style="font-weight: 700; margin-bottom: 0.75rem; display: block;">
                                    {{ locale === 'id' ? 'Pengalaman & Aktivitas yang Ingin Dirasakan' : 'Experiences & Specific Activities' }}
                                </label>
                                <div style="display: flex; flex-wrap: wrap; gap: 0.65rem;">
                                    <button
                                        v-for="item in commonInterests"
                                        :key="item.id"
                                        type="button"
                                        @click="toggleInterest(item.id)"
                                        style="padding: 0.5rem 1rem; border-radius: var(--radius-pill); font-size: 0.88rem; font-weight: 600; cursor: pointer; transition: var(--transition-base); border: 1px solid;"
                                        :style="{
                                            borderColor: form.preferences.interests.includes(item.id) ? 'var(--color-blue-600)' : 'var(--color-border-subtle)',
                                            background: form.preferences.interests.includes(item.id) ? 'var(--color-blue-600)' : 'var(--color-surface-subtle)',
                                            color: form.preferences.interests.includes(item.id) ? 'white' : 'var(--color-navy-900)'
                                        }"
                                    >
                                        {{ form.preferences.interests.includes(item.id) ? '✓ ' : '+ ' }}
                                        {{ locale === 'id' ? item.label_id : item.label_en }}
                                    </button>
                                </div>
                            </div>

                            <div style="display: flex; justify-content: space-between; margin-top: 2rem;">
                                <button
                                    type="button"
                                    class="turget-btn turget-btn-secondary"
                                    @click="currentStep = 1"
                                >
                                    ← {{ locale === 'id' ? 'Kembali' : 'Back' }}
                                </button>
                                <button
                                    type="button"
                                    class="turget-btn turget-btn-primary"
                                    @click="currentStep = 3"
                                >
                                    {{ locale === 'id' ? 'Lanjut: Anggaran & Kirim →' : 'Next: Budget & Submit →' }}
                                </button>
                            </div>
                        </div>

                        <!-- STEP 3: ANGGARAN & CATATAN -->
                        <div v-show="currentStep === 3">
                            <h2 style="font-family: var(--font-serif); font-size: 1.5rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                                💰 {{ locale === 'id' ? 'Ekspektasi Anggaran & Catatan Khusus' : 'Budget Expectation & Special Requests' }}
                            </h2>
                            <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-bottom: 2rem;">
                                {{ locale === 'id' ? 'Tentukan perkiraan anggaran agar proposal yang diajukan para tour guide realistis dan sesuai kemampuan.' : 'Define your target budget range to receive realistic bids.' }}
                            </p>

                            <div style="display: flex; flex-direction: column; gap: 1.5rem;">
                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                                    <div>
                                        <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                            {{ locale === 'id' ? 'Anggaran Minimal (IDR)' : 'Minimum Budget (IDR)' }}
                                        </label>
                                        <input
                                            v-model="form.budget_min"
                                            type="number"
                                            step="100000"
                                            class="turget-input"
                                        />
                                    </div>
                                    <div>
                                        <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                            {{ locale === 'id' ? 'Anggaran Maksimal (IDR)' : 'Maximum Budget (IDR)' }}
                                        </label>
                                        <input
                                            v-model="form.budget_max"
                                            type="number"
                                            step="100000"
                                            class="turget-input"
                                        />
                                    </div>
                                </div>

                                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem;">
                                    <div>
                                        <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                            {{ locale === 'id' ? 'Preferensi Transportasi' : 'Transport Preference' }}
                                        </label>
                                        <select v-model="form.preferences.transport" class="turget-input">
                                            <option value="private_car">{{ locale === 'id' ? 'Mobil Pribadi + Driver' : 'Private Car + Driver' }}</option>
                                            <option value="motorbike">{{ locale === 'id' ? 'Sewa Motor Mandiri' : 'Motorbike Rental' }}</option>
                                            <option value="boat">{{ locale === 'id' ? 'Kapal Cepat / Speedboat' : 'Speedboat / Cruise' }}</option>
                                            <option value="public">{{ locale === 'id' ? 'Transportasi Umum / Jalan Santai' : 'Public Transport / Walking' }}</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                            {{ locale === 'id' ? 'Preferensi Penginapan' : 'Accommodation Preference' }}
                                        </label>
                                        <select v-model="form.preferences.accommodation" class="turget-input">
                                            <option value="hotel">{{ locale === 'id' ? 'Hotel Berbintang (3-4 Star)' : 'Boutique Hotel (3-4 Star)' }}</option>
                                            <option value="villa">{{ locale === 'id' ? 'Private Villa dengan Kolam' : 'Private Villa with Pool' }}</option>
                                            <option value="homestay">{{ locale === 'id' ? 'Homestay Lokal Otentik' : 'Authentic Local Homestay' }}</option>
                                            <option value="none">{{ locale === 'id' ? 'Sudah Ada / Urus Sendiri' : 'Self Arranged / Not Needed' }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                        {{ locale === 'id' ? 'Permintaan Khusus / Pantangan Makanan / Catatan Penting' : 'Special Notes / Dietary / Accessibility Requests' }}
                                    </label>
                                    <textarea
                                        v-model="form.special_requests"
                                        rows="4"
                                        class="turget-input"
                                        :placeholder="locale === 'id' ? 'Contoh: Rombongan membawa lansia jadi mohon ritme santai, butuh makanan halal/vegetarian, pemandu harus fasih bahasa Inggris...' : 'e.g. Traveling with elderly so moderate pace, halal food required, guide fluent in English...'"
                                    ></textarea>
                                </div>

                                <div style="background: var(--color-blue-50); border-radius: var(--radius-lg); padding: 1.25rem; display: flex; gap: 1rem; align-items: flex-start;">
                                    <span style="font-size: 1.5rem;">🛡️</span>
                                    <div style="font-size: 0.88rem; color: var(--color-navy-900);">
                                        <strong>{{ locale === 'id' ? 'Jaminan Keamanan Escrow Turget.id' : 'Turget.id Escrow Protection' }}</strong>
                                        <p style="margin-top: 0.25rem; color: var(--color-text-muted);">
                                            {{ locale === 'id'
                                                ? 'Mengajukan rencana trip ini GRATIS tanpa biaya di muka. Pembayaran baru dilakukan setelah Anda memilih dan menyetujui proposal tour guide, dan dana Anda aman di escrow hingga trip selesai.'
                                                : 'Submitting a trip request is 100% free. You only pay after accepting a guide proposal, held safely in escrow.' }}
                                        </p>
                                    </div>
                                </div>

                                <div style="display: flex; justify-content: space-between; margin-top: 1rem;">
                                    <button
                                        type="button"
                                        class="turget-btn turget-btn-secondary"
                                        @click="currentStep = 2"
                                    >
                                        ← {{ locale === 'id' ? 'Kembali' : 'Back' }}
                                    </button>
                                    <button
                                        type="button"
                                        class="turget-btn turget-btn-primary"
                                        :disabled="form.processing"
                                        @click="submit"
                                    >
                                        🚀 {{ form.processing ? (locale === 'id' ? 'Memproses...' : 'Submitting...') : (locale === 'id' ? 'Terbitkan Rencana Trip & Buka Bidding' : 'Publish Trip & Open Bidding') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Live Summary Preview Sidebar -->
                    <div style="position: sticky; top: 6rem; background: white; border-radius: var(--radius-xl); padding: 1.75rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle);">
                        <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 0.75rem;">
                            📋 {{ locale === 'id' ? 'Ringkasan Rencana' : 'Trip Summary' }}
                        </h3>

                        <div style="display: flex; flex-direction: column; gap: 1rem; font-size: 0.92rem;">
                            <div>
                                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700; display: block;">
                                    {{ locale === 'id' ? 'Destinasi' : 'Destination' }}
                                </span>
                                <strong style="color: var(--color-navy-950);">
                                    {{ regions.find(r => r.id === Number(form.region_id))?.name || 'Indonesia' }}
                                </strong>
                            </div>

                            <div>
                                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700; display: block;">
                                    {{ locale === 'id' ? 'Durasi & Jadwal' : 'Dates & Duration' }}
                                </span>
                                <span style="color: var(--color-navy-950);">
                                    {{ form.start_date || 'Tgl Mulai' }} → {{ form.end_date || 'Tgl Selesai' }} ({{ form.duration_days }} Hari)
                                </span>
                            </div>

                            <div>
                                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700; display: block;">
                                    {{ locale === 'id' ? 'Rombongan' : 'Participants' }}
                                </span>
                                <span style="color: var(--color-navy-950);">
                                    {{ form.adults_count }} Dewasa<span v-if="form.children_count > 0">, {{ form.children_count }} Anak</span>
                                </span>
                            </div>

                            <div>
                                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700; display: block;">
                                    {{ locale === 'id' ? 'Gaya Liburan' : 'Travel Style' }}
                                </span>
                                <span class="turget-badge" style="background: var(--color-sand-100); color: var(--color-navy-900); font-weight: 700; margin: 0.25rem 0 0;">
                                    {{ travelStyles.find(s => s.value === form.travel_style)?.label_id || form.travel_style }}
                                </span>
                            </div>

                            <div>
                                <span style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700; display: block;">
                                    {{ locale === 'id' ? 'Target Budget' : 'Target Budget' }}
                                </span>
                                <strong style="color: var(--color-blue-600); font-size: 1.05rem;">
                                    Rp {{ (form.budget_min || 0).toLocaleString('id-ID') }} – Rp {{ (form.budget_max || 0).toLocaleString('id-ID') }}
                                </strong>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
