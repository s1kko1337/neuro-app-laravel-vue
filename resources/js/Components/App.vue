<template>
    <div class="w-full h-screen bg-gradient-to-br from-indigo-50 via-white to-blue-50 flex flex-col">
        <div
            :class="[currentRouter !== '/' && currentRouter !== '/about' && currentRouter !== '/contact' && currentRouter !== '/chat-survey' ? 'hidden' : '']">
            <Navbar/>
        </div>

        <!-- Главный экран приветствия -->
        <div :class="['flex flex-1 items-center justify-center', currentRouter !== '/' ? 'hidden' : '']">
            <div class="max-w-2xl text-center space-y-8 p-6 bg-white rounded-2xl shadow-xl transform transition-all duration-300 hover:shadow-2xl">
                <div class="h-2 bg-gradient-to-r from-indigo-400 to-purple-600 rounded-t-lg"></div>

                <TypewritterText text="Добро пожаловать в AI чат" :speed="100" class="text-3xl md:text-4xl font-bold text-gray-800"></TypewritterText>

                <p class="text-xl text-gray-700">
                    Ваш интеллектуальный помощник с ИИ
                </p>

                <div class="flex justify-center min-w-full">
                    <button @click="navigateToChat"
                            class="px-8 py-3 mt-4 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg">
                        Начать общение
                    </button>
                </div>
            </div>
        </div>

        <!-- Контейнер для остальных компонентов -->
        <div class="flex-1 flex items-center justify-center" v-if="currentRouter !== '/'">
            <div class="w-full h-full flex items-center justify-center">
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
import {Info, CheckCircle} from "lucide-vue-next";

export default {
    components: {
        Navbar,
        ThemeToggle,
        TypewritterText,
        Info,
        CheckCircle
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
                router.push({name: 'ChatList'});
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
