<template>
    <div :class="['flex flex-1 items-center justify-center']">
        <div class="max-w-2xl text-center space-y-8 p-6">
            <p class="text-xl text-gray-900">
                Your intelligent conversation partner powered by advanced AI
            </p>
            <div class="card border p-4 bg-secondary shadow-lg">
                <h2 class="text-2xl font-semibold text-gray-900">Contacts 🔗</h2>
                <p class="text-md text-gray-900 text-pretty">
                    <a href="https://github.com/finesko1" target="_blank" class="hover:underline">
                        https://github.com/finesko1
                    </a>
                </p>
                <p class="text-md text-gray-900 text-pretty">
                    <a href="https://github.com/s1kko1337" target="_blank" class="hover:underline">
                        https://github.com/s1kko1337
                    </a>
                </p>
            </div>
        </div>
    </div>
</template>


<script>
import {computed} from "vue";
import {useRouterStore} from "../stores/routerStore.js";
import {useRouter} from "vue-router";

export default{
    name:'Contact',
    setup(){
        const router = useRouter();

        const currentRouter = computed(() => router.currentRoute.value.path);

        return {
            currentRouter,
        }
    }
}
</script>

<style scoped>

</style>
