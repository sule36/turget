<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    product: Object,
    otherProducts: Array,
    similarProducts: Array,
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
    if (props.product.vendor?.user_id) {
        router.post('/chat/start', {
            target_user_id: props.product.vendor.user_id,
        });
    }
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

function getBookingModelLabel(model) {
    const models = {
        instant: locale.value === 'en' ? '⚡ Instant Confirmation' : '⚡ Konfirmasi Instan',
        request_to_book: locale.value === 'en' ? '📅 Request to Book' : '📅 Permintaan Pemesanan',
        manual: locale.value === 'en' ? '💬 Manual Coordination' : '💬 Koordinasi & Inquiry Manual',
    };
    return models[model] || model;
}
</script>

<template>
    <PublicLayout>
        <Head :title="`${tr(product.name)} — ${product.vendor?.business_name || 'Turget.id'}`" />

        <!-- Breadcrumbs -->
        <nav style="background: var(--color-surface-subtle); border-bottom: 1px solid var(--color-border-subtle); padding: 0.85rem 0;">
            <div class="turget-container" style="font-size: 0.88rem; color: var(--color-text-muted); display: flex; gap: 0.5rem; align-items: center; flex-wrap: wrap;">
                <Link href="/" style="color: inherit; text-decoration: none;">{{ t('nav.home', 'Beranda') }}</Link>
                <span>/</span>
                <Link href="/vendors" style="color: inherit; text-decoration: none;">Marketplace Vendor</Link>
                <span>/</span>
                <Link v-if="product.vendor" :href="`/vendors/${product.vendor.slug}`" style="color: inherit; text-decoration: none;">
                    {{ product.vendor.business_name }}
                </Link>
                <span v-if="product.vendor">/</span>
                <span style="color: var(--color-navy-950); font-weight: 600;">{{ tr(product.name) }}</span>
            </div>
        </nav>

        <main class="turget-container" style="padding: 2.25rem 0 5rem;">
            <!-- Top Hero Grid: Gallery & Main Info -->
            <div class="turget-detail-layout" style="margin-bottom: 2.5rem;">
                <!-- Main Image -->
                <div style="border-radius: var(--radius-2xl); overflow: hidden; background: var(--color-sand-200); max-height: 420px; box-shadow: var(--shadow-md); position: relative;">
                    <img
                        :src="product.cover_image || 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=1200&auto=format&fit=crop&q=80'"
                        :alt="tr(product.name)"
                        style="width: 100%; height: 100%; object-fit: cover;"
                    />
                    <div style="position: absolute; top: 1.25rem; left: 1.25rem; display: flex; gap: 0.5rem;">
                        <span class="turget-badge" style="background: rgba(11,27,43,0.85); color: white; border: none; backdrop-filter: blur(8px);">
                            {{ product.category }}
                        </span>
                    </div>
                </div>

                <!-- Price & Booking Card -->
                <div class="turget-card" style="padding: 1.75rem; display: flex; flex-direction: column; justify-content: space-between; border-radius: var(--radius-2xl);">
                    <div>
                        <!-- Location & Destination Tag -->
                        <div v-if="product.destination" style="display: flex; align-items: center; gap: 0.4rem; color: var(--color-blue-600); font-size: 0.88rem; font-weight: 700; margin-bottom: 0.6rem;">
                            <span>📍 Terletak di area</span>
                            <Link :href="`/places/${product.destination.slug}`" style="color: inherit; text-decoration: underline;">
                                {{ tr(product.destination.name) }}
                            </Link>
                        </div>

                        <h1 class="turget-detail-title">
                            {{ tr(product.name) }}
                        </h1>

                        <!-- Pricing Header -->
                        <div style="background: var(--color-blue-50); border: 1px solid rgba(37,99,235,0.15); border-radius: var(--radius-xl); padding: 1.25rem; margin-bottom: 1.5rem;">
                            <div style="font-size: 0.82rem; text-transform: uppercase; color: var(--color-text-subtle); font-weight: 700;">
                                {{ locale === 'en' ? 'Price Rate' : 'Tarif Layanan' }}
                            </div>
                            <div style="font-size: 2rem; font-weight: 800; color: var(--color-blue-600); display: flex; align-items: baseline; gap: 0.4rem;">
                                {{ formatPrice(product.price) }}
                                <span style="font-size: 1rem; font-weight: 600; color: var(--color-text-muted);">
                                    {{ getUnitLabel(product.price_unit) }}
                                </span>
                            </div>
                            <div style="font-size: 0.85rem; color: var(--color-navy-800); margin-top: 0.4rem; font-weight: 600;">
                                {{ getBookingModelLabel(product.booking_model) }}
                            </div>
                        </div>

                        <!-- Capacity / Stock Specs -->
                        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem; font-size: 0.9rem;">
                            <div v-if="product.capacity" style="background: var(--color-surface-subtle); padding: 0.75rem 1rem; border-radius: var(--radius-lg);">
                                <div style="color: var(--color-text-subtle); font-size: 0.78rem;">Kapasitas / Pax</div>
                                <div style="font-weight: 700; color: var(--color-navy-950);">👥 {{ product.capacity }} Orang / Unit</div>
                            </div>
                            <div style="background: var(--color-surface-subtle); padding: 0.75rem 1rem; border-radius: var(--radius-lg);">
                                <div style="color: var(--color-text-subtle); font-size: 0.78rem;">Ketersediaan Stok</div>
                                <div style="font-weight: 700; color: var(--color-navy-950);">📦 {{ product.stock_quantity }} Tersedia</div>
                            </div>
                        </div>
                    </div>

                    <!-- Vendor Snapshot -->
                    <div v-if="product.vendor" style="background: var(--color-surface-subtle); border-radius: var(--radius-xl); padding: 1.25rem; margin-bottom: 1.5rem; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="width: 2.75rem; height: 2.75rem; border-radius: var(--radius-full); background: var(--color-blue-600); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem;">
                                {{ product.vendor.business_name.charAt(0) }}
                            </div>
                            <div>
                                <div style="font-size: 0.95rem; font-weight: 700; color: var(--color-navy-950); display: flex; align-items: center; gap: 0.35rem;">
                                    {{ product.vendor.business_name }}
                                    <span v-if="product.vendor.badge_verified" title="Verified">🛡️</span>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--color-text-muted);">
                                    📍 {{ product.vendor.region?.name || 'Indonesia' }}
                                </div>
                            </div>
                        </div>
                        <Link :href="`/vendors/${product.vendor.slug}`" class="turget-btn turget-btn-secondary" style="font-size: 0.82rem; padding: 0.4rem 0.85rem;">
                            Lihat Vendor
                        </Link>
                    </div>

                    <!-- Booking Inquiry CTA -->
                    <div style="display: flex; flex-direction: column; gap: 0.65rem;">
                        <Link
                            :href="`/checkout/vendor-product/${product.slug}`"
                            class="turget-btn turget-btn-primary"
                            style="width: 100%; justify-content: center; padding: 0.85rem; font-size: 1rem; font-weight: 700;"
                        >
                            ⚡ {{ locale === 'en' ? 'Book Service (Protected Escrow)' : 'Pesan Layanan Ini (Escrow Aman)' }}
                        </Link>
                        <button
                            v-if="product.vendor"
                            type="button"
                            @click="startChatWithVendor"
                            class="turget-btn turget-btn-secondary"
                            style="width: 100%; justify-content: center; padding: 0.75rem; font-size: 0.95rem; font-weight: 600;"
                        >
                            💬 {{ locale === 'en' ? 'Chat Directly with Vendor' : 'Tanya / Chat Vendor' }}
                        </button>
                    </div>
                    <div style="font-size: 0.73rem; color: var(--color-text-muted); text-align: center; margin-top: 0.65rem; line-height: 1.4;">
                        🔒 {{ locale === 'en' ? 'All chats & transactions protected by Turget.id Escrow' : 'Chat & transaksi aman dilindungi Rekening Bersama Escrow Turget.id' }}
                    </div>
                </div>
            </div>

            <!-- Content Details Tabs: Inclusions, Exclusions, Terms -->
            <div class="turget-detail-layout">
                <div>
                    <!-- Description -->
                    <div class="turget-card" style="padding: 1.75rem; margin-bottom: 2rem;">
                        <h2 style="font-size: 1.4rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                            {{ locale === 'en' ? 'Service Description & Overview' : 'Deskripsi Layanan' }}
                        </h2>
                        <p style="font-size: 1.05rem; color: var(--color-text-body); line-height: 1.7; white-space: pre-line;">
                            {{ tr(product.description) }}
                        </p>
                    </div>

                    <!-- Inclusions & Exclusions -->
                    <div class="turget-card" style="padding: 1.75rem; margin-bottom: 2rem;">
                        <h3 style="font-size: 1.3rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.5rem;">
                            {{ locale === 'en' ? 'Inclusions & Exclusions' : 'Fasilitas Termasuk & Tidak Termasuk' }}
                        </h3>

                        <div class="turget-two-col-grid">
                            <div>
                                <h4 style="color: var(--color-green-700); font-size: 1rem; margin-bottom: 0.75rem;">
                                    ✅ {{ locale === 'en' ? 'Included' : 'Sudah Termasuk' }}
                                </h4>
                                <ul v-if="product.inclusions" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.95rem; color: var(--color-navy-900);">
                                    <li v-for="(inc, idx) in (product.inclusions[locale] || product.inclusions['id'] || product.inclusions)" :key="idx" style="display: flex; gap: 0.5rem;">
                                        <span>•</span>
                                        <span>{{ inc }}</span>
                                    </li>
                                </ul>
                                <p v-else style="color: var(--color-text-muted); font-size: 0.9rem;">Sesuai kesepakatan pemesanan.</p>
                            </div>

                            <div>
                                <h4 style="color: var(--color-red-600); font-size: 1rem; margin-bottom: 0.75rem;">
                                    ❌ {{ locale === 'en' ? 'Excluded' : 'Tidak Termasuk' }}
                                </h4>
                                <ul v-if="product.exclusions" style="list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: 0.5rem; font-size: 0.95rem; color: var(--color-navy-900);">
                                    <li v-for="(exc, idx) in (product.exclusions[locale] || product.exclusions['id'] || product.exclusions)" :key="idx" style="display: flex; gap: 0.5rem;">
                                        <span>•</span>
                                        <span>{{ exc }}</span>
                                    </li>
                                </ul>
                                <p v-else style="color: var(--color-text-muted); font-size: 0.9rem;">Pengeluaran pribadi & tip opsional.</p>
                            </div>
                        </div>
                    </div>

                    <!-- Terms & Conditions -->
                    <div class="turget-card" style="padding: 2rem;">
                        <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.75rem;">
                            📜 {{ locale === 'en' ? 'Terms & Usage Conditions' : 'Ketentuan & Syarat Pemesanan' }}
                        </h3>
                        <p style="color: var(--color-text-body); font-size: 0.95rem; line-height: 1.6;">
                            {{ tr(product.terms) || 'Wajib konfirmasi jadwal paling lambat 24 jam sebelum kedatangan. Menunjukkan identitas KTP/Paspor saat check-in atau serah terima unit.' }}
                        </p>
                    </div>
                </div>

                <!-- Right Sidebar: Tour Package Add-on Links -->
                <div class="turget-sticky-booking-sidebar">
                    <!-- Tour Packages that incorporate this add-on -->
                    <div v-if="product.tourPackages?.length > 0" class="turget-card" style="padding: 1.75rem; margin-bottom: 2rem;">
                        <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                            🎒 {{ locale === 'en' ? 'Available as Add-on in Tour Packages' : 'Tersedia Sebagai Add-on Paket Wisata' }}
                        </h3>
                        <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 1rem;">
                            Pesan paket wisata berikut dan tambahkan layanan ini untuk kenyamanan trip Anda:
                        </p>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <div
                                v-for="pkg in product.tourPackages"
                                :key="pkg.id"
                                style="padding: 0.85rem; background: var(--color-surface-subtle); border-radius: var(--radius-lg); border: 1px solid var(--color-border-subtle);"
                            >
                                <div style="font-size: 0.9rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.25rem;">
                                    <Link :href="`/packages/${pkg.slug}`" style="color: inherit; text-decoration: none;">
                                        {{ tr(pkg.title) }}
                                    </Link>
                                </div>
                                <div style="font-size: 0.8rem; color: var(--color-text-muted); display: flex; justify-content: space-between;">
                                    <span>Guide: {{ pkg.guide?.display_name }}</span>
                                    <span style="font-weight: 700; color: var(--color-blue-600);">{{ formatPrice(pkg.base_price) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Other products from same vendor -->
                    <div v-if="otherProducts?.length > 0" class="turget-card" style="padding: 1.75rem;">
                        <h3 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1rem;">
                            ✨ {{ locale === 'en' ? 'More from this Vendor' : 'Layanan Lain dari Vendor Ini' }}
                        </h3>
                        <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                            <div
                                v-for="other in otherProducts"
                                :key="other.id"
                                style="display: flex; gap: 0.75rem; align-items: center;"
                            >
                                <div style="width: 3.5rem; height: 3.5rem; border-radius: var(--radius-md); overflow: hidden; background: var(--color-sand-200); flex-shrink: 0;">
                                    <img :src="other.cover_image || 'https://images.unsplash.com/photo-1544620347-c4fd4a3d5957?w=200&auto=format&fit=crop&q=80'" style="width: 100%; height: 100%; object-fit: cover;" />
                                </div>
                                <div style="flex: 1; min-width: 0;">
                                    <div style="font-size: 0.88rem; font-weight: 700; color: var(--color-navy-950); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                        <Link :href="`/vendors/products/${other.slug}`" style="color: inherit; text-decoration: none;">
                                            {{ tr(other.name) }}
                                        </Link>
                                    </div>
                                    <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-blue-600);">
                                        {{ formatPrice(other.price) }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </PublicLayout>
</template>
