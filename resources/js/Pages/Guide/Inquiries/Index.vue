<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    guide: Object,
    inquiries: Object,
    regions: Array,
    filters: Object,
});

const { t, locale } = useI18n();

const selectedRegion = ref(props.filters?.region || '');
const selectedStyle = ref(props.filters?.style || '');

function applyFilter() {
    router.get('/guide/inquiries', {
        region: selectedRegion.value,
        style: selectedStyle.value,
    }, { preserveState: true });
}
</script>

<template>
    <PublicLayout>
        <Head :title="locale === 'id' ? 'Peluang Bidding Trip Wisatawan — Portal Guide' : 'Trip Opportunities & Bidding — Guide Portal'" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container">
                <!-- Top Breadcrumb & Navigation -->
                <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem; margin-bottom: 2.5rem;">
                    <div>
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.5rem;">
                            <Link href="/guide/dashboard" style="color: var(--color-text-subtle); text-decoration: none; font-size: 0.88rem;">
                                ← Portal Guide
                            </Link>
                            <span style="color: var(--color-text-subtle);">/</span>
                            <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); margin: 0;">
                                🎯 Bidding Board
                            </span>
                        </div>
                        <h1 style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: var(--color-navy-950);">
                            {{ locale === 'id' ? 'Peluang Trip Wisatawan' : 'Traveler Trip Inquiries' }}
                        </h1>
                        <p style="color: var(--color-text-muted); font-size: 1rem;">
                            {{ locale === 'id'
                                ? 'Temukan rencana perjalanan yang sesuai keahlian Anda, kirimkan proposal penawaran menarik, dan dapatkan order langsung.'
                                : 'Explore custom traveler requests matching your expertise and submit winning proposals.' }}
                        </p>
                    </div>

                    <Link href="/payouts" class="turget-btn turget-btn-secondary">
                        💰 {{ locale === 'id' ? 'Dompet Escrow & Payout' : 'Escrow Wallet & Payout' }}
                    </Link>
                </div>

                <!-- Filters -->
                <div style="background: white; border-radius: var(--radius-lg); padding: 1.25rem 1.75rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle); margin-bottom: 2rem; display: flex; flex-wrap: wrap; gap: 1.25rem; align-items: center;">
                    <div style="flex: 1; min-width: 220px;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: var(--color-navy-950); display: block; margin-bottom: 0.35rem;">
                            {{ locale === 'id' ? 'Wilayah Destinasi' : 'Region' }}
                        </label>
                        <select v-model="selectedRegion" class="turget-input" @change="applyFilter">
                            <option value="">{{ locale === 'id' ? 'Semua Wilayah' : 'All Regions' }}</option>
                            <option v-for="r in regions" :key="r.id" :value="r.id">{{ r.name }}</option>
                        </select>
                    </div>

                    <div style="flex: 1; min-width: 220px;">
                        <label style="font-size: 0.8rem; font-weight: 700; color: var(--color-navy-950); display: block; margin-bottom: 0.35rem;">
                            {{ locale === 'id' ? 'Gaya Liburan' : 'Travel Style' }}
                        </label>
                        <select v-model="selectedStyle" class="turget-input" @change="applyFilter">
                            <option value="">{{ locale === 'id' ? 'Semua Gaya' : 'All Styles' }}</option>
                            <option value="cultural">{{ locale === 'id' ? 'Budaya & Sejarah' : 'Culture & Heritage' }}</option>
                            <option value="adventure">{{ locale === 'id' ? 'Alam & Petualangan' : 'Nature & Adventure' }}</option>
                            <option value="culinary">{{ locale === 'id' ? 'Kuliner Otentik' : 'Food & Culinary' }}</option>
                            <option value="relaxed">{{ locale === 'id' ? 'Santai & Keluarga' : 'Relaxed & Family' }}</option>
                            <option value="photography">{{ locale === 'id' ? 'Fotografi' : 'Photography' }}</option>
                            <option value="luxury">{{ locale === 'id' ? 'Mewah' : 'Luxury' }}</option>
                        </select>
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="!inquiries.data?.length" style="background: white; border-radius: var(--radius-xl); padding: 5rem 2rem; text-align: center; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-sm);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🏖️</div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        {{ locale === 'id' ? 'Belum Ada Permintaan Trip yang Buka Bidding' : 'No Open Requests at the Moment' }}
                    </h3>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                        {{ locale === 'id' ? 'Cek kembali secara berkala saat wisatawan baru memposting rencana perjalanan mereka.' : 'Check back regularly as travelers submit new inquiries.' }}
                    </p>
                </div>

                <!-- Inquiries Cards Grid -->
                <div v-else style="display: grid; grid-template-columns: repeat(auto-fill, minmax(360px, 1fr)); gap: 1.75rem;">
                    <div
                        v-for="inquiry in inquiries.data"
                        :key="inquiry.id"
                        style="background: white; border-radius: var(--radius-xl); padding: 1.75rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle); display: flex; flex-direction: column; justify-content: space-between;"
                    >
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.75rem;">
                                <span class="turget-badge" style="background: var(--color-sand-100); color: var(--color-navy-900); font-size: 0.75rem; margin: 0;">
                                    📍 {{ inquiry.region?.name || 'Indonesia' }}
                                </span>
                                <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); font-size: 0.75rem; margin: 0;">
                                    {{ inquiry.proposals_count || 0 }} Proposal
                                </span>
                            </div>

                            <h3 style="font-size: 1.2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem; line-height: 1.4;">
                                {{ inquiry.title }}
                            </h3>

                            <div style="display: flex; flex-direction: column; gap: 0.4rem; font-size: 0.88rem; color: var(--color-text-muted); margin-bottom: 1.25rem;">
                                <div>📅 {{ inquiry.start_date }} s/d {{ inquiry.end_date }} ({{ inquiry.duration_days }} Hari)</div>
                                <div>👥 {{ inquiry.adults_count }} Dewasa<span v-if="inquiry.children_count > 0">, {{ inquiry.children_count }} Anak</span></div>
                                <div>🧭 Gaya: <strong style="color: var(--color-navy-950); text-transform: capitalize;">{{ inquiry.travel_style }}</strong></div>
                                <div>
                                    💰 Target Budget:
                                    <strong style="color: var(--color-blue-600);">
                                        Rp {{ Number(inquiry.budget_min || 0).toLocaleString('id-ID') }} – {{ Number(inquiry.budget_max || 0).toLocaleString('id-ID') }}
                                    </strong>
                                </div>
                            </div>
                        </div>

                        <div style="border-top: 1px solid var(--color-border-subtle); padding-top: 1rem; display: flex; justify-content: space-between; align-items: center;">
                            <span style="font-size: 0.82rem; color: var(--color-text-subtle);">
                                Wisatawan: {{ inquiry.user?.name?.split(' ')[0] }}
                            </span>
                            <Link :href="`/guide/inquiries/${inquiry.id}`" class="turget-btn turget-btn-primary turget-btn-sm">
                                {{ locale === 'id' ? 'Kirim Penawaran →' : 'Bid on Trip →' }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
