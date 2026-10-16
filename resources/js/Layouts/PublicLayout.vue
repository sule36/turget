<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { useI18n } from '@/composables/useI18n';

const page = usePage();
const { locale, t } = useI18n();

const authUser = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash || {});
const notifications = computed(() => page.props.notifications || { unread_count: 0, recent: [] });

const showMobileNav = ref(false);
const showNotifications = ref(false);

// Auto-close mobile drawer when route changes
watch(() => page.url, () => {
    showMobileNav.value = false;
    showNotifications.value = false;
});

function markNotificationAsRead(notif) {
    if (!notif.is_read) {
        router.post(`/notifications/${notif.id}/read`, {}, {
            preserveScroll: true,
            preserveState: true,
        });
    }
    showNotifications.value = false;
    if (notif.action_url) {
        router.visit(notif.action_url);
    }
}

function markAllNotificationsRead() {
    router.post('/notifications/read-all', {}, {
        preserveScroll: true,
        preserveState: true,
    });
}

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
                    <Link href="/" class="turget-brand" @click="showMobileNav = false">
                        <span class="turget-brand-icon">T</span>
                        <span>turget<span class="turget-brand-dot">.id</span></span>
                    </Link>

                    <!-- Main Navigation Links (Desktop) -->
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
                        <!-- Desktop Actions -->
                        <div class="turget-desktop-nav-actions">
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

                            <!-- User State (Desktop) -->
                            <template v-if="authUser">
                                <div class="turget-user-chip" style="display: flex; align-items: center; gap: 0.5rem;">
                                    <Link href="/my-trips" style="text-decoration: none; font-size: 0.82rem; font-weight: 700; color: var(--color-navy-950);">
                                        {{ authUser.name.split(' ')[0] }}
                                    </Link>
                                    <Link
                                        href="/my-favorites"
                                        class="turget-btn turget-btn-secondary turget-btn-sm"
                                        style="padding: 0.25rem 0.5rem; font-size: 0.75rem; color: #e11d48;"
                                        :title="locale === 'id' ? 'Wishlist & Favorit Saya' : 'My Saved Wishlist'"
                                    >
                                        ❤️
                                    </Link>
                                    <Link
                                        href="/profile"
                                        class="turget-btn turget-btn-secondary turget-btn-sm"
                                        style="padding: 0.25rem 0.5rem; font-size: 0.72rem;"
                                        :title="locale === 'id' ? 'Pengaturan Akun' : 'Account Settings'"
                                    >
                                        ⚙️ Profil
                                    </Link>
                                    <Link
                                        v-if="authUser?.roles?.includes('tour_guide') || authUser?.roles?.includes('vendor')"
                                        href="/payouts"
                                        class="turget-btn turget-btn-secondary turget-btn-sm"
                                        style="padding: 0.25rem 0.5rem; font-size: 0.72rem;"
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

                        <!-- Notification Bell Dropdown (Both Desktop & Mobile) -->
                        <div v-if="authUser" style="position: relative;">
                            <button
                                type="button"
                                @click="showNotifications = !showNotifications"
                                class="turget-btn turget-btn-secondary turget-btn-sm"
                                style="padding: 0.35rem 0.55rem; font-size: 0.95rem; position: relative; border-radius: var(--radius-full);"
                                :title="locale === 'id' ? 'Notifikasi' : 'Notifications'"
                                aria-label="Notifications"
                            >
                                🔔
                                <span
                                    v-if="notifications.unread_count > 0"
                                    style="position: absolute; top: -4px; right: -4px; background: #e11d48; color: white; font-size: 0.65rem; font-weight: 800; border-radius: 9999px; min-width: 17px; height: 17px; display: flex; align-items: center; justify-content: center; padding: 0 3px; border: 2px solid white; line-height: 1;"
                                >
                                    {{ notifications.unread_count > 9 ? '9+' : notifications.unread_count }}
                                </span>
                            </button>

                            <!-- Notification Dropdown Modal -->
                            <div
                                v-if="showNotifications"
                                style="position: absolute; top: calc(100% + 8px); right: 0; width: min(340px, calc(100vw - 2rem)); background: white; border-radius: var(--radius-lg); box-shadow: var(--shadow-xl); border: 1px solid var(--color-border-subtle); z-index: 1000; overflow: hidden;"
                            >
                                <div style="padding: 0.85rem 1rem; border-bottom: 1px solid var(--color-border-subtle); display: flex; justify-content: space-between; align-items: center; background: var(--color-surface-subtle);">
                                    <div style="font-weight: 800; font-size: 0.88rem; color: var(--color-navy-950);">
                                        🔔 {{ locale === 'id' ? 'Notifikasi Akun' : 'Notifications' }}
                                    </div>
                                    <button
                                        v-if="notifications.unread_count > 0"
                                        type="button"
                                        @click="markAllNotificationsRead"
                                        style="background: none; border: none; font-size: 0.72rem; color: var(--color-blue-600); cursor: pointer; font-weight: 600; padding: 0;"
                                    >
                                        {{ locale === 'id' ? 'Tandai Dibaca' : 'Mark all read' }}
                                    </button>
                                </div>

                                <div style="max-height: 320px; overflow-y: auto;">
                                    <div
                                        v-if="notifications.recent && notifications.recent.length > 0"
                                        style="display: flex; flex-direction: column;"
                                    >
                                        <div
                                            v-for="notif in notifications.recent"
                                            :key="notif.id"
                                            @click="markNotificationAsRead(notif)"
                                            style="padding: 0.75rem 1rem; border-bottom: 1px solid var(--color-border-subtle); cursor: pointer; transition: background 0.15s ease;"
                                            :style="{ background: notif.is_read ? 'white' : 'var(--color-blue-50)' }"
                                        >
                                            <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.5rem; margin-bottom: 0.2rem;">
                                                <div style="font-weight: 700; font-size: 0.82rem; color: var(--color-navy-950); line-height: 1.25;">
                                                    {{ notif.title }}
                                                </div>
                                                <span v-if="!notif.is_read" style="width: 7px; height: 7px; border-radius: 50%; background: var(--color-blue-600); flex-shrink: 0; margin-top: 4px;"></span>
                                            </div>
                                            <div style="font-size: 0.75rem; color: var(--color-text-muted); line-height: 1.35; margin-bottom: 0.25rem;">
                                                {{ notif.message }}
                                            </div>
                                            <div style="font-size: 0.68rem; color: var(--color-text-subtle);">
                                                {{ new Date(notif.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) }}
                                            </div>
                                        </div>
                                    </div>

                                    <div v-else style="padding: 2rem 1rem; text-align: center; color: var(--color-text-muted); font-size: 0.82rem;">
                                        {{ locale === 'id' ? 'Tidak ada pemberitahuan baru.' : 'No new notifications.' }}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Mobile Hamburger Button -->
                        <button
                            type="button"
                            class="turget-menu-toggle"
                            :class="{ 'is-active': showMobileNav }"
                            @click="showMobileNav = !showMobileNav"
                            :aria-label="showMobileNav ? 'Tutup menu' : 'Buka menu'"
                        >
                            <span class="turget-menu-bar"></span>
                            <span class="turget-menu-bar"></span>
                            <span class="turget-menu-bar"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Drawer -->
            <div v-if="showMobileNav" class="turget-mobile-drawer">
                <div class="turget-mobile-links">
                    <Link href="/" class="turget-mobile-link" :class="{ 'is-active': $page.url === '/' }" @click="showMobileNav = false">
                        <span class="turget-mobile-icon">🏠</span>
                        <span>{{ t('nav.home', 'Beranda') }}</span>
                    </Link>
                    <Link href="/destinations" class="turget-mobile-link" :class="{ 'is-active': $page.url.startsWith('/destinations') || $page.url.startsWith('/places') }" @click="showMobileNav = false">
                        <span class="turget-mobile-icon">🏖️</span>
                        <span>{{ t('nav.destinations', 'Destinasi') }}</span>
                    </Link>
                    <Link href="/guides" class="turget-mobile-link" :class="{ 'is-active': $page.url.startsWith('/guides') }" @click="showMobileNav = false">
                        <span class="turget-mobile-icon">🧭</span>
                        <span>{{ t('nav.tour_guides', 'Tour Guide') }}</span>
                    </Link>
                    <Link href="/vendors" class="turget-mobile-link" :class="{ 'is-active': $page.url.startsWith('/vendors') }" @click="showMobileNav = false">
                        <span class="turget-mobile-icon">🏨</span>
                        <span>{{ t('nav.vendors', 'Layanan & Vendor') }}</span>
                    </Link>
                    <Link href="/articles" class="turget-mobile-link" :class="{ 'is-active': $page.url.startsWith('/articles') }" @click="showMobileNav = false">
                        <span class="turget-mobile-icon">📖</span>
                        <span>{{ t('nav.articles', 'Inspirasi & Panduan') }}</span>
                    </Link>
                    <Link href="/trips/plan" class="turget-mobile-link turget-mobile-link-highlight" :class="{ 'is-active': $page.url === '/trips/plan' }" @click="showMobileNav = false">
                        <span class="turget-mobile-icon">✨</span>
                        <span>{{ locale === 'id' ? 'Rencanakan Trip Kustom' : 'Plan a Custom Trip' }}</span>
                    </Link>
                </div>

                <div class="turget-mobile-divider"></div>

                <!-- User Section / Auth Actions in Mobile Drawer -->
                <div v-if="authUser" class="turget-mobile-user-section">
                    <div class="turget-mobile-user-card">
                        <div class="turget-mobile-avatar">{{ authUser.name.charAt(0) }}</div>
                        <div style="flex: 1; min-width: 0;">
                            <div style="font-weight: 700; font-size: 0.92rem; color: var(--color-navy-950); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ authUser.name }}
                            </div>
                            <div style="font-size: 0.78rem; color: var(--color-text-muted); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ authUser.email }}
                            </div>
                        </div>
                    </div>

                    <div class="turget-mobile-user-links">
                        <Link href="/my-trips" class="turget-mobile-sublink" @click="showMobileNav = false">
                            🎒 {{ locale === 'id' ? 'Trip Saya' : 'My Trips' }}
                        </Link>
                        <Link href="/my-bookings" class="turget-mobile-sublink" @click="showMobileNav = false">
                            🛡️ {{ locale === 'id' ? 'Pesanan & Booking' : 'My Bookings' }}
                        </Link>
                        <Link href="/my-favorites" class="turget-mobile-sublink" @click="showMobileNav = false">
                            ❤️ {{ locale === 'id' ? 'Favorit Saya' : 'My Saved Wishlist' }}
                        </Link>
                        <Link href="/chat" class="turget-mobile-sublink" @click="showMobileNav = false">
                            💬 {{ locale === 'id' ? 'Pesan & Diskusi' : 'Messages' }}
                        </Link>
                        <Link href="/profile" class="turget-mobile-sublink" @click="showMobileNav = false">
                            ⚙️ {{ locale === 'id' ? 'Pengaturan Profil' : 'Profile Settings' }}
                        </Link>
                        <Link v-if="authUser?.roles?.includes('tour_guide') || authUser?.roles?.includes('vendor')" href="/payouts" class="turget-mobile-sublink" @click="showMobileNav = false">
                            💰 {{ locale === 'id' ? 'Dompet & Pencairan' : 'Payouts' }}
                        </Link>
                        <Link v-if="authUser?.roles?.includes('vendor')" href="/vendor/dashboard" class="turget-mobile-sublink" style="color: var(--color-blue-600); font-weight: 700;" @click="showMobileNav = false">
                            🏨 Portal Vendor
                        </Link>
                        <Link v-if="authUser?.roles?.includes('tour_guide')" href="/guide/dashboard" class="turget-mobile-sublink" style="color: var(--color-blue-600); font-weight: 700;" @click="showMobileNav = false">
                            🧭 Portal Guide
                        </Link>
                        <Link v-if="authUser?.is_admin" href="/admin" class="turget-mobile-sublink" style="color: var(--color-blue-600); font-weight: 700;" @click="showMobileNav = false">
                            🛡️ Admin CMS
                        </Link>
                        <Link href="/logout" method="post" as="button" class="turget-btn turget-btn-secondary" style="width: 100%; margin-top: 0.5rem; justify-content: center;" @click="showMobileNav = false">
                            {{ t('nav.logout', 'Keluar') }}
                        </Link>
                    </div>
                </div>

                <div v-else class="turget-mobile-auth-actions">
                    <Link href="/login" class="turget-btn turget-btn-secondary" style="width: 100%; justify-content: center;" @click="showMobileNav = false">
                        {{ t('nav.login', 'Masuk') }}
                    </Link>
                    <Link href="/register" class="turget-btn turget-btn-primary" style="width: 100%; justify-content: center;" @click="showMobileNav = false">
                        {{ t('nav.register', 'Daftar Sekarang') }}
                    </Link>
                </div>

                <!-- Language Selection in Mobile Drawer -->
                <div class="turget-mobile-lang-row">
                    <span style="font-size: 0.8rem; font-weight: 600; color: var(--color-text-muted);">Pilih Bahasa:</span>
                    <div class="turget-lang-pill">
                        <button type="button" class="turget-lang-btn" :class="{ 'is-active': locale === 'id' }" @click="switchLang('id')">ID</button>
                        <button type="button" class="turget-lang-btn" :class="{ 'is-active': locale === 'en' }" @click="switchLang('en')">EN</button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Mobile Drawer Backdrop Overlay -->
        <div
            v-if="showMobileNav"
            class="turget-mobile-backdrop"
            @click="showMobileNav = false"
        ></div>

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
