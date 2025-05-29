<template>
    <div class="flex flex-1 items-center justify-center py-12">
        <div
            class="max-w-2xl text-center space-y-8 p-6 bg-white rounded-2xl shadow-xl transform transition-all duration-300 hover:shadow-2xl">
            <div class="h-2 bg-gradient-to-r from-indigo-400 to-purple-600 rounded-t-lg"></div>

            <p class="text-xl text-gray-700">
                Ваш личный помощник с искусственным интеллектом 🌐
            </p>

            <div
                class="p-6 bg-gradient-to-br from-indigo-50 via-white to-blue-50 rounded-xl shadow-md transform transition-all duration-300 hover:shadow-lg border border-indigo-100">
                <div class="flex items-center justify-center mb-4">
                    <Search size="24" class="text-indigo-600 mr-2"/>
                    <h2 class="text-2xl font-semibold text-gray-800">Project Overview</h2>
                </div>
                <p class="text-md text-gray-700 text-pretty">
                    Проект, объединяющий в себе веб интерфейс для работы с моделями, а также подбирающий пользователей по интересам.
                </p>
            </div>

            <div
                class="p-6 bg-gradient-to-br from-indigo-50 via-white to-blue-50 rounded-xl shadow-md transform transition-all duration-300 hover:shadow-lg border border-indigo-100">
                <div class="flex items-center justify-center mb-4">
                    <Lightbulb size="24" class="text-indigo-600 mr-2"/>
                    <h3 class="text-xl font-semibold text-gray-800">Тема</h3>
                </div>
                <p class="text-md text-gray-700 text-pretty">
                    Аналог ChatGPT (Нейросети сближают людей).
                </p>
            </div>

            <div
                class="p-6 bg-gradient-to-br from-indigo-50 via-white to-blue-50 rounded-xl shadow-md transform transition-all duration-300 hover:shadow-lg border border-indigo-100">
                <div class="flex items-center justify-center mb-4">
                    <Users size="24" class="text-indigo-600 mr-2"/>
                    <h3 class="text-xl font-semibold text-gray-800">Команда</h3>
                </div>
                <p class="text-md text-gray-700 text-pretty">
                    Федоров Михаил
                </p>
                <p class="text-md text-gray-700 text-pretty">
                    Ивахненко михаил
                </p>
            </div>

            <div
                class="p-6 bg-gradient-to-br from-indigo-50 via-white to-blue-50 rounded-xl shadow-md transform transition-all duration-300 hover:shadow-lg border border-indigo-100">
                <div class="flex items-center justify-center mb-4">
                    <LineChart size="24" class="text-indigo-600 mr-2"/>
                    <h3 class="text-xl font-semibold text-gray-800">План разработки</h3>
                </div>
                <div class="space-y-2">
                    <div class="flex items-center">
                        <CheckCircle size="16" class="text-indigo-600 mr-2"/>
                        <p class="text-md text-gray-700 text-pretty">
                            Первая фаза - разработка прототипа;
                        </p>
                    </div>
                    <div class="flex items-center">
                        <CheckCircle size="16" class="text-indigo-600 mr-2"/>
                        <p class="text-md text-gray-700 text-pretty">
                            Вторая фаза - тестирование;
                        </p>
                    </div>
                    <div class="flex items-center">
                        <CheckCircle size="16" class="text-indigo-600 mr-2"/>
                        <p class="text-md text-gray-700 text-pretty">
                            Третья фаза - развертывание.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import {computed} from "vue";
import {useRouter} from "vue-router";
import {Search, Lightbulb, Users, LineChart, CheckCircle} from "lucide-vue-next";

export default {
    name: 'About',
    components: {
        Search,
        Lightbulb,
        Users,
        LineChart,
        CheckCircle
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

</style>
