<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    trips: Object,
});

const { t, locale } = useI18n();
</script>

<template>
    <PublicLayout>
        <Head :title="locale === 'id' ? 'Daftar Trip Saya — Turget.id' : 'My Trips — Turget.id'" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container">
                <!-- Top Header -->
                <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 1rem; margin-bottom: 2.5rem;">
                    <div>
                        <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); margin-bottom: 0.5rem;">
                            🧭 {{ locale === 'id' ? 'Kustom Perjalanan' : 'Bespoke Trips' }}
                        </span>
                        <h1 style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: var(--color-navy-950);">
                            {{ locale === 'id' ? 'Rencana Perjalanan Saya' : 'My Trip Inquiries' }}
                        </h1>
                        <p style="color: var(--color-text-muted); font-size: 1rem;">
                            {{ locale === 'id' ? 'Pantau proposal penawaran masuk dari para pemandu wisata terverifikasi.' : 'Track proposals and bids from verified local tour guides.' }}
                        </p>
                    </div>

                    <Link href="/trips/plan" class="turget-btn turget-btn-primary">
                        + {{ locale === 'id' ? 'Rancang Trip Baru' : 'Plan a New Trip' }}
                    </Link>
                </div>

                <!-- Empty State -->
                <div v-if="!trips.data?.length" style="background: white; border-radius: var(--radius-xl); padding: 5rem 2rem; text-align: center; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-sm);">
                    <div style="font-size: 3.5rem; margin-bottom: 1rem;">🗺️</div>
                    <h3 style="font-size: 1.35rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        {{ locale === 'id' ? 'Belum Ada Rencana Trip' : 'No Trip Inquiries Yet' }}
                    </h3>
                    <p style="color: var(--color-text-muted); max-width: 480px; margin: 0 auto 2rem; font-size: 0.95rem;">
                        {{ locale === 'id'
                            ? 'Buat rencana trip kustom Anda sekarang! Anda bisa menentukan destinasi, tanggal, anggaran, dan memilih proposal terbaik.'
                            : 'Create your first custom trip plan now. Verified tour guides will pitch itineraries tailored to your budget.' }}
                    </p>
                    <Link href="/trips/plan" class="turget-btn turget-btn-primary">
                        {{ locale === 'id' ? 'Mulai Rancang Sekarang' : 'Start Planning Now' }}
                    </Link>
                </div>

                <!-- Trips List -->
                <div v-else style="display: flex; flex-direction: column; gap: 1.5rem;">
                    <div
                        v-for="trip in trips.data"
                        :key="trip.id"
                        style="background: white; border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle); display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1.5rem;"
                    >
                        <div style="max-width: 680px;">
                            <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem;">
                                <span class="turget-badge" style="background: var(--color-sand-100); color: var(--color-navy-900); font-size: 0.75rem; margin: 0;">
                                    📍 {{ trip.region?.name || 'Indonesia' }}
                                </span>
                                <span
                                    class="turget-status-badge"
                                    :class="{
                                        'turget-status-published': trip.status === 'open' || trip.status === 'bidding',
                                        'turget-status-verified': trip.status === 'accepted' || trip.status === 'booked',
                                        'turget-status-draft': trip.status === 'completed',
                                        'turget-status-rejected': trip.status === 'cancelled'
                                    }"
                                >
                                    ● {{ trip.status?.toUpperCase() }}
                                </span>
                            </div>

                            <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                                {{ trip.title }}
                            </h3>

                            <div style="display: flex; flex-wrap: wrap; gap: 1.25rem; font-size: 0.9rem; color: var(--color-text-muted);">
                                <span>📅 {{ trip.start_date }} s/d {{ trip.end_date }} ({{ trip.duration_days }} Hari)</span>
                                <span>👥 {{ trip.adults_count }} Dewasa<span v-if="trip.children_count > 0">, {{ trip.children_count }} Anak</span></span>
                                <span>💰 Rp {{ Number(trip.budget_min || 0).toLocaleString('id-ID') }} – {{ Number(trip.budget_max || 0).toLocaleString('id-ID') }}</span>
                            </div>
                        </div>

                        <div style="display: flex; align-items: center; gap: 1.25rem;">
                            <div style="text-align: right;">
                                <div style="font-size: 1.5rem; font-weight: 800; color: var(--color-blue-600);">
                                    {{ trip.proposals_count || 0 }}
                                </div>
                                <div style="font-size: 0.8rem; color: var(--color-text-muted);">
                                    {{ locale === 'id' ? 'Proposal Masuk' : 'Proposals' }}
                                </div>
                            </div>

                            <Link :href="`/trips/${trip.id}`" class="turget-btn turget-btn-primary">
                                {{ locale === 'id' ? 'Lihat Proposal →' : 'View Proposals →' }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
