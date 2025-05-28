
<template>
    <nav class="w-full bg-secondary border-b">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 ">
            <div class="flex justify-between h-16 items-center">
                <router-link to="/">
                    <div class="text-xl font-bold text-gray-900 hover:text-accent">
                        AI Chat
                    </div>
                </router-link>
                <div class="flex space-x-4 ml-auto">
                    <router-link to="/chat">
                        <div class="text-xl font-bold text-gray-900 hover:text-accent">
                            Chat
                        </div>
                    </router-link>
                    <router-link to="/about">
                        <div class="text-xl font-bold text-gray-900 hover:text-accent">
                            About
                        </div>
                    </router-link>
                    <router-link to="/contact">
                        <div class="text-xl font-bold text-gray-900 hover:text-accent">
                            Contact
                        </div>
                    </router-link>
                    <router-link v-if="needSurvey" to="/chat-survey">
                        <div class="text-xl font-bold text-gray-900 hover:text-accent">
                            Пройти опрос
                        </div>
                    </router-link>
                    <template v-if="!authStore.isAuthenticated">
                        <router-link
                            :to="{name:'register'}"
                            class="text-xl font-bold text-gray-900 hover:text-accent"
                        >Регистрация
                        </router-link>
                        <router-link
                            :to="{name:'login'}"
                            class="text-xl font-bold text-gray-900 hover:text-accent"
                        >Войти
                        </router-link>
                    </template>

                    <!-- Кнопки для авторизованных пользователей -->
                    <template v-else>
                        <button
                            @click="handleLogout"
                            class="text-xl font-bold text-gray-900 hover:text-accent"
                        >Выход
                        </button>
                    </template>
                    <ThemeToggle />
                </div>
            </div>
        </div>
    </nav>
</template>

<script>
import { useRouter } from 'vue-router';
import {computed, onMounted, ref} from "vue";
import ThemeToggle from "./ThemeToggle.vue";
import {useAuthStore} from "../stores/authStore.js";

export default {
    name: 'Navbar',
    components: { ThemeToggle },
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

