<template>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full min-h-screen bg-primary">
        <div class="flex w-full h-screen">
            <div class="w-64 p-4 border-r bg-secondary border-primary">
            <div class="flex items-center justify-between mb-8">
                <button class="text-xl font-semibold color-primary"
                    @click="exitHandler">>
                Home
                </button>
                <ThemeToggle theme={theme} setTheme={setTheme} />
            </div>
            <div class="flex flex-col h-[calc(100%-5rem)] justify-between overflow-y-auto px-2">
                <ChatList :chats="chats" @select-chat="loadMessages" theme={theme} ref="ChatList" class="flex-1"/>
                <div class="my-auto sticky bottom-0">
                    <button class="mt-4 flex items-center justify-center space-x-2 w-full px-4 py-2 rounded-lg text-secondary bg-primary hover:bg-blue-600 hover:text-primary ease-in-out transition-colors"
                            @click="addChatHandler">
                        <span>New Chat</span>
                    </button>
                </div>
            </div>
        </div>
        <div class="flex-1 flex flex-col" v-if="currentChatId">
            <div
                class="flex justify-between items-center px-4 py-2 border-b color-primary border-secondary"
            >
            <ModelSelector @model-selected="handleModelSelected" />
        </div>
        <div class="flex-1 overflow-y-auto p-4">
            <ChatMessage
                v-for="(msg, index) in messages"
                :key="index"
                :message="msg.content"
                :isAi="msg.role === 'assistant'"
            />
        </div>
        <div
            class="p-4 border-t bg-secondary border-primary"
        >
        <div class="flex items-center space-x-2">
            <button @click="isFileUploadVisible = true" class="p-2 rounded-lg hover:bg-primary hover:text-secondary">
                <Upload size="20"
                />
            </button>
            <FileUpload v-if="isFileUploadVisible" :on-close="closeFileUpload" />

            <div>
                <button @click="isModelSettingsVisible = true" class="p-2 rounded-lg hover:bg-primary hover:text-secondary">
                    <Settings size="20"
                />
                </button>
                <ModelSettings v-if="isModelSettingsVisible" :on-close="closeModelSettings" />
            </div>
            <input
                type="text"
                v-model="inputMessage"
                @keyup.enter="sendMessage"
                placeholder="Type your message..."
                class="flex-1 p-2 rounded-lg border  bg-primary border-secondary focus:border-blue-500 outline-none"
            />
            <button @click="sendMessage" class="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
              <Send size="20"/>
            </button>
        </div>
    </div>
    </div>
    </div>
    </div>
    </div>
</template>
<script>
import {onMounted, ref, watch} from "vue";
import { useRouter, useRoute } from 'vue-router';
import ThemeToggle from "./ThemeToggle.vue";
import ModelSelector from "./ModelSelector.vue";
import ChatList from "./ChatList.vue";
import ModelSettings from "./ModelSettings.vue";
import FileUpload from "./FileUpload.vue";
import ChatMessage from "./ChatMessage.vue";
import {Upload, Send, Settings} from "lucide-vue-next";
import axios from "axios";

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
    },
    setup() {
        const router = useRouter();
        const route = useRoute();
        const isFileUploadVisible = ref(false);
        const isModelSettingsVisible = ref(false);
        const inputMessage = ref("");
        const messages = ref([]);
        const currentModel = ref("");
        const currentChatId = ref(route.params.chatId || null);
        const chats = ref([]);

        const closeModelSettings = () => {
            isModelSettingsVisible.value = false;
        };
        const closeFileUpload = () => {
            isFileUploadVisible.value = false;
        };
        const exitHandler = async () => {
            await router.push('/');
        };

        const handleModelSelected = (model) => {
            currentModel.value = model;
        };

        onMounted(async () => {
            try {
                const response = await axios.get('/api/chats');
                chats.value = response.data;
            } catch (error) {
                console.error("Error loading chats:", error);
            }
        });

        watch(
            () => route.params.chatId,
            (newChatId) => {
                currentChatId.value = newChatId || null;
                if (newChatId) {
                    loadMessages(newChatId);
                } else {
                    messages.value = [];
                }
            },
            { immediate: true }
        );

        const isSending = ref(false);

        const sendMessage = async () => {
            if (!inputMessage.value.trim() || isSending.value) return;

            isSending.value = true;

            // Добавляем сообщение пользователя
            messages.value.push({ role: "user", content: inputMessage.value });
            inputMessage.value = "";

            try {
                const response = await axios.post('/api/chat', {
                    messages: messages.value,
                    model: currentModel.value,
                    chatId: currentChatId.value
                });

                const assistantMessage = response.data.message;
                messages.value.push({ role: "assistant", content: assistantMessage });
            } catch (error) {
                console.error("Error sending message:", error);
            } finally {
                isSending.value = false;
            }
        };

        const loadMessages = async (chatId) => {
            try {
                const response = await axios.get(`/api/chats/${chatId}/messages`);
                messages.value = response.data;
                currentChatId.value = chatId;
            } catch (error) {
                console.error("Error loading messages:", error);
            }
        };

        const addChatHandler = async () => {
            try {
                const response = await axios.post('/api/createChat');
                chats.value.push(response.data);
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
            currentChatId
        };
    },
};
</script>
