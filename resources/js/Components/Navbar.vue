<template>
    <nav class="w-full bg-gradient-to-r from-indigo-50 via-white to-blue-50 border-b border-indigo-100 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <router-link to="/" class="transform transition-all duration-300 hover:scale-[1.02]">
                    <div class="text-xl font-bold text-gray-800 hover:text-indigo-600 transition-colors duration-300">
                        AI Chat
                    </div>
                </router-link>
                <div class="flex space-x-6 ml-auto items-center">
                    <router-link to="/chat">
                        <div
                            class="text-lg font-semibold text-gray-800 hover:text-indigo-600 transition-colors duration-300">
                            Чат
                        </div>
                    </router-link>
                    <router-link to="/about">
                        <div
                            class="text-lg font-semibold text-gray-800 hover:text-indigo-600 transition-colors duration-300">
                            О нас
                        </div>
                    </router-link>
                    <router-link to="/contact">
                        <div
                            class="text-lg font-semibold text-gray-800 hover:text-indigo-600 transition-colors duration-300">
                            Контакты
                        </div>
                    </router-link>
                    <router-link v-if="needSurvey" to="/chat-survey">
                        <div
                            class="text-lg font-semibold text-gray-800 hover:text-indigo-600 transition-colors duration-300">
                            Пройти опрос
                        </div>
                    </router-link>
                    <template v-if="!authStore.isAuthenticated">
                        <router-link
                            :to="{name:'register'}"
                            class="px-4 py-2 rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold transition-all duration-300 transform hover:scale-[1.02] hover:shadow-md"
                        >Регистрация
                        </router-link>
                        <router-link
                            :to="{name:'login'}"
                            class="px-4 py-2 rounded-lg bg-white border border-indigo-200 text-indigo-600 font-semibold hover:bg-indigo-50 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-md"
                        >Войти
                        </router-link>
                    </template>

                    <!-- Кнопки для авторизованных пользователей -->
                    <template v-else>
                        <button
                            @click="handleLogout"
                            class="px-4 py-2 rounded-lg bg-white border border-indigo-200 text-indigo-600 font-semibold hover:bg-indigo-50 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-md flex items-center gap-2"
                        >
                            <LogOut size="16"/>
                            Выход
                        </button>
                    </template>
                    <ThemeToggle/>
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
import {useRouter} from 'vue-router';
import {computed, onMounted, ref} from "vue";
import ThemeToggle from "./ThemeToggle.vue";
import {useAuthStore} from "../stores/authStore.js";
import {LogOut} from "lucide-vue-next";

export default {
    name: 'Navbar',
    components: {
        ThemeToggle,
        LogOut
    },
    setup() {
        const router = useRouter();
        const needSurvey = ref(true);
        const authStore = useAuthStore();
        onMounted(async () => {
            const response = await axios.get('/api/v1/survey')
            needSurvey.value = response.data
        })

        const currentRouter = computed(() => router.currentRoute.value.path);
        const handleLogout = async () => {
            await authStore.logout();
            await router.push({name: 'Main'});
        };
        return {
            currentRouter,
            handleLogout,
            needSurvey,
            authStore
        };
    }
}
</script>

