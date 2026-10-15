<script setup>
import { Link, usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useI18n } from '@/composables/useI18n';

const page = usePage();
const { locale, t } = useI18n();

const authUser = computed(() => page.props.auth?.user);
const flash = computed(() => page.props.flash || {});

function switchLang(lang) {
    if (lang === locale.value) return;
    router.post(`/locale/${lang}`, {}, {
        preserveScroll: true,
        preserveState: false,
    });
}
</script>

<template>
    <div class="turget-admin-layout">
        <!-- Sidebar -->
        <aside class="turget-admin-sidebar">
            <div style="margin-bottom: 2.5rem; display: flex; align-items: center; justify-content: space-between;">
                <Link href="/admin" class="turget-brand" style="color: white; font-size: 1.25rem;">
                    <span class="turget-brand-icon" style="width: 2rem; height: 2rem; font-size: 1rem;">T</span>
                    <span>turget<span class="turget-brand-dot">.id</span></span>
                </Link>
                <span class="turget-badge" style="font-size: 0.65rem; padding: 0.15rem 0.45rem; background: rgba(37,99,235,0.25); color: var(--color-blue-400); border: none;">
                    CMS
                </span>
            </div>

            <nav style="display: flex; flex-direction: column; flex: 1;">
                <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-text-subtle); margin-bottom: 0.6rem; padding-left: 0.75rem;">
                    Editorial Modul
                </div>

                <Link
                    href="/admin"
                    class="turget-admin-nav-item"
                    :class="{ 'is-active': $page.url === '/admin' }"
                >
                    <span>📊</span>
                    <span>{{ t('cms.dashboard', 'Dashboard') }}</span>
                </Link>

                <Link
                    href="/admin/destinations"
                    class="turget-admin-nav-item"
                    :class="{ 'is-active': $page.url.startsWith('/admin/destinations') }"
                >
                    <span>🏛️</span>
                    <span>{{ t('cms.destinations', 'Destinasi') }}</span>
                </Link>

                <Link
                    href="/admin/regions"
                    class="turget-admin-nav-item"
                    :class="{ 'is-active': $page.url.startsWith('/admin/regions') }"
                >
                    <span>🗺️</span>
                    <span>{{ t('cms.regions', 'Wilayah & Kota') }}</span>
                </Link>

                <Link
                    href="/admin/articles"
                    class="turget-admin-nav-item"
                    :class="{ 'is-active': $page.url.startsWith('/admin/articles') }"
                >
                    <span>✍️</span>
                    <span>{{ t('cms.articles', 'Artikel & Panduan') }}</span>
                </Link>

                <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-text-subtle); margin: 1.25rem 0 0.6rem; padding-left: 0.75rem;">
                    Marketplace & KYC
                </div>

                <Link
                    href="/admin/guides"
                    class="turget-admin-nav-item"
                    :class="{ 'is-active': $page.url.startsWith('/admin/guides') }"
                >
                    <span>🛡️</span>
                    <span>Verifikasi Tour Guide</span>
                </Link>

                <Link
                    href="/admin/packages"
                    class="turget-admin-nav-item"
                    :class="{ 'is-active': $page.url.startsWith('/admin/packages') }"
                >
                    <span>🎒</span>
                    <span>Moderasi Paket Wisata</span>
                </Link>

                <Link
                    href="/admin/vendors"
                    class="turget-admin-nav-item"
                    :class="{ 'is-active': $page.url.startsWith('/admin/vendors') }"
                >
                    <span>🏨</span>
                    <span>Verifikasi Vendor Mitra</span>
                </Link>

                <Link
                    href="/admin/vendor-products"
                    class="turget-admin-nav-item"
                    :class="{ 'is-active': $page.url.startsWith('/admin/vendor-products') }"
                >
                    <span>📦</span>
                    <span>Katalog Layanan & Add-on</span>
                </Link>

                <div style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.08em; color: var(--color-text-subtle); margin: 1.25rem 0 0.6rem; padding-left: 0.75rem;">
                    Finansial & Escrow
                </div>

                <Link
                    href="/admin/escrow"
                    class="turget-admin-nav-item"
                    :class="{ 'is-active': $page.url.startsWith('/admin/escrow') }"
                >
                    <span>🛡️</span>
                    <span>Buku Besar Escrow & Komisi</span>
                </Link>

                <Link
                    href="/admin/payouts"
                    class="turget-admin-nav-item"
                    :class="{ 'is-active': $page.url.startsWith('/admin/payouts') }"
                >
                    <span>🏦</span>
                    <span>Pencairan Dana (Payouts)</span>
                </Link>

                <Link
                    href="/admin/refunds"
                    class="turget-admin-nav-item"
                    :class="{ 'is-active': $page.url.startsWith('/admin/refunds') }"
                >
                    <span>⚖️</span>
                    <span>Sengketa & Refund</span>
                </Link>

                <div style="margin-top: auto; padding-top: 1.5rem; border-top: 1px solid rgba(255,255,255,0.08);">
                    <Link href="/" class="turget-admin-nav-item" style="color: var(--color-sand-200);">
                        <span>🌐</span>
                        <span>Lihat Website Publik</span>
                    </Link>
                </div>
            </nav>
        </aside>

        <!-- Main Workspace -->
        <div class="turget-admin-main">
            <!-- Header -->
            <header class="turget-admin-header">
                <div style="display: flex; align-items: center; gap: 0.75rem;">
                    <span style="font-size: 0.88rem; font-weight: 700; color: var(--color-navy-950);">
                        Turget.id Editorial CMS
                    </span>
                    <span class="turget-badge" style="margin: 0; background: var(--color-sand-100); color: var(--color-navy-800); border-color: var(--color-border-subtle);">
                        RBAC Protected
                    </span>
                </div>

                <div style="display: flex; align-items: center; gap: 1.25rem;">
                    <!-- Language Switcher Pill -->
                    <div class="turget-lang-pill">
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

                    <div style="display: flex; align-items: center; gap: 0.65rem;">
                        <div style="text-align: right;">
                            <div style="font-size: 0.88rem; font-weight: 700; color: var(--color-navy-950);">
                                {{ authUser?.name }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--color-text-muted);">
                                {{ authUser?.roles?.join(', ') || 'Administrator' }}
                            </div>
                        </div>

                        <Link
                            href="/logout"
                            method="post"
                            as="button"
                            class="turget-btn turget-btn-secondary turget-btn-sm"
                        >
                            {{ t('nav.logout', 'Keluar') }}
                        </Link>
                    </div>
                </div>
            </header>

            <!-- Flash Alert -->
            <div v-if="flash.success || flash.error" style="padding: 1.5rem 2rem 0;">
                <div v-if="flash.success" class="turget-flash turget-flash-success" style="margin-bottom: 0;">
                    <span>✓ {{ flash.success }}</span>
                </div>
                <div v-if="flash.error" class="turget-flash turget-flash-error" style="margin-bottom: 0;">
                    <span>⚠ {{ flash.error }}</span>
                </div>
            </div>

            <!-- Content -->
            <div class="turget-admin-content">
                <slot />
            </div>
        </div>
    </div>
</template>
