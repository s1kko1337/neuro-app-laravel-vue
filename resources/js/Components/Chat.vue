<template>
    <div class="flex flex-col items-center justify-center">
        <div class="w-full min-h-screen bg-primary">
            <div class="flex w-full h-screen">
                <!--Desktop menu -->
                <div class="hidden lg:block w-64 border rounded-3xl m-4 p-4 bg-secondary border-accent">
                    <div class="flex items-center justify-between mb-8">
                        <button class="text-xl font-semibold color-primary hover:text-accent"
                                @click="exitHandler">
                            Home
                        </button>
                        <ThemeToggle theme={theme} setTheme={setTheme} />
                    </div>
                    <div class="flex flex-col min-h-sc  justify-between overflow-y-auto px-2">
                        <ChatList :chats="chats" @select-chat="loadMessages" theme={theme} ref="ChatList"
                                  class="flex-1"/>
                        <div class="my-auto">
                            <button
                                class="mt-4 flex items-center justify-center space-x-2 w-full px-4 py-2 rounded-lg text-gray-900 bg-accent hover:bg-blue-600"
                                @click="addChatHandler">
                                <span>New Chat</span>
                            </button>
                        </div>
                    </div>
                </div>
                <!--Mobile menu -->
                <div v-if="isMenuOpen" class="fixed inset-0 z-50 flex">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="toggleMenu"></div>
                    <div class="relative z-50 w-64 bg-secondary">
                        <div class="flex items-center justify-between p-4 border-b border-primary">
                            <span class="text-xl font-semibold color-primary">Menu</span>
                            <button @click="toggleMenu" class="p-2">
                                <span class="text-gray-900">✕</span>
                            </button>
                        </div>
                        <div class="flex flex-col h-full justify-between overflow-y-auto p-4">
                            <div class="flex flex-row justify-between mb-2">
                                <button class="text-xl font-semibold color-primary hover:bg-primary"
                                        @click="exitHandler">
                                    Home
                                </button>
                                <ThemeToggle theme={theme} setTheme={setTheme} />
                            </div>
                            <ChatList
                                :chats="chats"
                                :activeChatId="currentChatId"
                                @select-chat="loadMessages"
                                @close-menu="toggleMenu"
                                theme={theme}
                                ref="ChatList"
                                class="flex-1"
                            />
                            <div class="my-auto sticky bottom-0">
                                <button
                                    class="mt-4 flex items-center justify-center space-x-2 w-full px-4 py-2 rounded-lg text-gray-900 bg-accent hover:bg-blue-600 hover:text-primary"
                                    @click="addChatHandler">
                                    <span>New Chat</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex-1 flex flex-col border rounded-3xl m-4 p-4 border-accent" v-if="currentChatId">
                    <div
                        class="flex justify-between items-center px-4 py-2 color-primary "
                    >
                        <ModelSelector @model-selected="handleModelSelected"/>
                        <button class="lg:hidden p-2 rounded-lg" @click="toggleMenu">
                            <Menu size="20" />
                        </button>
                    </div>
                    <div class="flex-1 overflow-y-auto p-4">
                        <ChatMessage
                            v-for="(msg, index) in messages"
                            :key="index"
                            :message="msg.content"
                            :isAi="msg.role === 'assistant'"
                        />
                    </div>
                    <div class="p-4 border bg-secondary rounded-3xl border-accent" v-if="currentChatId" >
                        <div class="flex items-center space-x-2">
                            <div>
                            <button @click="isFileUploadVisible = true"
                                    class="p-2 rounded-lg hover:bg-primary hover:text-accent">
                                <Upload size="20"
                                />
                            </button>
                            <FileUpload v-if="isFileUploadVisible" :on-close="closeFileUpload"/>
                                <button @click="isModelSettingsVisible = true"
                                        class="p-2 rounded-lg hover:bg-primary hover:text-accent">
                                    <Settings size="20"
                                    />
                                </button>
                                <ModelSettings v-if="isModelSettingsVisible" :on-close="closeModelSettings"/>
                            </div>
                            <input
                                type="text"
                                v-model="inputMessage"
                                @keyup.enter="sendMessage"
                                placeholder="Type your message..."
                                class="flex-1 p-2 rounded-lg border  bg-primary border-secondary"
                            />
                            <button @click="sendMessage"
                                    class="p-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
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
import {Upload, Send, Settings, Menu} from "lucide-vue-next";
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
        Menu
    },
    setup() {
        const router = useRouter();
        const route = useRoute();
        const isFileUploadVisible = ref(false);
        const isModelSettingsVisible = ref(false);
        const inputMessage = ref("");
        const messages = ref([]);
        const currentModel = ref("");
        const currentChatId = ref(localStorage.getItem('selectedChatId') || null);
        const chats = ref([]);
        const isMenuOpen = ref(false);
        const isSending = ref(false);

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

        onMounted(async () => {
            try {
                const response = await axios.get('/api/chats');
                chats.value = response.data;

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
            }
        });


        const sendMessage = async () => {
            if (!inputMessage.value.trim() || isSending.value) return;

            isSending.value = true;

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
                localStorage.setItem('selectedChatId', chatId);
            } catch (error) {
                console.error("Error loading messages:", error);
            }
        };

        const addChatHandler = async () => {
            try {
                const response = await axios.post('/api/createChat');
                chats.value.push(response.data);
                const newChat = response.data;
                currentChatId.value = newChat.id;
                localStorage.setItem('selectedChatId', newChat.id);
                await loadMessages(newChat.id);
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
            toggleMenu,
            isMenuOpen
        };
    },
};
</script>
