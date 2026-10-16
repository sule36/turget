<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    trip: Object,
    isOwner: Boolean,
});

const { t, locale } = useI18n();

function acceptProposal(proposalId) {
    if (confirm(locale.value === 'id' ? 'Apakah Anda yakin ingin menerima proposal ini dan melanjutkan ke pembayaran escrow?' : 'Accept this proposal and proceed to escrow payment?')) {
        router.post(`/proposals/${proposalId}/accept`);
    }
}

function rejectProposal(proposalId) {
    if (confirm(locale.value === 'id' ? 'Tolak penawaran proposal ini?' : 'Decline this proposal?')) {
        router.post(`/proposals/${proposalId}/reject`);
    }
}

function startChatWithGuide(guideUserId) {
    router.post('/chat/start', {
        target_user_id: guideUserId,
        trip_inquiry_id: props.trip.id,
    });
}
</script>

<template>
    <PublicLayout>
        <Head :title="`${trip.title} — Rencana Trip Turget.id`" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container">
                <!-- Top Breadcrumb & Status Navigation -->
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
                    <Link href="/my-trips" class="turget-btn turget-btn-secondary" style="font-size: 0.88rem;">
                        ← {{ locale === 'id' ? 'Kembali ke Daftar Trip Saya' : 'Back to My Trips' }}
                    </Link>

                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                        <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); font-weight: 700; margin: 0;">
                            ID: #TRIP-{{ trip.id }}
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
                            ● Status: {{ trip.status?.toUpperCase() }}
                        </span>
                    </div>
                </div>

                <!-- Trip Details Hero Card -->
                <div style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-xs); border: 1px solid var(--color-border-subtle); margin-bottom: 1.75rem;">
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; flex-wrap: wrap;">
                        <div style="max-width: 720px;">
                            <span class="turget-badge" style="background: var(--color-sand-100); color: var(--color-navy-900); font-weight: 700; margin-bottom: 0.5rem; padding: 0.2rem 0.55rem; font-size: 0.72rem;">
                                📍 {{ trip.region?.name || 'Indonesia' }}
                            </span>
                            <h1 style="font-family: var(--font-heading); font-size: 1.65rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem; letter-spacing: -0.025em;">
                                {{ trip.title }}
                            </h1>
                            <div style="display: flex; flex-wrap: wrap; gap: 1rem; color: var(--color-text-muted); font-size: 0.86rem;">
                                <span>📅 <strong>{{ trip.start_date }}</strong> s/d <strong>{{ trip.end_date }}</strong> ({{ trip.duration_days }} Hari)</span>
                                <span>👥 <strong>{{ trip.adults_count }} Dewasa</strong><span v-if="trip.children_count > 0">, {{ trip.children_count }} Anak</span></span>
                                <span>🧭 Gaya: <strong style="text-transform: capitalize;">{{ trip.travel_style }}</strong></span>
                            </div>
                        </div>

                        <div class="turget-cta-card-mobile-full" style="background: var(--color-surface-subtle); border-radius: var(--radius-md); padding: 1rem 1.25rem; min-width: 230px; text-align: right; border: 1px solid var(--color-border-subtle);">
                            <div style="font-size: 0.75rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700;">
                                {{ locale === 'id' ? 'Target Anggaran' : 'Target Budget' }}
                            </div>
                            <div style="font-size: 1.25rem; font-weight: 800; color: var(--color-blue-600); margin-top: 0.15rem;">
                                Rp {{ Number(trip.budget_min || 0).toLocaleString('id-ID') }} – {{ Number(trip.budget_max || 0).toLocaleString('id-ID') }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--color-text-subtle); margin-top: 0.15rem;">
                                {{ trip.proposals?.length || 0 }} {{ locale === 'id' ? 'Proposal Masuk' : 'Proposals Received' }}
                            </div>
                        </div>
                    </div>

                    <!-- Special Requests & Preferences -->
                    <div v-if="trip.special_requests || trip.preferences" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-subtle);">
                        <h4 style="font-size: 0.95rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                            {{ locale === 'id' ? 'Catatan & Preferensi Wisatawan:' : 'Traveler Preferences & Special Notes:' }}
                        </h4>
                        <p v-if="trip.special_requests" style="color: var(--color-text-muted); font-size: 0.92rem; line-height: 1.6; margin-bottom: 0.75rem;">
                            "{{ trip.special_requests }}"
                        </p>
                        <div v-if="trip.preferences?.interests?.length" style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <span v-for="tag in trip.preferences.interests" :key="tag" class="turget-badge" style="background: var(--color-surface-subtle); color: var(--color-navy-800); border: 1px solid var(--color-border-subtle);">
                                ✓ {{ tag }}
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Section: Guide Proposals / Bids -->
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                        <div>
                            <h2 style="font-family: var(--font-serif); font-size: 1.65rem; font-weight: 700; color: var(--color-navy-950);">
                                🏷️ {{ locale === 'id' ? 'Penawaran Proposal dari Tour Guide Lokal' : 'Proposals from Local Tour Guides' }} ({{ trip.proposals?.length || 0 }})
                            </h2>
                            <p style="color: var(--color-text-muted); font-size: 0.92rem;">
                                {{ locale === 'id'
                                    ? 'Bandingkan penawaran itinerary dan rincian harga. Diskusi langsung via chat berfitur anti-bocor, lalu bayar aman lewat Escrow.'
                                    : 'Compare customized itineraries and prices. Chat safely in-app, then book securely with Escrow protection.' }}
                            </p>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-if="!trip.proposals?.length" style="background: white; border-radius: var(--radius-xl); padding: 4rem 2rem; text-align: center; border: 1px dashed var(--color-border-subtle);">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">🧭</div>
                        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                            {{ locale === 'id' ? 'Belum Ada Penawaran Masuk' : 'No Proposals Yet' }}
                        </h3>
                        <p style="color: var(--color-text-muted); max-width: 520px; margin: 0 auto; font-size: 0.95rem;">
                            {{ locale === 'id'
                                ? 'Rencana trip Anda telah disiarkan ke para pemandu wisata terverifikasi di wilayah ini. Biasanya penawaran pertama masuk dalam hitungan beberapa jam.'
                                : 'Your trip request has been broadcasted to verified local guides. Proposals typically arrive within a few hours.' }}
                        </p>
                    </div>

                    <!-- List of Proposals -->
                    <div v-else style="display: flex; flex-direction: column; gap: 2rem;">
                        <div
                            v-for="proposal in trip.proposals"
                            :key="proposal.id"
                            style="background: white; border-radius: var(--radius-xl); padding: 2rem; box-shadow: var(--shadow-sm); border: 2px solid;"
                            :style="{
                                borderColor: proposal.status === 'accepted' ? 'var(--color-emerald-500)' : 'var(--color-border-subtle)',
                                background: proposal.status === 'accepted' ? '#F0FDF4' : 'white'
                            }"
                        >
                            <!-- Proposal Header -->
                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 1.5rem; flex-wrap: wrap; margin-bottom: 1.5rem; border-bottom: 1px solid var(--color-border-subtle); padding-bottom: 1.25rem;">
                                <div style="display: flex; gap: 1rem; align-items: center;">
                                    <div style="width: 3.5rem; height: 3.5rem; border-radius: 50%; background: var(--color-blue-50); color: var(--color-blue-600); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.3rem;">
                                        {{ proposal.guide?.user?.name?.charAt(0) || 'G' }}
                                    </div>
                                    <div>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950);">
                                                {{ proposal.guide?.user?.name }}
                                            </h3>
                                            <span v-if="proposal.guide?.badge_verified" class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); padding: 0.15rem 0.5rem; font-size: 0.75rem;">
                                                ✓ Terverifikasi
                                            </span>
                                        </div>
                                        <div style="font-size: 0.85rem; color: var(--color-text-muted);">
                                            📍 {{ proposal.guide?.region?.name || 'Pemandu Lokal' }} • ⭐ {{ proposal.guide?.rating_avg || '5.0' }} ({{ proposal.guide?.total_reviews || 0 }} ulasan)
                                        </div>
                                    </div>
                                </div>

                                <div style="text-align: right;">
                                    <div style="font-size: 0.8rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700;">
                                        {{ locale === 'id' ? 'Total Penawaran' : 'Quoted Price' }}
                                    </div>
                                    <div style="font-size: 1.75rem; font-weight: 800; color: var(--color-blue-600);">
                                        Rp {{ Number(proposal.total_amount).toLocaleString('id-ID') }}
                                    </div>
                                    <span class="turget-badge" style="font-size: 0.75rem; margin-top: 0.25rem;"
                                          :style="{
                                              background: proposal.status === 'accepted' ? 'var(--color-emerald-50)' : 'var(--color-surface-subtle)',
                                              color: proposal.status === 'accepted' ? 'var(--color-emerald-600)' : 'var(--color-text-muted)'
                                          }">
                                        Status: {{ proposal.status?.toUpperCase() }}
                                    </span>
                                </div>
                            </div>

                            <!-- Proposal Title & Cover Letter -->
                            <div style="margin-bottom: 1.5rem;">
                                <h4 style="font-size: 1.2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                                    {{ proposal.title }}
                                </h4>
                                <p style="color: var(--color-text-main); font-size: 0.95rem; line-height: 1.6; white-space: pre-line;">
                                    {{ proposal.cover_letter }}
                                </p>
                            </div>

                            <!-- Day-by-Day Itinerary -->
                            <div v-if="proposal.itinerary?.length" style="background: var(--color-surface-subtle); border-radius: var(--radius-lg); padding: 1.25rem; margin-bottom: 1.5rem; border: 1px solid var(--color-border-subtle);">
                                <h5 style="font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                                    🗺️ {{ locale === 'id' ? 'Jadwal Itinerary yang Ditawarkan:' : 'Proposed Itinerary Days:' }}
                                </h5>
                                <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                                    <div v-for="(day, dIdx) in proposal.itinerary" :key="dIdx" style="background: white; border-radius: var(--radius-md); padding: 0.85rem 1.15rem; border: 1px solid var(--color-border-subtle);">
                                        <div style="font-weight: 700; color: var(--color-blue-600); font-size: 0.9rem; margin-bottom: 0.25rem;">
                                            Hari {{ day.day || (dIdx + 1) }}: {{ day.title }}
                                        </div>
                                        <div style="font-size: 0.88rem; color: var(--color-text-muted);">
                                            {{ day.description || day.activities?.join(' • ') }}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Action Bar -->
                            <div v-if="isOwner" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem; padding-top: 1rem; border-top: 1px solid var(--color-border-subtle);">
                                <button
                                    type="button"
                                    class="turget-btn turget-btn-secondary"
                                    @click="startChatWithGuide(proposal.guide.user_id)"
                                >
                                    💬 {{ locale === 'id' ? 'Chat Tanya Guide' : 'Chat with Guide' }}
                                </button>

                                <div v-if="proposal.status === 'submitted'" style="display: flex; gap: 0.75rem;">
                                    <button
                                        type="button"
                                        class="turget-btn turget-btn-secondary"
                                        style="color: var(--color-coral-600);"
                                        @click="rejectProposal(proposal.id)"
                                    >
                                        {{ locale === 'id' ? 'Tolak' : 'Decline' }}
                                    </button>
                                    <button
                                        type="button"
                                        class="turget-btn turget-btn-primary"
                                        style="background: var(--color-emerald-600);"
                                        @click="acceptProposal(proposal.id)"
                                    >
                                        ✓ {{ locale === 'id' ? 'Terima Penawaran & Bayar Escrow' : 'Accept & Pay with Escrow' }}
                                    </button>
                                </div>
                                <div v-else-if="proposal.status === 'accepted'" style="color: var(--color-emerald-600); font-weight: 700; font-size: 0.95rem;">
                                    ✓ {{ locale === 'id' ? 'Proposal ini telah Anda terima!' : 'Proposal accepted!' }}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
