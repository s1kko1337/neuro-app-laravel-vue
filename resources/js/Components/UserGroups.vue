<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import { CheckCircle, Info } from "lucide-vue-next";

const groups = ref([]);
const loading = ref(false);
const matchLoading = ref(false);
const message = ref('');
const messageType = ref(''); // 'success' or 'error'

// Fetch relevant groups on component mount
onMounted(async () => {
    await fetchGroups();
});

// Function to fetch relevant groups
const fetchGroups = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/v1/relevante_group');
        groups.value = response.data.data || [];
        if (groups.value.length === 0) {
            message.value = 'Нет доступных групп';
            messageType.value = 'info';
        }
    } catch (error) {
        console.error('Error fetching groups:', error);
        message.value = 'Ошибка при загрузке групп';
        messageType.value = 'error';
    } finally {
        loading.value = false;
    }
};

// Function to trigger matching
const triggerMatch = async () => {
    matchLoading.value = true;
    message.value = '';

    try {
        const response = await axios.post('/api/v1/relevante_group');
        message.value = 'Подбор групп успешно выполнен';
        messageType.value = 'success';

        // Refresh groups list after matching
        await fetchGroups();
    } catch (error) {
        console.error('Error triggering match:', error);
        message.value = 'Ошибка при подборе групп';
        messageType.value = 'error';
    } finally {
        matchLoading.value = false;
    }
};
</script>

<template>
    <div class="w-full max-w-4xl p-6 bg-white rounded-2xl shadow-xl">
        <div class="h-2 bg-gradient-to-r from-indigo-400 to-purple-600 rounded-t-lg mb-6"></div>

        <h2 class="text-2xl font-bold text-gray-800 mb-6">Релевантные группы</h2>

        <!-- Status message -->
        <div v-if="message"
             :class="[
                'p-4 mb-6 rounded-lg flex items-center',
                messageType === 'success' ? 'bg-green-100 text-green-800' :
                messageType === 'error' ? 'bg-red-100 text-red-800' :
                'bg-blue-100 text-blue-800'
             ]">
            <CheckCircle v-if="messageType === 'success'" class="mr-2 h-5 w-5" />
            <Info v-else class="mr-2 h-5 w-5" />
            {{ message }}
        </div>

        <!-- Match button -->
        <div class="mb-8">
            <button @click="triggerMatch"
                    :disabled="matchLoading"
                    class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed">
                <span v-if="matchLoading">Подбор групп...</span>
                <span v-else>Подобрать релевантные группы</span>
            </button>
        </div>

        <!-- Groups list -->
        <div class="space-y-4">
            <div v-if="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-indigo-600"></div>
                <p class="mt-2 text-gray-600">Загрузка групп...</p>
            </div>

            <div v-else-if="groups.length === 0" class="text-center py-8 text-gray-500">
                <p>Нет доступных групп</p>
            </div>

            <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div v-for="(group, index) in groups" :key="index"
                     class="p-4 border border-gray-200 rounded-lg hover:shadow-md transition-shadow">
                    <h3 class="font-semibold text-lg">{{ group.name }}</h3>
                    <p class="text-gray-600">{{ group.description }}</p>
                    <div class="mt-2 flex flex-wrap gap-2">
                        <span v-for="(tag, tagIndex) in group.tags" :key="tagIndex"
                              class="px-2 py-1 bg-indigo-100 text-indigo-800 text-xs rounded-full">
                            {{ tag }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
