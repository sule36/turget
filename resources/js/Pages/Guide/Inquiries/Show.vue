<script setup>
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    guide: Object,
    trip: Object,
    existingProposal: Object,
});

const { t, locale } = useI18n();

const form = useForm({
    title: `Penawaran Eksplorasi Bersama ${props.guide?.display_name || 'Guide Lokal'}`,
    cover_letter: '',
    itinerary: Array.from({ length: props.trip.duration_days || 2 }, (_, i) => ({
        day: i + 1,
        title: `Hari ke-${i + 1}: Eksplorasi Destinasi`,
        description: '',
    })),
    price_breakdown: {
        guide_fee: 600000 * (props.trip.duration_days || 1),
        transport: 500000 * (props.trip.duration_days || 1),
        tickets_and_activities: 250000 * (props.trip.adults_count || 1),
        meals_and_others: 200000,
    },
    total_amount: 0,
    valid_until: new Date(Date.now() + 7 * 24 * 60 * 60 * 1000).toISOString().split('T')[0],
});

const computedTotal = computed(() => {
    const pb = form.price_breakdown;
    return Number(pb.guide_fee || 0) + Number(pb.transport || 0) + Number(pb.tickets_and_activities || 0) + Number(pb.meals_and_others || 0);
});

// Sync total_amount
form.total_amount = computedTotal.value;

function addDay() {
    form.itinerary.push({
        day: form.itinerary.length + 1,
        title: `Hari ke-${form.itinerary.length + 1}: Aktivitas Tambahan`,
        description: '',
    });
}

function removeDay(index) {
    if (form.itinerary.length > 1) {
        form.itinerary.splice(index, 1);
        form.itinerary.forEach((d, i) => d.day = i + 1);
    }
}

function submitProposal() {
    form.total_amount = computedTotal.value;
    form.post(`/trips/${props.trip.id}/proposals`);
}

function startChat() {
    router.post('/chat/start', {
        target_user_id: props.trip.user_id,
        trip_inquiry_id: props.trip.id,
    });
}
</script>

<template>
    <PublicLayout>
        <Head :title="`Detail & Bidding: ${trip.title} — Portal Guide`" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container">
                <!-- Top Breadcrumb -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <Link href="/guide/inquiries" class="turget-btn turget-btn-secondary" style="font-size: 0.88rem;">
                        ← {{ locale === 'id' ? 'Kembali ke Bidding Board' : 'Back to Opportunities' }}
                    </Link>

                    <button type="button" @click="startChat" class="turget-btn turget-btn-secondary">
                        💬 {{ locale === 'id' ? 'Kirim Pesan / Chat Wisatawan' : 'Chat with Traveler' }}
                    </button>
                </div>

                <!-- Traveler Request Card -->
                <div style="background: white; border-radius: var(--radius-xl); padding: 2.5rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle); margin-bottom: 2.5rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 2rem; flex-wrap: wrap;">
                        <div>
                            <span class="turget-badge" style="background: var(--color-sand-100); color: var(--color-navy-900); font-weight: 700; margin-bottom: 0.5rem;">
                                📍 {{ trip.region?.name || 'Indonesia' }}
                            </span>
                            <h1 style="font-family: var(--font-serif); font-size: 2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                                {{ trip.title }}
                            </h1>
                            <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; color: var(--color-text-muted); font-size: 0.95rem;">
                                <span>👤 Wisatawan: <strong>{{ trip.user?.name }}</strong></span>
                                <span>📅 <strong>{{ trip.start_date }}</strong> s/d <strong>{{ trip.end_date }}</strong> ({{ trip.duration_days }} Hari)</span>
                                <span>👥 <strong>{{ trip.adults_count }} Dewasa</strong><span v-if="trip.children_count > 0">, {{ trip.children_count }} Anak</span></span>
                                <span>🧭 Gaya: <strong style="text-transform: capitalize;">{{ trip.travel_style }}</strong></span>
                            </div>
                        </div>

                        <div style="background: var(--color-surface-subtle); border-radius: var(--radius-lg); padding: 1.25rem 1.75rem; text-align: right; border: 1px solid var(--color-border-subtle);">
                            <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700;">
                                Ekspektasi Anggaran Wisatawan
                            </div>
                            <div style="font-size: 1.5rem; font-weight: 800; color: var(--color-blue-600); margin-top: 0.25rem;">
                                Rp {{ Number(trip.budget_min || 0).toLocaleString('id-ID') }} – {{ Number(trip.budget_max || 0).toLocaleString('id-ID') }}
                            </div>
                        </div>
                    </div>

                    <div v-if="trip.special_requests" style="margin-top: 1.5rem; padding-top: 1.25rem; border-top: 1px solid var(--color-border-subtle); font-size: 0.92rem; color: var(--color-text-main);">
                        <strong>Catatan Khusus Wisatawan:</strong>
                        <p style="color: var(--color-text-muted); margin-top: 0.25rem;">"{{ trip.special_requests }}"</p>
                    </div>
                </div>

                <!-- ALREADY SUBMITTED STATE -->
                <div v-if="existingProposal" style="background: white; border-radius: var(--radius-xl); padding: 2.5rem; box-shadow: var(--shadow-sm); border: 2px solid var(--color-blue-500);">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-navy-950);">
                            ✓ Anda Sudah Mengajukan Proposal untuk Trip Ini
                        </h2>
                        <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); font-weight: 700;">
                            Status: {{ existingProposal.status?.toUpperCase() }}
                        </span>
                    </div>

                    <div style="background: var(--color-surface-subtle); border-radius: var(--radius-lg); padding: 1.5rem; margin-bottom: 1.5rem;">
                        <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                            {{ existingProposal.title }}
                        </h3>
                        <p style="color: var(--color-text-main); font-size: 0.95rem; line-height: 1.6; margin-bottom: 1rem; white-space: pre-line;">
                            {{ existingProposal.cover_letter }}
                        </p>
                        <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-blue-600);">
                            Total Penawaran: Rp {{ Number(existingProposal.total_amount).toLocaleString('id-ID') }}
                        </div>
                    </div>

                    <p style="font-size: 0.9rem; color: var(--color-text-muted);">
                        Wisatawan sedang meninjau proposal Anda. Anda dapat berkomunikasi langsung melalui fitur Chat in-app Turget.id.
                    </p>
                </div>

                <!-- PROPOSAL BIDDING FORM -->
                <div v-else style="background: white; border-radius: var(--radius-xl); padding: 2.5rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle);">
                    <div style="margin-bottom: 2rem;">
                        <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); font-weight: 700; margin-bottom: 0.5rem;">
                            ✍️ Form Penawaran Resmi
                        </span>
                        <h2 style="font-family: var(--font-serif); font-size: 1.75rem; font-weight: 700; color: var(--color-navy-950);">
                            Kirimkan Proposal & Estimasi Biaya
                        </h2>
                        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                            Susun rencana perjalanan yang memikat dan transparan. Wisatawan akan membandingkan proposal Anda dengan pemandu lain.
                        </p>
                    </div>

                    <form @submit.prevent="submitProposal" style="display: flex; flex-direction: column; gap: 2rem;">
                        <!-- Proposal Title -->
                        <div>
                            <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                Judul Proposal / Paket Penawaran *
                            </label>
                            <input
                                v-model="form.title"
                                type="text"
                                class="turget-input"
                                placeholder="Contoh: Eksplorasi Budaya & Hidden Gems Bali Timur 3D2N Eksklusif"
                                required
                            />
                        </div>

                        <!-- Cover Letter -->
                        <div>
                            <label class="turget-label" style="font-weight: 700; margin-bottom: 0.5rem; display: block;">
                                Surat Pengantar & Alasan Memilih Anda (Cover Letter) *
                            </label>
                            <textarea
                                v-model="form.cover_letter"
                                rows="5"
                                class="turget-input"
                                placeholder="Jelaskan pengalaman Anda di destinasi ini, keistimewaan pelayanan Anda, flexibilitas jadwal, dan keramahan lokal..."
                                required
                            ></textarea>
                        </div>

                        <!-- Multi-day Itinerary -->
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                                <label class="turget-label" style="font-weight: 700; margin: 0;">
                                    🗺️ Rencana Hari demi Hari (Itinerary)
                                </label>
                                <button type="button" @click="addDay" class="turget-btn turget-btn-secondary turget-btn-sm">
                                    + Tambah Hari
                                </button>
                            </div>

                            <div style="display: flex; flex-direction: column; gap: 1rem;">
                                <div
                                    v-for="(day, idx) in form.itinerary"
                                    :key="idx"
                                    style="background: var(--color-surface-subtle); border-radius: var(--radius-lg); padding: 1.25rem; border: 1px solid var(--color-border-subtle);"
                                >
                                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                                        <span style="font-weight: 700; color: var(--color-blue-600); font-size: 0.95rem;">
                                            Hari {{ day.day }}
                                        </span>
                                        <button v-if="form.itinerary.length > 1" type="button" @click="removeDay(idx)" style="background: none; border: none; color: var(--color-coral-600); cursor: pointer; font-size: 0.85rem;">
                                            Hapus Hari
                                        </button>
                                    </div>

                                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                        <input
                                            v-model="day.title"
                                            type="text"
                                            class="turget-input"
                                            placeholder="Tema Hari (misal: Kedatangan & Sunset di Pantai Melasti)"
                                        />
                                        <textarea
                                            v-model="day.description"
                                            rows="2"
                                            class="turget-input"
                                            placeholder="Rincian jam, aktivitas, dan destinasi yang dikunjungi..."
                                        ></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Itemized Price Breakdown -->
                        <div>
                            <label class="turget-label" style="font-weight: 700; margin-bottom: 0.75rem; display: block;">
                                💵 Rincian Komponen Biaya (Price Breakdown)
                            </label>

                            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.25rem; background: var(--color-surface-subtle); padding: 1.5rem; border-radius: var(--radius-lg); border: 1px solid var(--color-border-subtle);">
                                <div>
                                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem;">
                                        Honor Guide (Jasa Pemandu)
                                    </label>
                                    <input v-model="form.price_breakdown.guide_fee" type="number" step="50000" class="turget-input" />
                                </div>
                                <div>
                                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem;">
                                        Transportasi / BBM / Driver
                                    </label>
                                    <input v-model="form.price_breakdown.transport" type="number" step="50000" class="turget-input" />
                                </div>
                                <div>
                                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem;">
                                        Tiket Masuk & Aktivitas
                                    </label>
                                    <input v-model="form.price_breakdown.tickets_and_activities" type="number" step="25000" class="turget-input" />
                                </div>
                                <div>
                                    <label style="font-size: 0.85rem; font-weight: 700; display: block; margin-bottom: 0.35rem;">
                                        Makan / Mineral / Lainnya
                                    </label>
                                    <input v-model="form.price_breakdown.meals_and_others" type="number" step="25000" class="turget-input" />
                                </div>
                            </div>
                        </div>

                        <!-- Commission & Earnings Callout -->
                        <div style="background: #F0FDF4; border: 1px solid var(--color-emerald-500); border-radius: var(--radius-lg); padding: 1.5rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;">
                            <div>
                                <div style="font-size: 0.85rem; color: var(--color-emerald-600); font-weight: 700;">
                                    TOTAL PENAWARAN KE WISATAWAN:
                                </div>
                                <div style="font-size: 1.85rem; font-weight: 800; color: var(--color-navy-950);">
                                    Rp {{ computedTotal.toLocaleString('id-ID') }}
                                </div>
                                <div style="font-size: 0.82rem; color: var(--color-text-muted); margin-top: 0.25rem;">
                                    Komisi Platform Turget.id (10%): Rp {{ (computedTotal * 0.10).toLocaleString('id-ID') }}
                                </div>
                            </div>

                            <div style="text-align: right;">
                                <div style="font-size: 0.85rem; color: var(--color-text-muted); font-weight: 700;">
                                    ESTIMASI PENGHASILAN BERSIH ANDA:
                                </div>
                                <div style="font-size: 1.85rem; font-weight: 800; color: var(--color-emerald-600);">
                                    Rp {{ (computedTotal * 0.90).toLocaleString('id-ID') }}
                                </div>
                                <div style="font-size: 0.82rem; color: var(--color-emerald-600); font-weight: 600;">
                                    🛡️ Dicairkan otomatis via Escrow pasca-trip
                                </div>
                            </div>
                        </div>

                        <div style="display: flex; justify-content: flex-end;">
                            <button
                                type="submit"
                                class="turget-btn turget-btn-primary"
                                style="font-size: 1.05rem; padding: 0.85rem 2rem;"
                                :disabled="form.processing"
                            >
                                🚀 {{ form.processing ? 'Mengirim Proposal...' : 'Kirim Proposal Penawaran Sekarang' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
