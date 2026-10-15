<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
}
</script>

<template>
    <PublicLayout>
        <Head title="Daftar Akun Baru Turget.id" />

        <div class="turget-container" style="padding: 5rem 0 7rem; max-width: 480px;">
            <div style="background: white; border-radius: var(--radius-xl); padding: 2.75rem 2.25rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-lg);">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <span class="turget-brand-icon" style="margin: 0 auto 1rem; width: 3rem; height: 3rem; font-size: 1.5rem;">T</span>
                    <h1 style="font-family: var(--font-serif); font-size: 1.85rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.35rem;">
                        {{ t('nav.register', 'Daftar Akun Baru') }}
                    </h1>
                    <p style="font-size: 0.92rem; color: var(--color-text-muted);">
                        Bergabunglah dengan komunitas wisatawan dan guide lokal Turget.id.
                    </p>
                </div>

                <form @submit.prevent="submit" style="display: flex; flex-direction: column; gap: 1.25rem;">
                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.4rem;">
                            Nama Lengkap
                        </label>
                        <input
                            type="text"
                            v-model="form.name"
                            required
                            placeholder="Nama Anda"
                            style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--color-border-subtle); border-radius: var(--radius-md); font-size: 0.95rem;"
                        />
                        <div v-if="form.errors.name" style="color: var(--color-coral-600); font-size: 0.82rem; margin-top: 0.3rem;">
                            {{ form.errors.name }}
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.4rem;">
                            Email
                        </label>
                        <input
                            type="email"
                            v-model="form.email"
                            required
                            placeholder="nama@email.com"
                            style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--color-border-subtle); border-radius: var(--radius-md); font-size: 0.95rem;"
                        />
                        <div v-if="form.errors.email" style="color: var(--color-coral-600); font-size: 0.82rem; margin-top: 0.3rem;">
                            {{ form.errors.email }}
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.4rem;">
                            Kata Sandi
                        </label>
                        <input
                            type="password"
                            v-model="form.password"
                            required
                            placeholder="Minimal 8 karakter"
                            style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--color-border-subtle); border-radius: var(--radius-md); font-size: 0.95rem;"
                        />
                        <div v-if="form.errors.password" style="color: var(--color-coral-600); font-size: 0.82rem; margin-top: 0.3rem;">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <div>
                        <label style="display: block; font-size: 0.88rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.4rem;">
                            Konfirmasi Kata Sandi
                        </label>
                        <input
                            type="password"
                            v-model="form.password_confirmation"
                            required
                            placeholder="Ulangi kata sandi"
                            style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--color-border-subtle); border-radius: var(--radius-md); font-size: 0.95rem;"
                        />
                    </div>

                    <button
                        type="submit"
                        class="turget-btn turget-btn-primary"
                        style="width: 100%; padding: 0.85rem; font-size: 1rem;"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Mendaftarkan...' : 'Daftar Sekarang' }}
                    </button>
                </form>

                <div style="text-align: center; margin-top: 2rem; font-size: 0.88rem; color: var(--color-text-muted);">
                    Sudah memiliki akun?
                    <Link href="/login" style="color: var(--color-blue-600); font-weight: 700;">
                        Masuk di Sini
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
