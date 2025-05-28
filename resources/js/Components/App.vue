<template>
    <div class="w-full min-h-screen bg-primary flex flex-col">
        <div
            :class="[currentRouter !== '/' && currentRouter !== '/about' && currentRouter !== '/contact' && currentRouter !== '/chat-survey' ? 'hidden' : '']">
            <Navbar/>
        </div>
        <div :class="['flex flex-1 items-center justify-center', currentRouter !== '/' ? 'hidden' : '']">

            <div class="max-w-2xl text-center space-y-8 p-6">
                <TypewritterText text="Welcome to AI Chat Assistant" :speed="100"></TypewritterText>
                <p class="text-xl text-gray-900">
                    Your intelligent conversation partner powered by advanced AI
                </p>
                <button @click="navigateToChat"
                        class="px-8 py-3 mt-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-lg">
                    Start Chatting
                </button>
            </div>
        </div>

        <div class="flex-1">
            <div v-if="currentRouter !== '/'" class="flex-1">
                <router-view></router-view>
            </div>
        </div>
    </div>
</template>


<script>
import {computed, onMounted, ref, onBeforeUnmount, watch, nextTick} from 'vue';
import {useRouterStore} from "../stores/routerStore.js";
import Navbar from "./Navbar.vue";
import ThemeToggle from "./ThemeToggle.vue";
import TypewritterText from "./TypewritterText.vue";
import {useRouter} from 'vue-router';
import {useAuthStore} from '../stores/authStore.js';

export default {
    components: {
        Navbar,
        ThemeToggle,
        TypewritterText,
    },
    setup() {
        const router = useRouter();
        const authStore = useAuthStore();
        const initializing = ref(true);
        const isScrolled = ref(false);

        // Обработчик прокрутки страницы
        const handleScroll = () => {
            isScrolled.value = window.scrollY > 50;
        };

        // Обработчик выхода из системы
        const handleLogout = async () => {
            await authStore.logout();
            router.push({name: 'login'});
        };

        // Function to navigate to chat with user ID
        const navigateToChat = async () => {
            if (!authStore.initialized) {
                await authStore.initialize();
            }

            if (authStore.isAuthenticated) {
                router.push(`/chat/${authStore.userId}`);
            } else {
                router.push('/login');
            }
        };

        // Перенаправление на требуемую страницу в зависимости от аутентификации
        const redirectIfNeeded = async () => {
            const currentRoute = router.currentRoute.value;

            // Если пользователь авторизован и находится на странице входа или регистрации,
            // перенаправляем на страницу GET
            if (authStore.isAuthenticated &&
                (currentRoute.name === 'login' || currentRoute.name === 'register')) {
                router.push({name: 'chat'});
            }

            // Если пользователь не авторизован и пытается получить доступ к защищенным маршрутам
            if (!authStore.isAuthenticated && currentRoute.meta?.requiresAuth) {
                router.push({name: 'login'});
            }

            // Если маршрут требует верификации email, проверяем статус
            if (authStore.isAuthenticated && currentRoute.meta?.requiresVerification) {
                try {
                    const verificationStatus = await authStore.checkVerificationStatus();
                    if (verificationStatus && !verificationStatus.verified) {
                        // Если email не подтвержден, перенаправляем на страницу верификации
                        router.push({name: 'emailVerification'});
                    }
                } catch (error) {
                    console.error('Error checking verification status:', error);
                }
            }
        };

        // Инициализация при монтировании компонента
        onMounted(async () => {
            try {
                // Добавляем слушатель события прокрутки
                window.addEventListener('scroll', handleScroll);

                // Инициализируем auth store и восстанавливаем сессию
                await authStore.initialize();
                await nextTick();
                // Перенаправляем если нужно
                redirectIfNeeded();
            } catch (error) {
                console.error('App initialization error:', error);
            } finally {
                initializing.value = false;
            }
        });

        // Следим за изменениями состояния аутентификации
        const unsubscribe = authStore.$subscribe((mutation, state) => {
            // При изменении статуса аутентификации проверяем, нужно ли перенаправить
            if (mutation.events.key === 'token' || mutation.events.key === 'user') {
                redirectIfNeeded();
            }
        });

        // Очищаем подписки при уничтожении компонента
        onBeforeUnmount(() => {
            unsubscribe();
            window.removeEventListener('scroll', handleScroll);
        });

        const currentRouter = computed(() => router.currentRoute.value.path);

        return {
            currentRouter,
            navigateToChat,
            handleLogout
        }
    }
}
</script>
