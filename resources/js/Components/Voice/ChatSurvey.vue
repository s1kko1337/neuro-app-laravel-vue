<template>
    <div class="min-h-screen bg-gray-100 p-8">
        <div class="max-w-2xl mx-auto bg-white rounded-lg shadow-md p-6">
            <!-- Панель настроек -->
            <div class="flex gap-4 mb-6">
                <div class="flex-1">
                    <label class="block text-gray-700 mb-2">Язык озвучивания:</label>
                    <select v-model="selectedLanguage" class="w-full p-2 border rounded">
                        <option value="ru-RU">Русский</option>
                        <option value="en-US">Английский (США)</option>
                        <option value="de-DE">Немецкий</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-gray-700 mb-2">Модель озвучивания:</label>
                    <select v-model="selectedProvider" class="w-full p-2 border rounded">
                        <option value="yandex">yandex</option>
                        <option value="espeak">eSpeak</option>
                    </select>
                </div>
            </div>

            <!-- Микрофон -->
            <div class="relative flex-shrink-0">
                <div
                    @click="toggleRecording"
                    class="relative w-40 h-40 mx-auto cursor-pointer transition-all duration-300"
                    :class="{ 'scale-110': isRecording }"
                >
                    <!-- Основной круг -->
                    <div
                        class="absolute inset-0 rounded-full flex items-center justify-center transition-all"
                        :class="[
                       isRecording
                           ? 'bg-red-500 shadow-lg shadow-red-200'
                           : 'bg-blue-500 hover:bg-blue-600'
                   ]"
                    >
                        <svg
                            class="w-16 h-16 text-white"
                            fill="none"
                            stroke="currentColor"
                            viewBox="0 0 24 24"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 11a7 7 0 01-7 7m0 0a7 7 0 01-7-7m7 7v4m0 0H8m4 0h4m-4-8a3 3 0 01-3-3V5a3 3 0 116 0v6a3 3 0 01-3 3z"
                            />
                        </svg>
                    </div>
                    <!-- Анимация пульсации -->
                    <div
                        v-if="isRecording"
                        class="absolute inset-0 border-2 border-red-300 rounded-full animate-ping-slow"
                    ></div>
                </div>
            </div>

            <!-- Добавляем заголовок и кнопку управления -->
            <div class="mb-2 flex items-center justify-between cursor-pointer" @click="toggleChat">
                <h3 class="text-gray-600 font-medium">История чата</h3>
                <span class="transform transition-transform duration-200"
                      :class="{'rotate-180': !isChatExpanded}">
                    ▼
                </span>
            </div>
            <div class="mb-6 overflow-hidden transition-all duration-300 ease-in-out"
                 :class="[
                isChatExpanded ? 'max-h-[38rem]' : 'max-h-24',
                'overflow-y-auto']">
                <div class="bg-gray-50 rounded-lg p-4 space-y-4 min-h-[6rem]">
                    <div v-for="(message, index) in chatHistory" :key="index"
                         :class="[
                            'flex transition-all duration-300',
                            message.is_bot ? 'justify-start' : 'justify-end',
                        !isChatExpanded && 'scale-90 opacity-80']">
                        <div :class="[
                                'max-w-xs p-3 rounded-lg transition-all duration-300 relative',
                                message.is_bot
                                    ? 'bg-gray-300 text-black'
                                    : 'bg-sky-300 border text-black',
                                !isChatExpanded && 'p-2 max-w-[200px] text-xs'
                            ]">
                            <!-- Контент сообщения -->
                            <p class="text-sm transition-all mb-2 pr-6"
                               :class="!isChatExpanded && 'text-xs leading-tight'">
                                {{ message.content }}
                            </p>

                            <!-- Кнопка воспроизведения -->
                            <button v-if="message.is_bot && message.audio_url"
                                    @click.stop="toggleAudio(message)"
                                    class="absolute top-2 right-2 p-1 hover:bg-gray-400/30 rounded-full transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path v-if="currentAudioId !== message.id"
                                          stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                    <path v-else
                                          stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10 9h4v6h-4V9zM14 9h4v6h-4V9z"/>
                                </svg>
                            </button>

                            <!-- Таймстамп -->
                            <p class="text-xs mt-1 opacity-70 transition-opacity"
                               :class="!isChatExpanded && 'opacity-0 h-0'">
                                {{ message.timestamp }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Блок ввода -->
            <div class="flex gap-4">

                <!-- Текстовый ввод -->
                <div class="flex-1">
                    <textarea
                        v-model="userInput"
                        @keyup.enter="sendTextMessage"
                        class="w-full p-2 border rounded-lg"
                        rows="2"
                        placeholder="Введите текст сообщения..."
                    ></textarea>
                </div>

                <!-- Кнопка отправки -->
                <button
                    @click="sendTextMessage"
                    :disabled="isSending"
                    class="self-end bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded"
                >
                    {{ isSending ? 'Отправка...' : 'Отправить' }}
                </button>
            </div>

        </div>
    </div>
</template>

<script setup>
import {ref, onMounted, watch, nextTick} from 'vue';
import axios from 'axios';

const selectedLanguage = ref('ru-RU');
const errorMessage = ref('');
const selectedProvider = ref('yandex');
const audioUrl = ref(null);

// WebSpeech
const isRecording = ref(false);
const transcript = ref('');
const status = ref('');
let recognition = null;

// WebSocket
const chatHistory = ref([]);
const userInput = ref('');
const isSending = ref(false);
let ws = null;
const isChatExpanded = ref(true);

const toggleChat = () => {
    isChatExpanded.value = !isChatExpanded.value;
};

const loadHistory = async () => {
    try {
        const response = await axios.get('/api/v1/chat/history');
        chatHistory.value = response.data.data.reverse();
    } catch (e) {
        errorMessage.value = 'Ошибка загрузки истории';
    }
};

// Инициализация WebSocket
const initWebSocket = () => {
    // ws.value = new WebSocket(`ws://${window.location.host}/ws/chat`);
    //
    // ws.value.onmessage = (event) => {
    //     const data = JSON.parse(event.data);
    //
    //     // Обновляем только новые сообщения
    //     if (!messages.value.some(msg => msg.id === data.id)) {
    //         messages.value.push(data);
    //     }
    // };
    //
    // ws.value.onerror = (e) => {
    //     error.value = 'Ошибка соединения с сервером';
    // };
};

// Распознавание
const initWebSpeech = () => {
    if ('SpeechRecognition' in window || 'webkitSpeechRecognition' in window) {
        recognition = new (window.SpeechRecognition || window.webkitSpeechRecognition)();
        recognition.lang = 'ru-RU';
        recognition.interimResults = true;
        recognition.continuous = true;

        recognition.onresult = (event) => {
            let finalTranscript = '';
            let interimTranscript = '';

            for (let i = event.resultIndex; i < event.results.length; i++) {
                const transcript = event.results[i][0].transcript;
                if (event.results[i].isFinal) {
                    finalTranscript += transcript + ' ';
                } else {
                    interimTranscript += transcript;
                }
            }

            transcript.value = interimTranscript;

            if (finalTranscript) {
                userInput.value += finalTranscript.trim() + ' ';
            }
        };
        recognition.onerror = (event) => {
            status.value = `Ошибка: ${event.error}`;
        };
    } else {
        status.value = 'API не поддерживается вашим браузером';
    }
};

const toggleRecording = () => {
    if (!isRecording.value) {
        recognition.start();
        status.value = 'Идет распознавание...';
    } else {
        recognition.stop();
        status.value = 'Готово';
    }
    isRecording.value = !isRecording.value;
};

const currentAudioId = ref(null);
let audioElement = null;

const toggleAudio = async (message) => {
    try {
        if (currentAudioId.value === message.id) {
            audioElement.pause();
            currentAudioId.value = null;
            return;
        }

        if (audioElement) {
            audioElement.pause();
        }

        const audioUrl = `/api/v1/${message.audio_path}`;

        const response = await axios.get(audioUrl, {
            responseType: 'blob'
        });

        const blob = new Blob([response.data], { type: 'audio/wav' });
        const url = URL.createObjectURL(blob);

        audioElement = new Audio(url);
        audioElement.preload = 'auto';

        audioElement.addEventListener('error', (e) => {
            console.error('Audio error:', e.target.error);
            errorMessage.value = 'Ошибка воспроизведения';
            currentAudioId.value = null;
        });

        audioElement.addEventListener('canplaythrough', () => {
            audioElement.play();
            currentAudioId.value = message.id;
        });
        currentAudioId.value = null;
    } catch (e) {
        errorMessage.value = 'Ошибка загрузки аудио';
        currentAudioId.value = null;
    }
};

initWebSpeech();

// WebSockets
// Инициализация WebSocket
onMounted(async () => {
    await loadHistory();
    initWebSocket();
});

// Добавление сообщения в историю
const addMessage = (message) => {
    chatHistory.value.push(message);
};

// Отправка текстового сообщения
const sendTextMessage = async () => {
    if (!userInput.value.trim()) return;

    isSending.value = true;
    const userMessageContent = userInput.value; // Сохраняем сообщение перед очисткой

    try {
        // Добавляем сообщение пользователя сразу
        chatHistory.value.push({
            content: userMessageContent,
            is_bot: false,
            timestamp: new Date().toISOString(),
            audio_url: null
        });

        const response = await axios.post('/api/v1/survey', {
            content: userMessageContent,
            language: selectedLanguage.value,
            tts_provider: selectedProvider.value,
        });

        // Добавляем ответ бота
        let botMessage = chatHistory.value.push({
            content: response.data.data.bot_response.content,
            audio_url: response.data.data.bot_response.audio_url,
            audio_path: response.data.data.bot_response.audio_path,
            is_bot: true,
            timestamp: response.data.data.bot_response.created_at,
            id: response.data.data.bot_response.id
        });

        // Автопрокрутка к последнему сообщению
        nextTick(() => {
            const chatContainer = document.querySelector('.chat-container');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });

    } catch (error) {
        console.error('Error:', error);
        errorMessage.value = 'Ошибка отправки сообщения';
    } finally {
        userInput.value = '';
        isSending.value = false;
    }
};
</script>

<style scoped>
@keyframes ping-slow {
    0% {
        transform: scale(0.9);
        opacity: 1;
    }
    100% {
        transform: scale(1.5);
        opacity: 0;
    }
}

.animate-ping-slow {
    animation: ping-slow 1.5s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
