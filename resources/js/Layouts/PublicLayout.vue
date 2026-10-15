<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { useI18n } from '@/composables/useI18n';

const page = usePage();
const { locale, t } = useI18n();

const authUser = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash || {});

const showMobileNav = ref(false);

function switchLang(lang) {
    if (lang === locale.value) return;
    router.post(`/locale/${lang}`, {}, {
        preserveScroll: true,
        preserveState: false,
    });
}
</script>

<template>
    <div class="turget-site-wrapper">
        <!-- Top Navigation Bar -->
        <header class="turget-navbar">
            <div class="turget-container">
                <div class="turget-navbar-inner">
                    <!-- Brand Logo -->
                    <Link href="/" class="turget-brand">
                        <span class="turget-brand-icon">T</span>
                        <span>turget<span class="turget-brand-dot">.id</span></span>
                    </Link>

                    <!-- Main Navigation Links -->
                    <nav class="turget-nav-links" aria-label="Main Navigation">
                        <Link href="/" class="turget-nav-link" :class="{ 'is-active': $page.url === '/' }">
                            {{ t('nav.home', 'Beranda') }}
                        </Link>
                        <Link href="/destinations" class="turget-nav-link" :class="{ 'is-active': $page.url.startsWith('/destinations') || $page.url.startsWith('/places') }">
                            {{ t('nav.destinations', 'Destinasi') }}
                        </Link>
                        <Link href="/guides" class="turget-nav-link" :class="{ 'is-active': $page.url.startsWith('/guides') }">
                            {{ t('nav.tour_guides', 'Tour Guide') }}
                        </Link>
                        <Link href="/vendors" class="turget-nav-link" :class="{ 'is-active': $page.url.startsWith('/vendors') }">
                            {{ t('nav.vendors', 'Layanan & Vendor') }}
                        </Link>
                        <Link href="/articles" class="turget-nav-link" :class="{ 'is-active': $page.url.startsWith('/articles') }">
                            {{ t('nav.articles', 'Inspirasi & Panduan') }}
                        </Link>
                        <Link href="/trips/plan" class="turget-nav-link" style="color: var(--color-blue-600); font-weight: 700;" :class="{ 'is-active': $page.url === '/trips/plan' }">
                            ✨ {{ locale === 'id' ? 'Rencanakan Trip' : 'Plan a Trip' }}
                        </Link>
                        <template v-if="authUser">
                            <Link href="/my-bookings" class="turget-nav-link" :class="{ 'is-active': $page.url.startsWith('/my-bookings') || $page.url.startsWith('/bookings') }">
                                🛡️ {{ locale === 'id' ? 'Pesanan' : 'Bookings' }}
                            </Link>
                            <Link href="/chat" class="turget-nav-link" :class="{ 'is-active': $page.url.startsWith('/chat') }">
                                💬 {{ locale === 'id' ? 'Chat' : 'Chat' }}
                            </Link>
                        </template>
                        <template v-if="authUser?.roles?.includes('vendor')">
                            <Link href="/vendor/dashboard" class="turget-nav-link" style="color: var(--color-blue-600); font-weight: 700;">
                                🏨 Portal Vendor
                            </Link>
                        </template>
                        <template v-if="authUser?.roles?.includes('tour_guide')">
                            <Link href="/guide/dashboard" class="turget-nav-link" style="color: var(--color-blue-600); font-weight: 700;">
                                🧭 Portal Guide
                            </Link>
                        </template>
                        <template v-else-if="!authUser?.is_admin && !authUser?.roles?.includes('vendor')">
                            <Link href="/become-a-vendor" class="turget-nav-link" style="color: var(--color-blue-600); font-weight: 600;">
                                Gabung Vendor
                            </Link>
                        </template>
                        <template v-if="authUser?.is_admin">
                            <Link href="/admin" class="turget-nav-link" style="color: var(--color-blue-600);">
                                🛡️ {{ t('nav.admin_cms', 'Admin CMS') }}
                            </Link>
                        </template>
                    </nav>

                    <!-- Right Actions -->
                    <div class="turget-nav-actions">
                        <!-- Language Switcher Pill -->
                        <div class="turget-lang-pill" aria-label="Language Switcher">
                            <button
                                type="button"
                                class="turget-lang-btn"
                                :class="{ 'is-active': locale === 'id' }"
                                @click="switchLang('id')"
                            >
                                ID
                            </button>
                            <button
                                type="button"
                                class="turget-lang-btn"
                                :class="{ 'is-active': locale === 'en' }"
                                @click="switchLang('en')"
                            >
                                EN
                            </button>
                        </div>

                        <!-- User State -->
                        <template v-if="authUser">
                            <div class="turget-user-chip" style="display: flex; align-items: center; gap: 0.75rem;">
                                <Link href="/my-trips" style="text-decoration: none; font-size: 0.85rem; font-weight: 700; color: var(--color-navy-950);">
                                    {{ authUser.name.split(' ')[0] }}
                                </Link>
                                <Link
                                    v-if="authUser?.roles?.includes('tour_guide') || authUser?.roles?.includes('vendor')"
                                    href="/payouts"
                                    class="turget-btn turget-btn-secondary turget-btn-sm"
                                    style="padding: 0.25rem 0.5rem; font-size: 0.75rem;"
                                >
                                    💰 Dompet
                                </Link>
                                <Link
                                    href="/logout"
                                    method="post"
                                    as="button"
                                    class="turget-btn turget-btn-secondary turget-btn-sm"
                                >
                                    {{ t('nav.logout', 'Keluar') }}
                                </Link>
                            </div>
                        </template>
                        <template v-else>
                            <Link href="/login" class="turget-btn turget-btn-secondary turget-btn-sm">
                                {{ t('nav.login', 'Masuk') }}
                            </Link>
                            <Link href="/register" class="turget-btn turget-btn-primary turget-btn-sm">
                                {{ t('nav.register', 'Daftar') }}
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </header>

        <!-- Flash Notices -->
        <div v-if="flash.success || flash.error" class="turget-container" style="margin-top: 1.25rem;">
            <div v-if="flash.success" class="turget-flash turget-flash-success">
                <span>✓ {{ flash.success }}</span>
            </div>
            <div v-if="flash.error" class="turget-flash turget-flash-error">
                <span>⚠ {{ flash.error }}</span>
            </div>
        </div>

        <!-- Main Slot -->
        <main>
            <slot />
        </main>

        <!-- Global Footer -->
        <footer class="turget-footer">
            <div class="turget-container">
                <div class="turget-footer-grid">
                    <div>
                        <Link href="/" class="turget-brand" style="color: white;">
                            <span class="turget-brand-icon">T</span>
                            <span>turget<span class="turget-brand-dot">.id</span></span>
                        </Link>
                        <p class="turget-footer-brand-desc">
                            {{ t('footer.tagline', 'Destination-first travel marketplace pertama di Indonesia yang menghubungkan wisatawan dengan pemandu lokal terverifikasi.') }}
                        </p>
                        <div style="margin-top: 1.5rem; display: flex; gap: 0.75rem;">
                            <span class="turget-badge" style="background: rgba(255,255,255,0.1); color: var(--color-sand-200); border-color: rgba(255,255,255,0.2);">
                                🇮🇩 Wonderful Indonesia
                            </span>
                        </div>
                    </div>

                    <div>
                        <h4 class="turget-footer-col-title">{{ t('footer.explore', 'Jelajahi') }}</h4>
                        <ul class="turget-footer-links">
                            <li><Link href="/destinations" class="turget-footer-link">{{ t('nav.destinations', 'Destinasi Populer') }}</Link></li>
                            <li><Link href="/destinations/indonesia" class="turget-footer-link">Panduan Indonesia</Link></li>
                            <li><Link href="/destinations/indonesia/bali" class="turget-footer-link">Wisata Bali</Link></li>
                            <li><Link href="/destinations/indonesia/di-yogyakarta" class="turget-footer-link">Wisata Yogyakarta</Link></li>
                            <li><Link href="/destinations/indonesia/jawa-timur" class="turget-footer-link">Wisata Bromo & Jatim</Link></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="turget-footer-col-title">{{ t('footer.partners', 'Mitra') }}</h4>
                        <ul class="turget-footer-links">
                            <li><a href="#" class="turget-footer-link">Daftar Tour Guide</a></li>
                            <li><a href="#" class="turget-footer-link">Mitra Penginapan & Villa</a></li>
                            <li><a href="#" class="turget-footer-link">Mitra Rental Kendaraan</a></li>
                            <li><a href="#" class="turget-footer-link">Aktivitas & Ekowisata</a></li>
                        </ul>
                    </div>

                    <div>
                        <h4 class="turget-footer-col-title">{{ t('footer.legal', 'Kebijakan') }}</h4>
                        <ul class="turget-footer-links">
                            <li><a href="#" class="turget-footer-link">{{ t('footer.terms', 'Syarat & Ketentuan') }}</a></li>
                            <li><a href="#" class="turget-footer-link">{{ t('footer.privacy', 'Kebijakan Privasi') }}</a></li>
                            <li><a href="#" class="turget-footer-link">Standar Verifikasi Mitra</a></li>
                            <li><a href="#" class="turget-footer-link">Perlindungan Transaksi Escrow</a></li>
                        </ul>
                    </div>
                </div>

                <div class="turget-footer-bottom">
                    <div>© {{ new Date().getFullYear() }} Turget.id. {{ t('footer.rights', 'Hak Cipta Dilindungi.') }}</div>
                    <div style="display: flex; gap: 1.5rem;">
                        <span>Explore with confidence. Travel your way.</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</template>
