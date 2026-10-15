<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import PublicLayout from '@/Layouts/PublicLayout.vue';
import { useI18n } from '@/composables/useI18n';

const { t } = useI18n();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}
</script>

<template>
    <PublicLayout>
        <Head title="Masuk ke Akun Turget.id" />

        <div class="turget-container" style="padding: 5rem 0 7rem; max-width: 480px;">
            <div style="background: white; border-radius: var(--radius-xl); padding: 2.75rem 2.25rem; border: 1px solid var(--color-border-subtle); box-shadow: var(--shadow-lg);">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <span class="turget-brand-icon" style="margin: 0 auto 1rem; width: 3rem; height: 3rem; font-size: 1.5rem;">T</span>
                    <h1 style="font-family: var(--font-serif); font-size: 1.85rem; font-weight: 700; color: var(--color-navy-950); margin-bottom: 0.35rem;">
                        {{ t('nav.login', 'Masuk ke Turget.id') }}
                    </h1>
                    <p style="font-size: 0.92rem; color: var(--color-text-muted);">
                        Akses perjalanan, booking tersimpan, dan kelola pengalaman Anda.
                    </p>
                </div>

                <!-- Test account hint pill -->
                <div style="background: var(--color-blue-50); border: 1px solid rgba(37,99,235,0.2); border-radius: var(--radius-md); padding: 0.85rem 1rem; font-size: 0.82rem; color: var(--color-navy-800); margin-bottom: 1.5rem;">
                    <strong>Akun Demo Admin CMS:</strong><br>
                    Email: <code>admin@turget.id</code><br>
                    Password: <code>password</code>
                </div>

                <form @submit.prevent="submit" style="display: flex; flex-direction: column; gap: 1.25rem;">
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
                            placeholder="••••••••"
                            style="width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--color-border-subtle); border-radius: var(--radius-md); font-size: 0.95rem;"
                        />
                        <div v-if="form.errors.password" style="color: var(--color-coral-600); font-size: 0.82rem; margin-top: 0.3rem;">
                            {{ form.errors.password }}
                        </div>
                    </div>

                    <div style="display: flex; align-items: center; justify-content: space-between; font-size: 0.85rem;">
                        <label style="display: flex; align-items: center; gap: 0.5rem; cursor: pointer;">
                            <input type="checkbox" v-model="form.remember" />
                            <span>Ingat saya</span>
                        </label>
                    </div>

                    <button
                        type="submit"
                        class="turget-btn turget-btn-primary"
                        style="width: 100%; padding: 0.85rem; font-size: 1rem;"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Memproses...' : 'Masuk Sekarang' }}
                    </button>
                </form>

                <div style="text-align: center; margin-top: 2rem; font-size: 0.88rem; color: var(--color-text-muted);">
                    Belum punya akun?
                    <Link href="/register" style="color: var(--color-blue-600); font-weight: 700;">
                        Daftar Gratis
                    </Link>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
