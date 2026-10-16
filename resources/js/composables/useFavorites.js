import { router, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useFavorites() {
    const page = usePage();

    const favoriteIds = computed(() => page.props.favoriteIds || { guide: [], package: [], destination: [] });

    function isFavorited(type, id) {
        if (!type || !id) return false;
        const list = favoriteIds.value[type] || [];
        return list.includes(Number(id));
    }

    function toggleFavorite(type, id) {
        if (!page.props.auth?.user) {
            router.visit('/login');
            return;
        }

        router.post('/favorites/toggle', {
            type,
            id: Number(id),
        }, {
            preserveScroll: true,
            preserveState: true,
        });
    }

    return {
        favoriteIds,
        isFavorited,
        toggleFavorite,
    };
}
