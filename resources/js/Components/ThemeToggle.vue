<template>
    <button
        @click="toggleTheme"
        class="p-2 rounded-full transition-all duration-300 transform hover:scale-110 hover:shadow-md"
        :class="[
            currentTheme === 'light'
                ? 'bg-gradient-to-r from-indigo-50 to-blue-50 text-indigo-600'
                : 'bg-gradient-to-r from-indigo-900 to-purple-900 text-white'
        ]"
        aria-label="Toggle theme"
    >
        <component :is="currentIcon" size="20" />
    </button>
</template>

<script>
import { ref, computed, onMounted, watch } from 'vue';
import { Moon, Sun } from 'lucide-vue-next';

export default {
    name: 'ThemeToggle',
    components: {
        Moon,
        Sun
    },
    setup() {
        const currentTheme = ref('light');

        const toggleTheme = () => {
            currentTheme.value = currentTheme.value === 'light' ? 'dark' : 'light';
            document.documentElement.setAttribute('data-theme', currentTheme.value);
            document.documentElement.classList.toggle('dark', currentTheme.value === 'dark');
        };

        const currentIcon = computed(() => {
            return currentTheme.value === 'light' ? Moon : Sun;
        });

        onMounted(() => {
            // Check for system preference first
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

            // Then check for saved preference
            const savedTheme = localStorage.getItem('theme') || (prefersDark ? 'dark' : 'light');

            currentTheme.value = savedTheme;
            document.documentElement.setAttribute('data-theme', savedTheme);
            document.documentElement.classList.toggle('dark', savedTheme === 'dark');

            watch(currentTheme, (newTheme) => {
                localStorage.setItem('theme', newTheme);
                document.documentElement.classList.toggle('dark', newTheme === 'dark');
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
