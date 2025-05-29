<template>
    <div class="min-h-screen w-full bg-gradient-to-br from-indigo-50 via-white to-blue-50 p-4 md:p-8">
        <div class="w-full max-w-full mx-auto bg-white rounded-2xl shadow-lg p-4 md:p-6 border border-indigo-100">
            <!-- Settings Panel -->
            <div class="flex flex-col sm:flex-row gap-4 mb-6">
                <div class="flex-1">
                    <label class="block text-gray-700 mb-2 font-medium">Язык озвучивания:</label>
                    <select v-model="selectedLanguage" class="w-full p-2 border border-indigo-200 rounded-lg focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 outline-none transition-all duration-300">
                        <option value="ru-RU">Русский</option>
                        <option value="en-US">Английский (США)</option>
                        <option value="de-DE">Немецкий</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-gray-700 mb-2 font-medium">Модель озвучивания:</label>
                    <select v-model="selectedProvider" class="w-full p-2 border border-indigo-200 rounded-lg focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 outline-none transition-all duration-300">
                        <option value="yandex">yandex</option>
                        <option value="espeak">eSpeak</option>
                    </select>
                </div>
            </div>

            <!-- Microphone -->
            <div class="relative flex-shrink-0 mb-6">
                <div
                    @click="toggleRecording"
                    class="relative w-32 h-32 md:w-40 md:h-40 mx-auto cursor-pointer transition-all duration-300"
                    :class="{ 'scale-110': isRecording }"
                >
                    <!-- Main circle -->
                    <div
                        class="absolute inset-0 rounded-full flex items-center justify-center transition-all shadow-md"
                        :class="[
                            isRecording
                                ? 'bg-gradient-to-r from-red-500 to-red-600 shadow-lg shadow-red-200'
                                : 'bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700'
                        ]"
                    >
                        <Mic class="w-12 h-12 md:w-16 md:h-16 text-white" />
                    </div>
                    <!-- Pulse animation -->
                    <div
                        v-if="isRecording"
                        class="absolute inset-0 border-2 border-red-300 rounded-full animate-ping-slow"
                    ></div>
                </div>
            </div>

            <!-- Chat history header and toggle -->
            <div class="mb-2 flex items-center justify-between cursor-pointer rounded-lg hover:bg-indigo-50 p-2 transition-colors duration-200" @click="toggleChat">
                <h3 class="text-gray-700 font-medium">История чата</h3>
                <ChevronDown
                    class="w-5 h-5 text-indigo-600 transform transition-transform duration-200"
                    :class="{'rotate-180': !isChatExpanded}"
                />
            </div>

            <!-- Chat history container -->
            <div class="mb-6 overflow-hidden transition-all duration-300 ease-in-out rounded-lg border border-indigo-100"
                 :class="[
                    isChatExpanded ? 'max-h-[38rem]' : 'max-h-24',
                    'overflow-y-auto'
                 ]">
                <div class="bg-gradient-to-br from-indigo-50 via-white to-blue-50 p-4 space-y-4 min-h-[6rem]">
                    <div v-for="(message, index) in chatHistory" :key="index"
                         :class="[
                            'flex transition-all duration-300',
                            message.role === 'assistant' ? 'justify-start' : 'justify-end',
                            !isChatExpanded && 'scale-90 opacity-80'
                         ]">
                        <div :class="[
                                'max-w-xs sm:max-w-sm md:max-w-md p-3 rounded-lg transition-all duration-300 relative shadow-sm',
                                message.role === 'assistant'
                                    ? 'bg-white border border-indigo-100 text-gray-800'
                                    : 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white',
                                !isChatExpanded && 'p-2 max-w-[200px] text-xs'
                            ]">
                            <!-- Message content -->
                            <p class="text-sm transition-all mb-2 pr-6"
                               :class="!isChatExpanded && 'text-xs leading-tight'">
                                {{ message.content }}
                            </p>

                            <!-- Play button -->
                            <button v-if="message.role === 'assistant' && message.audio_url"
                                    @click.stop="toggleAudio(message)"
                                    class="absolute top-2 right-2 p-1 hover:bg-indigo-100/30 rounded-full transition-colors">
                                <Play v-if="currentAudioId !== message.id" class="w-4 h-4" />
                                <Pause v-else class="w-4 h-4" />
                            </button>

                            <!-- Timestamp -->
                            <p class="text-xs mt-1 opacity-70 transition-opacity"
                               :class="!isChatExpanded && 'opacity-0 h-0'">
                                {{ message.timestamp }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input area -->
            <div class="flex gap-4 border-t pt-4 border-indigo-100">
                <!-- Text input -->
                <div class="flex-1">
                    <textarea
                        v-model="userInput"
                        @keyup.enter="sendTextMessage"
                        class="w-full p-2 border border-indigo-200 rounded-lg focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 outline-none transition-all duration-300"
                        rows="2"
                        placeholder="Введите текст сообщения..."
                    ></textarea>
                </div>

                <!-- Send button -->
                <button
                    @click="sendTextMessage"
                    :disabled="isSending"
                    class="self-end bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white py-2 px-4 rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg disabled:opacity-50 disabled:transform-none disabled:hover:scale-100"
                >
                    <span v-if="isSending">Отправка...</span>
                    <Send v-else class="w-5 h-5" />
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import {ref, onMounted, watch, nextTick} from 'vue';
import { Mic, Play, Pause, Send, ChevronDown } from 'lucide-vue-next';
import axios from 'axios';

const selectedLanguage = ref('ru-RU');
const errorMessage = ref('');
const selectedProvider = ref('espeak');
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
const isChatExpanded = ref(false);

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

        const audioUrl = `/api/v1/get_audio/${message.audio_path}`;

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
            role: 'user',
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
            role: response.data.data.bot_response.role,
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

/* Custom scrollbar styling */
.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(99, 102, 241, 0.3);
    border-radius: 3px;
}

.overflow-y-auto::-webkit-scrollbar-thumb:hover {
    background-color: rgba(99, 102, 241, 0.5);
}
</style>
