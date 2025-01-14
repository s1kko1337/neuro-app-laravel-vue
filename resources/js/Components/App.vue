<template>
  <h1>Hello, {{ userName }}!</h1>
</template>




<script>
import {onMounted, ref} from 'vue';
import {useRouter} from "vue-router";

    export default {
        setup() {
            const userName = ref('Mikle')


            onMounted(async () => {
                const lastVisitedRoute = routerStore.lastVisitedRoute;
                const router = useRouter()
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


            return {
                userName
            }
        }
    }
</script>
