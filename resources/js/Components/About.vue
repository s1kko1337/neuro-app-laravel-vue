<template>
    <div :class="['flex flex-1 items-center justify-center']">
        <div class="max-w-2xl text-center space-y-8 p-6">
            <p class="text-xl text-gray-900">
                Your intelligent conversation partner powered by advanced AI 🌐
            </p>

            <div class="card border p-4 bg-secondary shadow-lg">
                <h2 class="text-2xl font-semibold text-gray-900">Project Overview 🔍</h2>
                <p class="text-md text-gray-900 text-pretty">
                    A project to create a social network with elements of friend matching and chat with llm.
                </p>
            </div>

            <div class="card border p-4 bg-secondary shadow-lg mt-8">
                <h3 class="text-xl font-semibold text-gray-900">Theme 💡</h3>
                <p class="text-md text-gray-900 text-pretty">
                    Neural networks bring people closer together.
                </p>
            </div>

            <div class="card border p-4 bg-secondary shadow-lg mt-8">
                <h3 class="text-xl font-semibold text-gray-900">Team 🛠️</h3>
                <p class="text-md text-gray-900 text-pretty">
                    This is where the creators will be...
                </p>
            </div>

            <div class="card border p-4 bg-secondary shadow-lg mt-8">
                <h3 class="text-xl font-semibold text-gray-900">Development Plan 📈</h3>
                <p class="text-md text-gray-900 text-pretty">
                   First phase - Prototype development;
                </p>
                <p class="text-md text-gray-900 text-pretty">
                    Second phase - Testing;
                </p>
                <p class="text-md text-gray-900 text-pretty">
                    Third phase - Deployment.
                </p>
            </div>

        </div>
    </div>
</template>
<script>
import {computed} from "vue";
import {useRouter} from "vue-router";

export default{
    name:'About',
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
