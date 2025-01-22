<template>
    <button
    @click="toggleTheme"
    class="text-gray-900 hover:text-accent"
    >
        <component :is="currentIcon" size="20" />
    </button>
</template>

<script>
import {ref, computed, onMounted, watch} from 'vue';
import { Moon,Sun } from 'lucide-vue-next';

export default {
    name:'ThemeToggle',
    setup() {
        const currentTheme = ref('light');

        const toggleTheme = () => {
            currentTheme.value = currentTheme.value === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', currentTheme.value);
        };

        const currentIcon = computed(() => {
            return currentTheme.value === 'light' ? Moon : Sun;
        });

        onMounted(async () => {
            const savedTheme = localStorage.getItem('theme') || 'light';
            currentTheme.value = savedTheme;
            document.documentElement.setAttribute('data-theme', savedTheme);


            watch(currentTheme, (newTheme) => {
                localStorage.setItem('theme', newTheme);
            });
        });
            return {
                toggleTheme,
                currentIcon,
                currentTheme,
            };
    }
}
</script>

<style scoped>

</style>
