<template>
    <div class="flex flex-1 items-center justify-center py-12">
        <div
            class="max-w-2xl text-center space-y-8 p-6 bg-white rounded-2xl shadow-xl transform transition-all duration-300 hover:shadow-2xl">
            <div class="h-2 bg-gradient-to-r from-indigo-400 to-purple-600 rounded-t-lg"></div>

            <h1 class="text-3xl md:text-4xl font-bold text-gray-800">Контакты</h1>


            <a
                href="https://github.com/finesko1"
                target="_blank"
                class="flex items-center justify-center space-x-2 px-4 py-3 rounded-lg bg-white border border-indigo-200 hover:border-indigo-400 hover:shadow-md transition-all duration-300"
            >
                <Github size="20" class="text-indigo-600"/>
                <span
                    class="text-gray-800 hover:text-indigo-600 transition-colors duration-300">github.com/finesko1</span>
            </a>

            <a
                href="https://github.com/s1kko1337"
                target="_blank"
                class="flex items-center justify-center space-x-2 px-4 py-3 rounded-lg bg-white border border-indigo-200 hover:border-indigo-400 hover:shadow-md transition-all duration-300"
            >
                <Github size="20" class="text-indigo-600"/>
                <span
                    class="text-gray-800 hover:text-indigo-600 transition-colors duration-300">github.com/s1kko1337</span>
            </a>

        </div>
    </div>
</template>

<script>
import {computed} from "vue";
import {useRouterStore} from "../stores/routerStore.js";
import {useRouter} from "vue-router";
import {Users, Github} from "lucide-vue-next";

export default {
    name: 'Contact',
    components: {
        Users,
        Github
    },
    setup() {
        const router = useRouter();
        const currentRouter = computed(() => router.currentRoute.value.path);

        return {
            currentRouter,
        }
    }
}
</script>

<style scoped>
/* Any custom styles can go here */
</style>
