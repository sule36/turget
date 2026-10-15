import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useI18n() {
    const page = usePage();

    const locale = computed(() => page.props.locale || 'id');
    const translations = computed(() => page.props.translations || {});

    /**
     * Translate a dot-nested UI key, e.g. t('nav.destinations')
     */
    function t(key, fallback = '') {
        const parts = key.split('.');
        let current = translations.value;

        for (const part of parts) {
            if (current && typeof current === 'object' && part in current) {
                current = current[part];
            } else {
                return fallback || key;
            }
        }

        return typeof current === 'string' ? current : (fallback || key);
    }

    /**
     * Pick language from a translatable JSON object { id: '...', en: '...' }
     */
    function tr(value) {
        if (!value) return '';
        if (typeof value === 'string') return value;
        if (typeof value === 'object') {
            const currentLang = locale.value;
            const fallbackLang = currentLang === 'id' ? 'en' : 'id';
            return value[currentLang] || value[fallbackLang] || Object.values(value)[0] || '';
        }
        return '';
    }

    return {
        locale,
        t,
        tr,
    };
}
