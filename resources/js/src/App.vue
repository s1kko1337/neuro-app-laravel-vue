<template>
    <div>
        <h1>Hello, {{ username }}!</h1>
        <p>Response from Ollama: {{ ollamaResponse }}</p>
        <button @click="fetchOllamaResponse">Get Ollama Response</button>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import axios from 'axios';

axios.defaults.baseURL = 'http://localhost';

const username = ref("Vue test name");
const ollamaResponse = ref("");

const fetchOllamaResponse = async () => {
    try {
        const response = await axios.get('/api/test-ollama');
        ollamaResponse.value = response.data?.response || 'Нет ответа';
    } catch (error) {
        console.error("Ошибка получение данных с олламы:", {
            message: error.message,
            response: error.response?.data,
            stack: error.stack,
        });
    }
};
</script>
