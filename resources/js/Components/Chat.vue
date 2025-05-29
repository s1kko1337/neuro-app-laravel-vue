<template>
    <div class="flex w-full min-h-screen bg-gradient-to-br from-indigo-50 via-white to-blue-50">
        <!-- Left sidebar - always visible on desktop -->
        <div class="hidden lg:flex flex-col w-64 border rounded-2xl m-4 p-4 bg-white shadow-lg border-indigo-100 h-screen">
            <div class="flex justify-between items-center px-4 py-2 text-gray-800">
                <router-link
                    :to="{name:'Main'}"
                    class="text-xl font-semibold text-indigo-600 hover:text-indigo-800 transition-colors duration-300"
                >На главную
                </router-link>
                <ThemeToggle :theme="theme" :setTheme="setTheme"/>
            </div>
            <!-- Chat list component with fixed height and scrollable content -->
            <div class="flex-1 overflow-hidden flex flex-col">
                <ChatList
                    :chats="chats"
                    :activeChatId="currentChatId"
                    :theme="theme"
                    ref="desktopChatList"
                    class="flex-1 overflow-y-auto"
                    @chat-deleted="handleChatDeleted"
                />
            </div>
            <!-- New chat button fixed at bottom -->
            <div class="p-4 mt-2">
                <button
                    class="w-full flex items-center justify-center space-x-2 px-4 py-3 rounded-lg text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg"
                    @click="addChatHandler"
                >
                    <span>Новый чат</span>
                </button>
            </div>
        </div>

        <!-- Mobile menu - toggleable -->
        <div v-if="isMenuOpen" class="fixed inset-0 z-50 flex lg:hidden">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="toggleMenu"></div>
            <div class="relative z-50 w-64 bg-white flex flex-col h-full shadow-xl">
                <div class="flex flex-col h-full">
                    <div class="flex flex-row justify-between mb-2 p-4">
                        <button class="text-xl font-semibold text-indigo-600 hover:text-indigo-800 transition-colors duration-300" @click="exitHandler">
                            На главную
                        </button>
                        <ThemeToggle :theme="theme" :setTheme="setTheme"/>
                        <button @click="toggleMenu" class="p-2 rounded-full hover:bg-indigo-50 transition-colors duration-300">
                            <X size="20" class="text-gray-700"/>
                        </button>
                    </div>
                    <!-- Mobile chat list with scrollable area -->
                    <div class="flex-1 overflow-y-auto px-4">
                        <ChatList
                            ref="mobileChatList"
                            :activeChatId="currentChatId"
                            :chats="chats"
                            :theme="theme"
                            @close-menu="toggleMenu"
                            @chat-deleted="handleChatDeleted"
                        />
                    </div>
                    <!-- New chat button fixed at bottom of mobile menu -->
                    <div class="p-4 border-t border-indigo-100">
                        <button
                            class="flex items-center justify-center space-x-2 w-full px-4 py-2 rounded-lg text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg"
                            @click="addChatHandler"
                        >
                            <span>Новый чат</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main content area (center) - always visible -->
        <div class="flex-1 flex flex-col border rounded-2xl m-4 p-4 border-indigo-100 bg-white shadow-lg h-screen">
            <!-- Header with menu toggle for mobile -->
            <div class="flex justify-between items-center px-4 py-2 text-gray-800">
                <div class="flex items-center space-x-2">
                    <button class="lg:hidden p-2 rounded-lg hover:bg-indigo-50 transition-colors duration-300" @click="toggleMenu">
                        <Menu size="20" class="text-indigo-600"/>
                    </button>
                    <ModelSelector @model-selected="handleModelSelected"/>
                </div>
                <div v-if="currentChatId" class="text-sm bg-gradient-to-r from-indigo-50 to-blue-50 px-3 py-1 rounded-full text-indigo-700">Chat #{{ currentChatId }}</div>
            </div>

            <!-- Empty state when no chat is selected -->
            <div v-if="!currentChatId" class="flex-1 flex items-center justify-center overflow-y-auto">
                <div class="text-center p-6 bg-gradient-to-br from-indigo-50 via-white to-blue-50 rounded-2xl shadow-md transform transition-all duration-300 hover:shadow-lg border border-indigo-100 max-w-md">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">Select a chat or start a new one</h2>
                    <button
                        class="px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg"
                        @click="addChatHandler"
                    >
                        New Chat
                    </button>
                </div>
            </div>

            <!-- Chat messages when a chat is selected with scrollable content -->
            <div v-else class="flex-1 overflow-y-auto p-4">
                <ChatMessage
                    v-for="(msg, index) in filteredMessages"
                    :key="index"
                    :message="msg.content"
                    :isAi="msg.role === 'assistant'"
                />
                <div v-if="filteredMessages.length === 0" class="flex items-center justify-center h-full text-gray-500">
                    <div class="p-6 bg-gradient-to-br from-indigo-50 via-white to-blue-50 rounded-xl shadow-md border border-indigo-100">
                        Нет чатов! Давайте поговорим!
                    </div>
                </div>
            </div>

            <!-- Message input area fixed at bottom -->
            <div v-if="currentChatId" class="p-4 border bg-white rounded-2xl border-indigo-100 shadow-md mt-2">
                <div class="flex items-center space-x-2">
                    <div class="flex space-x-1">
                        <button @click="isFileUploadVisible = true"
                                class="p-2 rounded-lg hover:bg-indigo-50 text-indigo-600 transition-colors duration-300">
                            <Upload size="20"/>
                        </button>
                        <FileUpload v-if="isFileUploadVisible" :on-close="closeFileUpload"
                                    :currentChatId="currentChatId" :onCollectionCreated="handleCollectionCreated"/>
                        <button @click="isModelSettingsVisible = true"
                                class="p-2 rounded-lg hover:bg-indigo-50 text-indigo-600 transition-colors duration-300">
                            <Settings size="20"/>
                        </button>
                        <ModelSettings
                            v-if="isModelSettingsVisible"
                            :on-close="closeModelSettings"
                            v-model:temperature="temperature"
                        />
                    </div>
                    <input
                        type="text"
                        v-model="inputMessage"
                        @keyup.enter="sendMessage"
                        placeholder="Введите ваше сообщение..."
                        class="flex-1 p-2 rounded-lg border border-indigo-200 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 outline-none transition-all duration-300"
                    />
                    <button
                        @click="sendMessage"
                        :disabled="isSending"
                        class="p-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg transition-all duration-300 transform hover:scale-[1.05] disabled:opacity-50 disabled:transform-none disabled:hover:scale-100"
                    >
                        <Send size="20"/>
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
// Import statements remain the same
import {onMounted, ref, watch, computed} from "vue";
import {useRouter, useRoute} from 'vue-router';
import ThemeToggle from "./ThemeToggle.vue";
import ModelSelector from "./ModelSelector.vue";
import ChatList from "./ChatList.vue";
import ModelSettings from "./ModelSettings.vue";
import FileUpload from "./FileUpload.vue";
import ChatMessage from "./ChatMessage.vue";
import {Upload, Send, Settings, Menu, MessageSquare, X} from "lucide-vue-next";
import axios from "axios";
import {useAuthStore} from "../stores/authStore.js";

export default {
    name: 'Chat',
    components: {
        Upload,
        Send,
        Settings,
        ThemeToggle,
        ChatList,
        ModelSelector,
        FileUpload,
        ChatMessage,
        ModelSettings,
        Menu,
        MessageSquare,
        X
    },
    setup() {
        const authStore = useAuthStore();
        const router = useRouter();
        const route = useRoute();
        const isFileUploadVisible = ref(false);
        const isModelSettingsVisible = ref(false);
        const inputMessage = ref("");
        const messages = ref([]);
        const currentModel = ref("");
        const chats = ref([]);
        const isMenuOpen = ref(false);
        const isSending = ref(false);
        const temperature = ref(localStorage.getItem('temperature') / 100 || 0.8);
        const theme = ref(localStorage.getItem('theme') || 'light');
        const chatCollectionParams = ref({
            local_collection: null,
            use_local_collection: false
        });

        // Reference to chat list components
        const desktopChatList = ref(null);
        const mobileChatList = ref(null);

        // Get chat ID from route params
        const currentChatId = computed(() => route.params.chatId || null);

        const setTheme = (newTheme) => {
            theme.value = newTheme;
            localStorage.setItem('theme', newTheme);
        };

        const handleTemperatureUpdate = (newTemperature) => {
            temperature.value = newTemperature;
        };

        const handleChatDeleted = (data) => {
            const {deletedChatId, updatedChats} = data;

            // Update the chats array without reloading the page
            if (Array.isArray(chats.value)) {
                // Direct array format
                chats.value = chats.value.filter(chat => chat.id !== deletedChatId);
            } else if (chats.value && chats.value.data) {
                // API response format
                chats.value.data = chats.value.data.filter(chat => chat.id !== deletedChatId);
            }
        };


        const closeModelSettings = () => {
            isModelSettingsVisible.value = false;
        };

        const closeFileUpload = () => {
            isFileUploadVisible.value = false;
        };

        const toggleMenu = () => {
            isMenuOpen.value = !isMenuOpen.value;
        };

        const exitHandler = async () => {
            await router.push('/');
        };

        const handleModelSelected = (model) => {
            currentModel.value = model;
        };

        const handleCollectionCreated = (params) => {
            chatCollectionParams.value = params;
            console.log('Collection params updated for this chat:', chatCollectionParams.value);
        };

        const navigateToChat = (chatId) => {
            router.push(`/chat/${chatId}`);
            if (isMenuOpen.value) {
                toggleMenu();
            }
        };

        onMounted(async () => {
            try {
                // Fetch all chats
                const response = await axios.get('/api/v1/chats');
                chats.value = response.data;

                // If we have a chatId in the URL, load its messages
                if (currentChatId.value) {
                    await loadMessages(currentChatId.value);
                }
            } catch (error) {
                console.error("Error loading chats:", error);
            }
        });

        watch(currentChatId, (newChatId) => {
            if (!newChatId) {
                messages.value = [];
                chatCollectionParams.value = {
                    local_collection: null,
                    use_local_collection: false
                };
            } else {
                loadMessages(newChatId);
                checkCollectionForCurrentChat();
            }
        });

        const checkCollectionForCurrentChat = () => {
            try {
                const COLLECTIONS_STORAGE_KEY = 'chat_collections';
                const storedCollections = localStorage.getItem(COLLECTIONS_STORAGE_KEY);

                if (storedCollections && currentChatId.value) {
                    const collections = JSON.parse(storedCollections);
                    const chatCollection = collections[currentChatId.value];

                    if (chatCollection) {
                        chatCollectionParams.value = chatCollection;
                        console.log('Collection params loaded for chat:', currentChatId.value, chatCollectionParams.value);
                    } else {
                        chatCollectionParams.value = {
                            local_collection: null,
                            use_local_collection: false
                        };
                    }
                }
            } catch (error) {
                console.error('Error checking collection for chat:', error);
            }
        };

        const sendMessage = async () => {
            if (!inputMessage.value.trim() || isSending.value) return;

            isSending.value = true;

            messages.value.push({role: "user", content: inputMessage.value});
            const userMessage = inputMessage.value;
            inputMessage.value = "";

            try {
                const response = await axios.post(`/api/v1/chats/${currentChatId.value}/messages`, {
                    messages: messages.value,
                    model: currentModel.value,
                    chatId: currentChatId.value,
                    temperature: temperature.value,
                    use_local_collection: chatCollectionParams.value.use_local_collection,
                    local_collection: chatCollectionParams.value.local_collection
                });

                const assistantMessage = response.data.message;
                messages.value.push({role: "assistant", content: assistantMessage});
            } catch (error) {
                console.error("Error sending message:", error);
                // Restore the message if there was an error
                inputMessage.value = userMessage;
                // Remove the last message from the array
                messages.value.pop();
            } finally {
                isSending.value = false;
            }
        };

        const loadMessages = async (chatId) => {
            try {
                const response = await axios.get(`/api/v1/chats/${chatId}/messages`);
                messages.value = response.data;
                checkCollectionForCurrentChat();
            } catch (error) {
                console.error("Error loading messages:", error);
            }
        };

        // Modified method to update UI in real-time after adding a new chat
        const addChatHandler = async () => {
            try {
                const response = await axios.post('/api/v1/createChat');
                const newChat = response.data;

                // Update the chats array in real-time
                if (Array.isArray(chats.value)) {
                    // Direct array format
                    chats.value.push(newChat);
                } else if (chats.value && chats.value.data) {
                    // API response format
                    chats.value.data.push(newChat);
                } else {
                    // Initialize if empty
                    chats.value = [newChat];
                }

                // Navigate to the new chat
                router.push(`/chat/${newChat.id}`);
                if (isMenuOpen.value) {
                    toggleMenu();
                }
            } catch (error) {
                console.error("Error creating chat:", error);
            }
        };

        return {
            isFileUploadVisible,
            closeFileUpload,
            isModelSettingsVisible,
            closeModelSettings,
            exitHandler,
            inputMessage,
            messages,
            sendMessage,
            handleModelSelected,
            addChatHandler,
            loadMessages,
            chats,
            currentChatId,
            chatCollectionParams,
            toggleMenu,
            isMenuOpen,
            handleTemperatureUpdate,
            handleCollectionCreated,
            navigateToChat,
            theme,
            setTheme,
            isSending,
            desktopChatList,
            mobileChatList,
            handleChatDeleted
        };
    },
    computed: {
        filteredMessages() {
            return this.messages.filter(msg => ['user', 'assistant'].includes(msg.role));
        }
    },
};
</script>

<style scoped>
/* Fixed height for main content containers */
.h-screen {
    height: calc(100vh - 2rem);
}

/* Smooth scrolling for better UX */
.overflow-y-auto {
    scroll-behavior: smooth;
}

/* Ensure message area doesn't push input field off screen */
.flex-1.overflow-y-auto {
    min-height: 0;
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
