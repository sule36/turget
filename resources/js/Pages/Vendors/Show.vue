<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    vendor: Object,
    nearbyDestinations: Array,
});

const { t, tr, locale } = useI18n();

function formatPrice(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(amount);
}

function startChatWithVendor() {
    router.post('/chat/start', {
        target_user_id: props.vendor.user_id,
    });
}

function getUnitLabel(unit) {
    const units = {
        per_night: locale.value === 'en' ? '/ night' : '/ malam',
        per_day: locale.value === 'en' ? '/ day' : '/ hari',
        per_person: locale.value === 'en' ? '/ person' : '/ orang',
        per_session: locale.value === 'en' ? '/ session' : '/ sesi',
        per_unit: locale.value === 'en' ? '/ unit' : '/ unit',
    };
    return units[unit] || '';
}
</script>

<template>
    <PublicLayout>
        <Head :title="`${vendor.business_name} — Mitra Vendor Terverifikasi Turget.id`" />

        <!-- Vendor Header Banner -->
        <section style="position: relative; height: 320px; background: var(--color-navy-950); overflow: hidden;">
            <img
                :src="vendor.cover_image_url || 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=1600&auto=format&fit=crop&q=80'"
                :alt="vendor.business_name"
                style="width: 100%; height: 100%; object-fit: cover; opacity: 0.65;"
            />
            <div style="position: absolute; inset: 0; background: linear-gradient(to top, rgba(11,27,43,0.95) 0%, rgba(11,27,43,0.2) 60%);"></div>

            <div class="turget-container" style="position: absolute; bottom: 2rem; left: 0; right: 0;">
                <div style="display: flex; align-items: flex-end; gap: 1.5rem; flex-wrap: wrap;">
                    <div style="width: 5rem; height: 5rem; border-radius: var(--radius-2xl); background: white; box-shadow: var(--shadow-lg); display: flex; align-items: center; justify-content: center; font-size: 2.25rem; font-weight: 800; color: var(--color-blue-600); border: 3px solid white; overflow: hidden;">
                        <img v-if="vendor.avatar_url" :src="vendor.avatar_url" style="width: 100%; height: 100%; object-fit: cover;" />
                        <span v-else>{{ vendor.business_name.charAt(0) }}</span>
                    </div>

                    <div style="flex: 1; min-width: 280px;">
                        <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 0.5rem; flex-wrap: wrap;">
                            <span class="turget-badge" style="background: rgba(255,255,255,0.15); color: white; border: none; backdrop-filter: blur(8px);">
                                {{ vendor.category }}
                            </span>
                            <span v-if="vendor.badge_verified" class="turget-badge" style="background: var(--color-blue-600); color: white; border: none;">
                                🛡️ {{ locale === 'en' ? 'Verified Vendor' : 'Mitra Terverifikasi' }}
                            </span>
                        </div>
                        <h1 class="turget-detail-title" style="color: white;">
                            {{ vendor.business_name }}
                        </h1>
                        <div style="color: var(--color-sand-200); font-size: 0.95rem; display: flex; align-items: center; gap: 1rem; flex-wrap: wrap;">
                            <span>📍 {{ vendor.address }}</span>
                            <span>•</span>
                            <span>⭐ {{ vendor.rating_avg }} ({{ vendor.reviews_count }} {{ locale === 'en' ? 'reviews' : 'ulasan' }})</span>
                            <span>•</span>
                            <span>🎯 {{ vendor.bookings_completed_count }} {{ locale === 'en' ? 'orders completed' : 'transaksi sukses' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Main Body -->
        <main class="turget-container" style="padding: 2.25rem 0 5rem;">
            <div class="turget-detail-layout">
                <!-- Left Column: Story, Offerings & Details -->
                <div>
                    <!-- About Section -->
                    <div class="turget-card" style="padding: 2rem; margin-bottom: 2.5rem;">
                        <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                            {{ locale === 'en' ? 'About This Vendor' : 'Tentang Penyedia Layanan' }}
                        </h2>
                        <p style="font-size: 1.05rem; color: var(--color-text-body); line-height: 1.7; white-space: pre-line;">
                            {{ tr(vendor.description) }}
                        </p>
                    </div>

                    <!-- Products / Services Catalog -->
                    <div style="margin-bottom: 3rem;">
                        <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem;">
                            <div>
                                <h2 style="font-size: 1.5rem; font-weight: 700; color: var(--color-navy-950); margin: 0;">
                                    {{ locale === 'en' ? 'Catalog of Services & Stays' : 'Katalog Layanan & Penginapan' }}
                                </h2>
                                <p style="font-size: 0.9rem; color: var(--color-text-muted); margin: 0.25rem 0 0;">
                                    {{ locale === 'en' ? 'Direct booking & add-on availability' : 'Dapat dipesan mandiri atau sebagai add-on paket wisata' }}
                                </p>
                            </div>
                            <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); border-color: rgba(37,99,235,0.2);">
                                {{ vendor.products?.length || 0 }} {{ locale === 'en' ? 'Items' : 'Layanan' }}
                            </span>
                        </div>

                        <div v-if="vendor.products?.length > 0" style="display: flex; flex-direction: column; gap: 1.25rem;">
                            <div
                                v-for="prod in vendor.products"
                                :key="prod.id"
                                class="turget-card"
                                style="display: grid; grid-template-columns: 180px 1fr auto; gap: 1.5rem; align-items: center; padding: 1.25rem; border-radius: var(--radius-xl); transition: transform 0.2s, box-shadow 0.2s;"
                                onmouseover="this.style.boxShadow='var(--shadow-md)';"
                                onmouseout="this.style.boxShadow='var(--shadow-sm)';"
                            >
                                <div style="height: 120px; border-radius: var(--radius-lg); overflow: hidden; background: var(--color-sand-200);">
                                    <img
                                        :src="prod.cover_image || 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=600&auto=format&fit=crop&q=80'"
                                        :alt="tr(prod.name)"
                                        style="width: 100%; height: 100%; object-fit: cover;"
                                    />
                                </div>

                                <div>
                                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                                        <span class="turget-badge" style="font-size: 0.72rem; padding: 0.2rem 0.5rem;">
                                            {{ prod.category }}
                                        </span>
                                        <span v-if="prod.booking_model === 'instant'" style="font-size: 0.75rem; color: var(--color-green-600); font-weight: 600;">
                                            ⚡ Konfirmasi Instan
                                        </span>
                                    </div>
                                    <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin: 0 0 0.4rem;">
                                        <Link :href="`/vendors/products/${prod.slug}`" style="color: inherit; text-decoration: none;">
                                            {{ tr(prod.name) }}
                                        </Link>
                                    </h3>
                                    <p style="font-size: 0.88rem; color: var(--color-text-muted); margin: 0; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                                        {{ tr(prod.description) }}
                                    </p>
                                </div>

                                <div style="text-align: right; min-width: 140px;">
                                    <div style="font-size: 1.2rem; font-weight: 800; color: var(--color-blue-600); margin-bottom: 0.5rem;">
                                        {{ formatPrice(prod.price) }}
                                        <div style="font-size: 0.75rem; font-weight: 500; color: var(--color-text-muted);">
                                            {{ getUnitLabel(prod.price_unit) }}
                                        </div>
                                    </div>
                                    <Link :href="`/vendors/products/${prod.slug}`" class="turget-btn turget-btn-primary" style="padding: 0.45rem 1rem; font-size: 0.85rem;">
                                        {{ locale === 'en' ? 'Book / View' : 'Pesan / Detail' }}
                                    </Link>
                                </div>
                            </div>
                        </div>

                        <div v-else style="padding: 2.5rem; text-align: center; background: var(--color-surface-subtle); border-radius: var(--radius-xl);">
                            <p style="color: var(--color-text-muted);">Vendor ini belum mempublikasikan layanan aktif.</p>
                        </div>
                    </div>

                    <!-- Policies & Terms -->
                    <div class="turget-card" style="padding: 2rem;">
                        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                            📋 {{ locale === 'en' ? 'Cancellation & Refund Policy' : 'Kebijakan Pembatalan & Refund' }}
                        </h3>
                        <p style="color: var(--color-text-body); font-size: 0.95rem; line-height: 1.6;">
                            {{ tr(vendor.cancellation_policy) || 'Pembatalan gratis hingga 48 jam sebelum jadwal penggunaan layanan. Setelahnya dikenakan biaya penalti sesuai ketentuan operasional vendor.' }}
                        </p>
                    </div>
                </div>

                <!-- Right Column: Sidebar Contacts & Location Map -->
                <div class="turget-sticky-booking-sidebar">
                    <!-- Verification & Contact Card -->
                    <div class="turget-card" style="padding: 1.75rem; margin-bottom: 2rem;">
                        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                            📞 {{ locale === 'en' ? 'Contact & Booking Info' : 'Informasi Pemesanan' }}
                        </h3>

                        <div style="display: flex; flex-direction: column; gap: 1rem; margin-bottom: 1.5rem; font-size: 0.92rem;">
                            <div>
                                <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700;">
                                    {{ locale === 'en' ? 'Legal Entity' : 'Badan Usaha' }}
                                </div>
                                <div style="font-weight: 600; color: var(--color-navy-900); text-transform: uppercase;">
                                    {{ vendor.business_type }} • {{ vendor.business_name }}
                                </div>
                            </div>

                            <div>
                                <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700;">
                                    {{ locale === 'en' ? 'Operating Hours' : 'Jam Operasional' }}
                                </div>
                                <div style="font-weight: 600; color: var(--color-navy-900);">
                                    {{ tr(vendor.opening_hours) || 'Setiap Hari, 08:00 - 20:00 WIB' }}
                                </div>
                            </div>

                            <div>
                                <div style="font-size: 0.78rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700;">
                                    {{ locale === 'en' ? 'Address & Region' : 'Alamat Operasional' }}
                                </div>
                                <div style="color: var(--color-navy-900);">
                                    {{ vendor.address }}, {{ vendor.region?.name }}
                                </div>
                            </div>
                        </div>

                        <!-- Direct In-App Live Chat Button -->
                        <button
                            type="button"
                            @click="startChatWithVendor"
                            class="turget-btn turget-btn-primary"
                            style="width: 100%; justify-content: center; font-weight: 700; margin-bottom: 0.75rem;"
                        >
                            💬 {{ locale === 'en' ? 'Chat Directly with Vendor' : 'Chat Langsung dengan Vendor' }}
                        </button>

                        <div style="font-size: 0.78rem; color: var(--color-text-subtle); text-align: center; line-height: 1.4;">
                            🛡️ {{ locale === 'en' ? 'Protected by Turget.id Escrow & anti-disintermediation security.' : 'Chat aman dilindungi oleh sistem keamanan Rekening Bersama Escrow Turget.id.' }}
                        </div>

                        <!-- Nearby Destinations -->
                        <div v-if="nearbyDestinations?.length > 0" style="margin-top: 2rem; padding-top: 1.5rem; border-top: 1px solid var(--color-border-subtle);">
                            <div style="font-size: 0.85rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                                📍 Destinasi Terdekat (Radius 25km):
                            </div>
                            <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                                <Link
                                    v-for="dest in nearbyDestinations"
                                    :key="dest.id"
                                    :href="`/places/${dest.slug}`"
                                    style="font-size: 0.85rem; color: var(--color-blue-600); text-decoration: none; display: flex; justify-content: space-between;"
                                >
                                    <span>🏛️ {{ tr(dest.name) }}</span>
                                    <span v-if="dest.distance_km" style="color: var(--color-text-subtle);">{{ Number(dest.distance_km).toFixed(1) }} km</span>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </PublicLayout>
</template>
