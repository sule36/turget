<script setup>
import { ref, onMounted, nextTick } from 'vue';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    conversation: Object,
    otherUser: Object,
    messages: Array,
});

const page = usePage();
const { t, locale } = useI18n();

const form = useForm({
    message: '',
});

const chatContainer = ref(null);

function scrollToBottom() {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
}

onMounted(() => {
    scrollToBottom();
});

function sendMessage() {
    if (!form.message.trim()) return;

    form.post(`/chat/${props.conversation.id}/messages`, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset('message');
            scrollToBottom();
        },
    });
}
</script>

<template>
    <PublicLayout>
        <Head :title="`Chat dengan ${otherUser?.name || 'Mitra'} — Turget.id`" />

        <div style="background: var(--color-surface-subtle); padding: 2rem 0 5rem;">
            <div class="turget-container" style="max-width: 900px;">
                <!-- Conversation Header Card -->
                <div style="background: white; border-radius: var(--radius-xl) var(--radius-xl) 0 0; padding: 1.25rem 2rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle); border-bottom: none; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <Link href="/chat" class="turget-btn turget-btn-secondary turget-btn-sm" style="padding: 0.4rem 0.75rem;">
                            ← {{ locale === 'id' ? 'Kembali' : 'Back' }}
                        </Link>

                        <div style="width: 2.75rem; height: 2.75rem; border-radius: 50%; background: var(--color-blue-50); color: var(--color-blue-600); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem;">
                            {{ (otherUser?.name || 'U').charAt(0) }}
                        </div>

                        <div>
                            <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin: 0;">
                                {{ otherUser?.name }}
                            </h2>
                            <span v-if="conversation.trip_inquiry" style="font-size: 0.8rem; color: var(--color-blue-600); font-weight: 600;">
                                Terkait Trip: {{ conversation.trip_inquiry.title }}
                            </span>
                        </div>
                    </div>

                    <div v-if="conversation.trip_inquiry">
                        <Link :href="`/trips/${conversation.trip_inquiry.id}`" class="turget-btn turget-btn-secondary turget-btn-sm">
                            Lihat Rencana Trip ↗
                        </Link>
                    </div>
                </div>

                <!-- Anti-Off-Platform Alert Banner (PRD §13.2) -->
                <div style="background: #FFFBEB; border-left: 4px solid var(--color-amber-500); border-right: 1px solid var(--color-border-subtle); padding: 0.9rem 1.75rem; display: flex; align-items: center; gap: 0.75rem; font-size: 0.85rem; color: #92400E;">
                    <span style="font-size: 1.25rem;">🛡️</span>
                    <div>
                        <strong>{{ locale === 'id' ? 'Perlindungan Transaksi & Anti-Bocor Aktif:' : 'Escrow Protection Active:' }}</strong>
                        {{ locale === 'id'
                            ? 'Demi keamanan dana dan perlindungan garansi 100% Escrow, nomor HP, WhatsApp, Telegram, email, dan rekening bank otomatis disamarkan oleh sistem.'
                            : 'To protect your funds via 100% Escrow, phone numbers, WhatsApp, emails, and external bank accounts are automatically masked.' }}
                    </div>
                </div>

                <!-- Chat Messages Scrollable Box -->
                <div
                    ref="chatContainer"
                    style="background: white; border-left: 1px solid var(--color-border-subtle); border-right: 1px solid var(--color-border-subtle); padding: 2rem; height: 480px; overflow-y: auto; display: flex; flex-direction: column; gap: 1.25rem;"
                >
                    <div v-if="!messages.length" style="text-align: center; color: var(--color-text-subtle); margin: auto; font-size: 0.95rem;">
                        <div style="font-size: 2.5rem; margin-bottom: 0.5rem;">👋</div>
                        {{ locale === 'id' ? 'Percakapan baru dimulai. Sapa dan diskusikan rencana perjalanan Anda!' : 'Start the conversation! Discuss itinerary details.' }}
                    </div>

                    <div
                        v-for="msg in messages"
                        :key="msg.id"
                        style="display: flex; flex-direction: column; max-width: 72%;"
                        :style="{
                            alignSelf: msg.sender_id === $page.props.auth?.user?.id ? 'flex-end' : 'flex-start',
                            alignItems: msg.sender_id === $page.props.auth?.user?.id ? 'flex-end' : 'flex-start'
                        }"
                    >
                        <div style="font-size: 0.75rem; color: var(--color-text-subtle); margin-bottom: 0.25rem; padding: 0 0.5rem;">
                            {{ msg.sender?.name?.split(' ')[0] }} • {{ new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' }) }}
                        </div>

                        <!-- Message Bubble -->
                        <div
                            style="padding: 0.9rem 1.25rem; border-radius: var(--radius-lg); font-size: 0.92rem; line-height: 1.5; word-break: break-word; box-shadow: var(--shadow-xs);"
                            :style="{
                                background: msg.sender_id === $page.props.auth?.user?.id ? 'var(--color-navy-900)' : 'var(--color-surface-subtle)',
                                color: msg.sender_id === $page.props.auth?.user?.id ? 'white' : 'var(--color-navy-950)',
                                border: msg.sender_id === $page.props.auth?.user?.id ? 'none' : '1px solid var(--color-border-subtle)',
                                borderBottomRightRadius: msg.sender_id === $page.props.auth?.user?.id ? '2px' : 'var(--radius-lg)',
                                borderBottomLeftRadius: msg.sender_id === $page.props.auth?.user?.id ? 'var(--radius-lg)' : '2px',
                            }"
                        >
                            {{ msg.clean_body }}
                        </div>

                        <!-- Flagged Warning Tag -->
                        <div v-if="msg.is_flagged" style="margin-top: 0.35rem; font-size: 0.72rem; color: #D97706; display: flex; align-items: center; gap: 0.35rem;">
                            <span>⚠️</span> {{ locale === 'id' ? 'Kontak luar disamarkan oleh sistem Turget' : 'Off-platform contact masked' }}
                        </div>
                    </div>
                </div>

                <!-- Input Footer -->
                <div style="background: white; border-radius: 0 0 var(--radius-xl) var(--radius-xl); padding: 1.25rem 2rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle); border-top: 1px solid var(--color-surface-muted);">
                    <form @submit.prevent="sendMessage" style="display: flex; gap: 1rem; align-items: center;">
                        <input
                            v-model="form.message"
                            type="text"
                            class="turget-input"
                            :placeholder="locale === 'id' ? 'Tulis pesan Anda di sini...' : 'Type your message...'"
                            style="flex: 1; border-radius: var(--radius-pill); padding: 0.75rem 1.25rem;"
                            :disabled="form.processing"
                        />
                        <button
                            type="submit"
                            class="turget-btn turget-btn-primary"
                            style="border-radius: var(--radius-pill); padding: 0.75rem 1.75rem;"
                            :disabled="form.processing || !form.message.trim()"
                        >
                            {{ locale === 'id' ? 'Kirim ➔' : 'Send ➔' }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
