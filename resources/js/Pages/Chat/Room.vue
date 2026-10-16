<script setup>
import { ref, computed, onMounted, onUnmounted, nextTick } from 'vue';
import { Head, Link, useForm, usePage, router } from '@inertiajs/vue3';
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
const messageInput = ref(null);
let pollTimer = null;

// Quick suggested inquiry questions (Tokopedia/Shopee style)
const quickSuggestions = computed(() => {
    return locale.value === 'id' ? [
        '📅 Apakah jadwal tanggal ini masih tersedia?',
        '👥 Apakah rute ini ramah lansia & anak-anak?',
        '📍 Di mana titik kumpul atau opsi penjemputan?',
        '🎒 Apa saja perlengkapan khusus yang wajib dibawa?',
        '⏱️ Berapa perkiraan durasi total aktivitas ini?',
    ] : [
        '📅 Is this date currently available?',
        '👥 Is this itinerary suitable for families & kids?',
        '📍 Where is the exact meeting or pickup point?',
        '🎒 What essential gear should we bring along?',
        '⏱️ What is the estimated total tour duration?',
    ];
});

function pickSuggestion(text) {
    form.message = text;
    nextTick(() => {
        if (messageInput.value) {
            messageInput.value.focus();
        }
    });
}

// Real-time client-side off-platform detection (Tokopedia & Shopee standard)
const isSuspiciousInput = computed(() => {
    const text = form.message;
    if (!text || text.length < 3) return false;

    // Detect Indonesian phone patterns, spaces between digits, WA mentions, bank keywords, or emails
    const phonePattern = /(?:\+?62|0|[oO])\s*8[0-9oO\s\.\-\(\)]{6,}/;
    const keywordPattern = /\b(?:wa|whatsapp|watsap|wea|w\.a|w\/a|japri|telegram|tele|rekening|rek\s*(?:bca|mandiri|bri|bni)|transfer\s*langsung|tf\s*langsung|bca|mandiri|bri|bni|dana|ovo|gopay)\b/i;
    const emailPattern = /[a-zA-Z0-9._%+-]+(?:\s*@\s*|\s*\[at\]\s*)[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/i;
    const socialPattern = /\b(?:ig|instagram|line|tiktok)\s*[:=]?\s*@?[a-zA-Z0-9_]{3,}/i;

    return phonePattern.test(text) || keywordPattern.test(text) || emailPattern.test(text) || socialPattern.test(text);
});

function scrollToBottom() {
    nextTick(() => {
        if (chatContainer.value) {
            chatContainer.value.scrollTop = chatContainer.value.scrollHeight;
        }
    });
}

onMounted(() => {
    scrollToBottom();

    // Live chat polling every 4 seconds for fresh messages without full reload
    pollTimer = setInterval(() => {
        router.reload({
            only: ['messages'],
            preserveScroll: true,
            onSuccess: () => {
                scrollToBottom();
            },
        });
    }, 4000);
});

onUnmounted(() => {
    if (pollTimer) {
        clearInterval(pollTimer);
    }
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
        <Head :title="`Live Chat — ${otherUser?.name || 'Mitra'} | Turget.id`" />

        <div style="background: var(--color-surface-subtle); padding: 1.75rem 0 5rem;">
            <div class="turget-container" style="max-width: 920px;">
                <!-- Conversation Header Card -->
                <div style="background: white; border-radius: var(--radius-xl) var(--radius-xl) 0 0; padding: 1.25rem 1.75rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle); border-bottom: none; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.85rem;">
                        <Link href="/chat" class="turget-btn turget-btn-secondary turget-btn-sm" style="padding: 0.4rem 0.75rem;">
                            ← {{ locale === 'id' ? 'Kotak Masuk' : 'Inbox' }}
                        </Link>

                        <div style="width: 2.75rem; height: 2.75rem; border-radius: 50%; background: var(--color-blue-50); color: var(--color-blue-600); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.15rem; border: 2px solid var(--color-blue-200);">
                            {{ (otherUser?.name || 'U').charAt(0) }}
                        </div>

                        <div>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <h2 style="font-size: 1.15rem; font-weight: 700; color: var(--color-navy-950); margin: 0;">
                                    {{ otherUser?.name }}
                                </h2>
                                <span class="turget-badge" style="background: #ECFDF5; color: #065F46; border-color: #A7F3D0; font-size: 0.72rem; padding: 0.15rem 0.5rem; margin: 0;">
                                    🛡️ Terverifikasi
                                </span>
                            </div>
                            <span v-if="conversation.trip_inquiry" style="font-size: 0.8rem; color: var(--color-blue-600); font-weight: 600;">
                                Terkait Rencana Trip: {{ conversation.trip_inquiry.title }}
                            </span>
                            <span v-else style="font-size: 0.78rem; color: var(--color-text-subtle);">
                                Obrolan Langsung & Konsultasi Wisata Resmi
                            </span>
                        </div>
                    </div>

                    <div v-if="conversation.trip_inquiry">
                        <Link :href="`/trips/${conversation.trip_inquiry.id}`" class="turget-btn turget-btn-secondary turget-btn-sm">
                            Detail Rencana Trip ↗
                        </Link>
                    </div>
                </div>

                <!-- Tokopedia/Shopee Anti-Disintermediation Security Banner -->
                <div class="turget-chat-security-banner">
                    <span class="turget-chat-security-icon">🛡️</span>
                    <div>
                        <div class="turget-chat-security-title">
                            {{ locale === 'id' ? 'Sistem Keamanan Anti-Penipuan & Proteksi Escrow Aktif' : 'Anti-Fraud & Escrow Protection Active' }}
                        </div>
                        <div style="line-height: 1.45;">
                            {{ locale === 'id'
                                ? 'Dilarang membagikan kontak pribadi (Nomor WhatsApp/HP, Email, Rekening Bank, atau Akun Medsos). Transaksi di luar sistem Turget.id berisiko tinggi dan tidak dilindungi garansi Rekening Bersama (Escrow).'
                                : 'Do not share personal contacts (WhatsApp/Phone numbers, Email, Bank accounts, or Social media). Transactions outside Turget.id forfeit Escrow protection and insurance guarantees.' }}
                        </div>
                    </div>
                </div>

                <!-- Chat Messages Box -->
                <div
                    ref="chatContainer"
                    style="background: white; border-left: 1px solid var(--color-border-subtle); border-right: 1px solid var(--color-border-subtle); padding: 1.75rem 2rem; height: 480px; overflow-y: auto; display: flex; flex-direction: column; gap: 1.25rem;"
                >
                    <div v-if="!messages.length" style="text-align: center; color: var(--color-text-subtle); margin: auto; font-size: 0.95rem;">
                        <div style="font-size: 2.75rem; margin-bottom: 0.5rem;">💬</div>
                        <div style="font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.25rem;">
                            {{ locale === 'id' ? 'Mulai Percakapan Aman' : 'Start Secure Conversation' }}
                        </div>
                        <p style="max-width: 420px; margin: 0 auto; font-size: 0.85rem; color: var(--color-text-muted);">
                            {{ locale === 'id'
                                ? 'Tanyakan ketersediaan tanggal, penyesuaian rute perjalanan, kebutuhan logistik, atau titik temu.'
                                : 'Ask about schedule availability, itinerary customization, logistics, or meeting points.' }}
                        </p>
                    </div>

                    <div
                        v-for="msg in messages"
                        :key="msg.id"
                        style="display: flex; flex-direction: column; max-width: 76%;"
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
                            style="padding: 0.85rem 1.25rem; border-radius: var(--radius-lg); font-size: 0.92rem; line-height: 1.5; word-break: break-word; box-shadow: var(--shadow-xs);"
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

                        <!-- Tokopedia/Shopee style Masked Contact Security Pill -->
                        <div v-if="msg.is_flagged" class="turget-chat-masked-pill">
                            <span>🔒</span>
                            <span>{{ locale === 'id' ? 'Kontak disensor otomatis oleh sistem keamanan Turget' : 'Off-platform contact masked by Turget security' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Input Footer with Quick Suggestion Chips & Typing Warning -->
                <div style="background: white; border-radius: 0 0 var(--radius-xl) var(--radius-xl); padding: 1.25rem 1.75rem; box-shadow: var(--shadow-sm); border: 1px solid var(--color-border-subtle); border-top: 1px solid var(--color-surface-muted);">
                    <!-- Quick Question Chips -->
                    <div class="turget-chat-quick-chips">
                        <button
                            v-for="(chip, cIdx) in quickSuggestions"
                            :key="cIdx"
                            type="button"
                            class="turget-chat-chip-btn"
                            @click="pickSuggestion(chip)"
                        >
                            {{ chip }}
                        </button>
                    </div>

                    <!-- Real-time Typing Warning for Off-Platform Leaks -->
                    <div v-if="isSuspiciousInput" class="turget-chat-typing-warning">
                        <span>⚠️</span>
                        <span>
                            {{ locale === 'id'
                                ? 'Peringatan Keamanan: Terdeteksi nomor kontak / rekening / kata kunci luar. Demi garansi Escrow, sistem akan otomatis menyamarkan kontak pribadi Anda.'
                                : 'Security Warning: Contact details or payment keywords detected. System will mask off-platform contact to ensure Escrow protection.' }}
                        </span>
                    </div>

                    <!-- Message Form -->
                    <form @submit.prevent="sendMessage" style="display: flex; gap: 0.75rem; align-items: center;">
                        <input
                            ref="messageInput"
                            v-model="form.message"
                            type="text"
                            class="turget-input"
                            :placeholder="locale === 'id' ? 'Tulis pesan Anda untuk pemandu / mitra...' : 'Type your message to guide / vendor...'"
                            style="flex: 1; border-radius: var(--radius-pill); padding: 0.75rem 1.25rem;"
                            :disabled="form.processing"
                        />
                        <button
                            type="submit"
                            class="turget-btn turget-btn-primary"
                            style="border-radius: var(--radius-pill); padding: 0.75rem 1.6rem;"
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
