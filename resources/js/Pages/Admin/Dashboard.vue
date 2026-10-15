<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    stats: Object,
    pendingReviews: Array,
    recentRevisions: Array,
    recentAuditLogs: Array,
});

const { t } = useI18n();
</script>

<template>
    <AdminLayout>
        <Head title="CMS Dashboard — Turget.id" />

        <div style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: flex-end;">
            <div>
                <h1 style="font-family: var(--font-serif); font-size: 2.2rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.25rem;">
                    Editorial CMS Dashboard
                </h1>
                <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                    Kelola hierarki destinasi, artikel editorial, dan alur persetujuan publikasi.
                </p>
            </div>

            <Link href="/admin/destinations/create" class="turget-btn turget-btn-primary">
                + Tambah Destinasi Baru
            </Link>
        </div>

        <!-- Metrics Cards Grid -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.25rem; margin-bottom: 2.5rem;">
            <div style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-xs);">
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                    Total Destinasi
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--color-navy-950); margin: 0.25rem 0;">
                    {{ stats.destinations_total }}
                </div>
                <div style="font-size: 0.82rem; color: var(--color-emerald-600); font-weight: 600;">
                    ✓ {{ stats.destinations_published }} Terbit ke Publik
                </div>
            </div>

            <div style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-xs);">
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                    Menunggu Review
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--color-amber-600); margin: 0.25rem 0;">
                    {{ stats.destinations_in_review }}
                </div>
                <div style="font-size: 0.82rem; color: var(--color-text-muted);">
                    Butuh persetujuan editor
                </div>
            </div>

            <div style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-xs);">
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                    Wilayah & Kota
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--color-navy-950); margin: 0.25rem 0;">
                    {{ stats.regions_count }}
                </div>
                <div style="font-size: 0.82rem; color: var(--color-text-muted);">
                    Hierarki provinsi & kab/kota
                </div>
            </div>

            <div style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-xs);">
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                    Artikel Editorial
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--color-navy-950); margin: 0.25rem 0;">
                    {{ stats.articles_count }}
                </div>
                <div style="font-size: 0.82rem; color: var(--color-blue-600); font-weight: 600;">
                    {{ stats.articles_published }} Panduan Aktif
                </div>
            </div>

            <div style="background: white; border-radius: var(--radius-lg); padding: 1.5rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-xs);">
                <div style="font-size: 0.8rem; font-weight: 700; color: var(--color-text-subtle); text-transform: uppercase;">
                    Tour Guide Terdaftar
                </div>
                <div style="font-size: 2rem; font-weight: 800; color: var(--color-navy-950); margin: 0.25rem 0;">
                    {{ stats.guides_total || 0 }}
                </div>
                <div style="font-size: 0.82rem; color: var(--color-emerald-600); font-weight: 600;">
                    🛡️ {{ stats.guides_verified || 0 }} Terverifikasi ({{ stats.guides_pending || 0 }} Antrean KYC)
                </div>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 3fr 2fr; gap: 2rem;">
            <!-- Pending Review Queue & Revisions -->
            <div style="display: flex; flex-direction: column; gap: 2rem;">
                <!-- Pending Approvals -->
                <div style="background: white; border-radius: var(--radius-xl); padding: 1.75rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-xs);">
                    <div style="display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.25rem;">
                        <h2 style="font-family: var(--font-serif); font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950);">
                            Antrean Review Konten
                        </h2>
                        <span class="turget-badge" style="margin: 0; background: var(--color-amber-50); color: var(--color-amber-600);">
                            {{ pendingReviews.length }} Tertunda
                        </span>
                    </div>

                    <div v-if="pendingReviews.length > 0" style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div
                            v-for="item in pendingReviews"
                            :key="item.id"
                            style="display: flex; align-items: center; justify-content: space-between; padding: 0.85rem 1rem; background: var(--color-surface-subtle); border-radius: var(--radius-md);"
                        >
                            <div>
                                <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.95rem;">
                                    {{ item.name }}
                                </div>
                                <div style="font-size: 0.8rem; color: var(--color-text-muted);">
                                    Diperbarui oleh {{ item.editor }} • {{ item.updated_at }}
                                </div>
                            </div>
                            <Link :href="`/admin/destinations/${item.id}/edit`" class="turget-btn turget-btn-sm turget-btn-primary">
                                Tinjau & Setujui →
                            </Link>
                        </div>
                    </div>
                    <div v-else style="text-align: center; padding: 2.5rem 1rem; color: var(--color-text-muted); font-size: 0.95rem;">
                        ✓ Semua draf telah ditinjau. Tidak ada antrean tertunda.
                    </div>
                </div>

                <!-- Recent Revisions -->
                <div style="background: white; border-radius: var(--radius-xl); padding: 1.75rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-xs);">
                    <h2 style="font-family: var(--font-serif); font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        Jejak Perubahan & Audit Trail (PRD §6.2)
                    </h2>

                    <div style="display: flex; flex-direction: column; gap: 0.75rem;">
                        <div
                            v-for="rev in recentRevisions"
                            :key="rev.id"
                            style="display: flex; align-items: flex-start; justify-content: space-between; gap: 1rem; padding: 0.75rem 0; border-bottom: 1px solid var(--color-border-subtle); font-size: 0.88rem;"
                        >
                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.2rem;">
                                    <span class="turget-badge" style="margin: 0; padding: 0.15rem 0.5rem; font-size: 0.7rem;">
                                        {{ rev.action }}
                                    </span>
                                    <span style="font-weight: 700; color: var(--color-navy-950);">
                                        {{ rev.user?.name || 'Sistem' }}
                                    </span>
                                </div>
                                <div style="color: var(--color-text-muted); font-size: 0.82rem;">
                                    {{ rev.note || 'Pembaruan data konten' }}
                                </div>
                            </div>
                            <div style="font-size: 0.75rem; color: var(--color-text-subtle); white-space: nowrap;">
                                {{ new Date(rev.created_at).toLocaleTimeString() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Audit Logs Feed -->
            <div>
                <div style="background: white; border-radius: var(--radius-xl); padding: 1.75rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-xs);">
                    <h2 style="font-family: var(--font-serif); font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 1.25rem;">
                        Log Aktivitas Administratif
                    </h2>

                    <div style="display: flex; flex-direction: column; gap: 0.85rem;">
                        <div
                            v-for="log in recentAuditLogs"
                            :key="log.id"
                            style="padding: 0.75rem; background: var(--color-surface-subtle); border-radius: var(--radius-md); font-size: 0.85rem;"
                        >
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                <strong style="color: var(--color-blue-600);">{{ log.action }}</strong>
                                <span style="font-size: 0.75rem; color: var(--color-text-subtle);">
                                    {{ new Date(log.created_at).toLocaleTimeString() }}
                                </span>
                            </div>
                            <div style="color: var(--color-navy-950);">
                                Oleh: {{ log.user?.name || 'Administrator' }}
                            </div>
                            <div v-if="log.ip_address" style="font-size: 0.75rem; color: var(--color-text-muted); margin-top: 0.2rem;">
                                IP: {{ log.ip_address }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
