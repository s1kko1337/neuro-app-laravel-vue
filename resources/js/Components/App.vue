<template>
    <div class="w-full min-h-screen bg-primary flex flex-col">
        <div :class="[currentRouter === '/chat' ? 'hidden' : '']">
        <Navbar/>
        </div>
        <div :class="['flex flex-1 items-center justify-center', currentRouter !== '/' ? 'hidden' : '']">

            <div class="max-w-2xl text-center space-y-8 p-6">
                    <TypewritterText text="Welcome to AI Chat Assistant" :speed="100"></TypewritterText>
                    <p class="text-xl text-secondary">
                        Your intelligent conversation partner powered by advanced AI
                    </p>
                    <router-link to="/chat">
                        <div
                            class="px-8 py-3 mt-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-lg"
                        >
                            Start Chatting
                        </div>
                    </router-link>
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
import {computed, onMounted, ref, watch} from 'vue';
import {useRouterStore} from "../stores/routerStore.js";
import {useRouter} from "vue-router";
import Navbar from "./Navbar.vue";
import ThemeToggle from "./ThemeToggle.vue";
import TypewritterText from "./TypewritterText.vue";
export default {
    components: {
        Navbar,
        ThemeToggle,
        TypewritterText,
    },
    setup() {
        const routerStore = useRouterStore();
        const router = useRouter();


        onMounted(async () => {
            const lastVisitedRoute = routerStore.lastVisitedRoute;
            await new Promise(resolve => setTimeout(resolve, 1));

            const isResetPasswordRoute = router.currentRoute.value.name === 'ResetPassword' && router.currentRoute.value.params.token;
            if (isResetPasswordRoute) {
                return;
            }

            try {
                const currentRoutePath = router.currentRoute.value.path;

                // Обновленное условие перенаправления
                const authRoutes = ['/', '/login'];
                const shouldRedirect = authRoutes.includes(currentRoutePath) && lastVisitedRoute;

                if (shouldRedirect) {
                    const routeExists = router.getRoutes().some(route => route.path === lastVisitedRoute);

                    if (routeExists) {
                        await router.push(lastVisitedRoute);
                    }
                }
            } catch (error) {
                // Обработка ошибок остается без изменений
                if (error.response) {

                } else {

                }
            }
        });

        const currentRouter = computed(() => router.currentRoute.value.path);

        return {
            currentRouter,
        }
    }
}
</script>
