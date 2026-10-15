<script setup>
import { Head, Link } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    conversations: Object,
});

const { t, locale } = useI18n();
</script>

<template>
    <PublicLayout>
        <Head :title="locale === 'id' ? 'Pesan & Percakapan — Turget.id' : 'Messages & Chat — Turget.id'" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container" style="max-width: 860px;">
                <!-- Header -->
                <div style="margin-bottom: 2rem;">
                    <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); margin-bottom: 0.5rem;">
                        💬 {{ locale === 'id' ? 'In-App Messenger' : 'In-App Messenger' }}
                    </span>
                    <h1 style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: var(--color-navy-950);">
                        {{ locale === 'id' ? 'Kotak Pesan Percakapan' : 'Messages & Inquiries' }}
                    </h1>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                        {{ locale === 'id'
                            ? 'Komunikasi aman antara wisatawan dan tour guide/vendor lokal. Dilengkapi proteksi anti-transaksi luar platform.'
                            : 'Secure messaging with guides and vendors. Protected by anti-off-platform contact masking.' }}
                    </p>
                </div>

                <!-- Empty State -->
                <div v-if="!conversations.data?.length" style="background: white; border-radius: var(--radius-xl); padding: 5rem 2rem; text-align: center; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-sm);">
                    <div style="font-size: 3rem; margin-bottom: 1rem;">💬</div>
                    <h3 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.5rem;">
                        {{ locale === 'id' ? 'Belum Ada Percakapan' : 'No Conversations Yet' }}
                    </h3>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                        {{ locale === 'id'
                            ? 'Percakapan akan otomatis dimulai ketika Anda menghubungi tour guide atau mengajukan rencana trip.'
                            : 'Chats start automatically when you reach out to a guide or plan a trip.' }}
                    </p>
                </div>

                <!-- Conversation List -->
                <div v-else style="display: flex; flex-direction: column; gap: 1rem;">
                    <Link
                        v-for="conv in conversations.data"
                        :key="conv.id"
                        :href="`/chat/${conv.id}`"
                        style="text-decoration: none; color: inherit; background: white; border-radius: var(--radius-xl); padding: 1.5rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle); display: flex; align-items: center; justify-content: space-between; gap: 1.25rem; transition: var(--transition-base);"
                        class="turget-card-hover"
                    >
                        <div style="display: flex; align-items: center; gap: 1.25rem;">
                            <!-- Avatar Initial -->
                            <div style="width: 3.25rem; height: 3.25rem; border-radius: 50%; background: var(--color-blue-50); color: var(--color-blue-600); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem;">
                                {{ (conv.user_one?.name || 'U').charAt(0) }}
                            </div>

                            <div>
                                <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                                    <h3 style="font-size: 1.1rem; font-weight: 700; color: var(--color-navy-950);">
                                        {{ conv.user_one?.id === $page.props.auth?.user?.id ? conv.user_two?.name : conv.user_one?.name }}
                                    </h3>
                                    <span v-if="conv.trip_inquiry" class="turget-badge" style="background: var(--color-sand-100); color: var(--color-navy-900); font-size: 0.72rem; margin: 0;">
                                        Trip: {{ conv.trip_inquiry.title?.slice(0, 30) }}...
                                    </span>
                                </div>

                                <p style="font-size: 0.88rem; color: var(--color-text-muted); max-width: 450px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ conv.messages?.[0]?.clean_body || 'Mulai percakapan baru...' }}
                                </p>
                            </div>
                        </div>

                        <div style="text-align: right; font-size: 0.8rem; color: var(--color-text-subtle);">
                            <span>Buka Chat →</span>
                        </div>
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
