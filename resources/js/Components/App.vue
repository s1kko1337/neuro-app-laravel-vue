<template>
  <div class="flex flex-col flex-1 min-h-screen bg-neutral-200">
      <div class="flex h-1/5 bg-cyan-700 items-center justify-between w-full">
          <div class="flex">navigation</div>
          <div :class="['flex items-center justify-center', currentRouter === '/chat' ? 'hidden' : '']">
            <router-link to="/chat">
                <div class="shadow-md px-16 py-2 rounded-lg bg-emerald-800 hover:bg-emerald-700 hover:scale-95 transition-transform duration-300 ease-in-out">
                    Открыть чат
                </div>
            </router-link>
          </div>
      </div>
      <div class="flex flex-1 h-full w-full">
          <div v-if="currentRouter !== '/'" class="flex-1">
            <router-view></router-view>
          </div>
      </div>
      <div class="flex h-1/5 justify-center bg-cyan-700">
          <span>footer</span>
      </div>
  </div>
</template>




<script>
import {computed, onMounted, ref} from 'vue';
import {useRouterStore} from "@/stores/routerStore.js";
import {useRouter} from "vue-router";

    export default {
        setup() {
            const routerStore = useRouterStore()
            const router = useRouter()


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
                currentRouter
            }
        }
    }
</script>
