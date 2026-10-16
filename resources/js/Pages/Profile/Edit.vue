<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const props = defineProps({
    user: Object,
});

const page = usePage();
const { t, locale } = useI18n();

const profileForm = useForm({
    name: props.user.name || '',
    email: props.user.email || '',
    phone: props.user.phone || '',
});

const passwordForm = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

function updateProfile() {
    profileForm.put('/profile', {
        preserveScroll: true,
    });
}

function updatePassword() {
    passwordForm.put('/profile/password', {
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
}
</script>

<template>
    <PublicLayout>
        <Head :title="locale === 'id' ? 'Pengaturan Profil & Keamanan — Turget.id' : 'Profile & Security Settings — Turget.id'" />

        <div style="background: var(--color-surface-subtle); padding: 3rem 0 6rem;">
            <div class="turget-container" style="max-width: 860px;">
                <!-- Header -->
                <div style="margin-bottom: 2rem; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
                    <div>
                        <span class="turget-badge" style="background: var(--color-blue-50); color: var(--color-blue-600); margin-bottom: 0.5rem;">
                            ⚙️ {{ locale === 'id' ? 'Akun Saya' : 'My Account' }}
                        </span>
                        <h1 style="font-family: var(--font-serif); font-size: 2.25rem; font-weight: 700; color: var(--color-navy-950);">
                            {{ locale === 'id' ? 'Pengaturan Profil & Keamanan' : 'Profile & Security' }}
                        </h1>
                        <p style="color: var(--color-text-muted); font-size: 0.95rem;">
                            {{ locale === 'id'
                                ? 'Kelola informasi identitas, nomor kontak untuk sistem, dan kata sandi akun Anda.'
                                : 'Manage your identity information, contact phone, and account security credentials.' }}
                        </p>
                    </div>

                    <!-- User Snapshot -->
                    <div style="display: flex; align-items: center; gap: 1rem; background: white; padding: 0.75rem 1.25rem; border-radius: var(--radius-xl); border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-xs);">
                        <div style="width: 3rem; height: 3rem; border-radius: 50%; background: var(--color-navy-900); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.2rem;">
                            {{ user.name?.charAt(0) || 'U' }}
                        </div>
                        <div>
                            <div style="font-weight: 700; color: var(--color-navy-950); font-size: 0.95rem;">
                                {{ user.name }}
                            </div>
                            <div style="font-size: 0.78rem; color: var(--color-blue-600); font-weight: 600;">
                                {{ user.roles?.join(', ') || 'Wisatawan' }}
                            </div>
                        </div>
                    </div>
                </div>

                <div style="display: flex; flex-direction: column; gap: 2rem;">
                    <!-- CARD 1: PERSONAL INFORMATION -->
                    <div style="background: white; border-radius: var(--radius-xl); padding: 2.25rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-sm);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                            <span style="font-size: 1.25rem;">👤</span>
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin: 0;">
                                {{ locale === 'id' ? 'Informasi Pribadi' : 'Personal Information' }}
                            </h2>
                        </div>
                        <p style="font-size: 0.88rem; color: var(--color-text-muted); margin-bottom: 1.75rem;">
                            {{ locale === 'id'
                                ? 'Nama lengkap ini akan tertera pada voucher perjalanan resmi dan komunikasi sistem.'
                                : 'This name will be displayed on official travel vouchers and system notifications.' }}
                        </p>

                        <form @submit.prevent="updateProfile">
                            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                                <div>
                                    <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.4rem;">
                                        {{ locale === 'id' ? 'Nama Lengkap *' : 'Full Name *' }}
                                    </label>
                                    <input
                                        v-model="profileForm.name"
                                        type="text"
                                        class="turget-input"
                                        required
                                        style="border-radius: var(--radius-md);"
                                    />
                                    <div v-if="profileForm.errors.name" style="color: var(--color-coral-600); font-size: 0.82rem; margin-top: 0.25rem;">
                                        {{ profileForm.errors.name }}
                                    </div>
                                </div>

                                <div>
                                    <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.4rem;">
                                        {{ locale === 'id' ? 'Alamat Email *' : 'Email Address *' }}
                                    </label>
                                    <input
                                        v-model="profileForm.email"
                                        type="email"
                                        class="turget-input"
                                        required
                                        style="border-radius: var(--radius-md);"
                                    />
                                    <div v-if="profileForm.errors.email" style="color: var(--color-coral-600); font-size: 0.82rem; margin-top: 0.25rem;">
                                        {{ profileForm.errors.email }}
                                    </div>
                                </div>

                                <div>
                                    <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.4rem;">
                                        {{ locale === 'id' ? 'Nomor Telepon / Kontak Darurat' : 'Phone / Emergency Contact' }}
                                    </label>
                                    <input
                                        v-model="profileForm.phone"
                                        type="tel"
                                        class="turget-input"
                                        placeholder="Contoh: 081234567890"
                                        style="border-radius: var(--radius-md);"
                                    />
                                    <div style="font-size: 0.78rem; color: var(--color-text-subtle); margin-top: 0.25rem;">
                                        🔒 {{ locale === 'id' ? 'Nomor telepon disimpan privat dan hanya digunakan untuk keperluan konfirmasi sistem & darurat.' : 'Phone number is strictly private for system verification and emergencies.' }}
                                    </div>
                                    <div v-if="profileForm.errors.phone" style="color: var(--color-coral-600); font-size: 0.82rem; margin-top: 0.25rem;">
                                        {{ profileForm.errors.phone }}
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 1.75rem; display: flex; justify-content: flex-end;">
                                <button
                                    type="submit"
                                    class="turget-btn turget-btn-primary"
                                    :disabled="profileForm.processing"
                                    style="font-weight: 700; padding: 0.75rem 1.75rem;"
                                >
                                    {{ profileForm.processing ? 'Menyimpan...' : (locale === 'id' ? 'Simpan Perubahan Profil' : 'Save Changes') }}
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- CARD 2: PASSWORD & SECURITY -->
                    <div style="background: white; border-radius: var(--radius-xl); padding: 2.25rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-sm);">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.35rem;">
                            <span style="font-size: 1.25rem;">🔒</span>
                            <h2 style="font-size: 1.25rem; font-weight: 700; color: var(--color-navy-950); margin: 0;">
                                {{ locale === 'id' ? 'Ubah Kata Sandi Akun' : 'Change Password' }}
                            </h2>
                        </div>
                        <p style="font-size: 0.88rem; color: var(--color-text-muted); margin-bottom: 1.75rem;">
                            {{ locale === 'id'
                                ? 'Pastikan akun Anda menggunakan kata sandi yang panjang dan acak demi keamanan akun.'
                                : 'Ensure your account is protected with a strong, secure password.' }}
                        </p>

                        <form @submit.prevent="updatePassword">
                            <div style="display: flex; flex-direction: column; gap: 1.25rem;">
                                <div>
                                    <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.4rem;">
                                        {{ locale === 'id' ? 'Kata Sandi Saat Ini *' : 'Current Password *' }}
                                    </label>
                                    <input
                                        v-model="passwordForm.current_password"
                                        type="password"
                                        class="turget-input"
                                        required
                                        style="border-radius: var(--radius-md);"
                                    />
                                    <div v-if="passwordForm.errors.current_password" style="color: var(--color-coral-600); font-size: 0.82rem; margin-top: 0.25rem;">
                                        {{ passwordForm.errors.current_password }}
                                    </div>
                                </div>

                                <div>
                                    <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.4rem;">
                                        {{ locale === 'id' ? 'Kata Sandi Baru * (Min. 8 karakter)' : 'New Password * (Min. 8 chars)' }}
                                    </label>
                                    <input
                                        v-model="passwordForm.password"
                                        type="password"
                                        class="turget-input"
                                        required
                                        style="border-radius: var(--radius-md);"
                                    />
                                    <div v-if="passwordForm.errors.password" style="color: var(--color-coral-600); font-size: 0.82rem; margin-top: 0.25rem;">
                                        {{ passwordForm.errors.password }}
                                    </div>
                                </div>

                                <div>
                                    <label class="turget-label" style="font-weight: 700; display: block; margin-bottom: 0.4rem;">
                                        {{ locale === 'id' ? 'Konfirmasi Kata Sandi Baru *' : 'Confirm New Password *' }}
                                    </label>
                                    <input
                                        v-model="passwordForm.password_confirmation"
                                        type="password"
                                        class="turget-input"
                                        required
                                        style="border-radius: var(--radius-md);"
                                    />
                                    <div v-if="passwordForm.errors.password_confirmation" style="color: var(--color-coral-600); font-size: 0.82rem; margin-top: 0.25rem;">
                                        {{ passwordForm.errors.password_confirmation }}
                                    </div>
                                </div>
                            </div>

                            <div style="margin-top: 1.75rem; display: flex; justify-content: flex-end;">
                                <button
                                    type="submit"
                                    class="turget-btn turget-btn-primary"
                                    :disabled="passwordForm.processing"
                                    style="font-weight: 700; padding: 0.75rem 1.75rem;"
                                >
                                    {{ passwordForm.processing ? 'Memperbarui...' : (locale === 'id' ? 'Perbarui Kata Sandi' : 'Update Password') }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
