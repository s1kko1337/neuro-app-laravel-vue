<template>
    <div class="chat-list-container">

        <div class="chat-list-items overflow-y-auto">
            <div v-for="chat in localChats" :key="chat.id" class="relative group mb-2">
                <div class="flex items-center justify-between">
                    <router-link :to="`/chat/${chat.id}`"
                                 :class="{'border border-indigo-300 bg-gradient-to-r from-indigo-50 to-blue-50': activeChatId == chat.id}"
                                 class="flex items-center space-x-2 w-full px-3 py-2 rounded-lg hover:bg-gradient-to-r from-indigo-50 to-blue-50 transition-all duration-300 mb-1 shadow-sm hover:shadow-md transform hover:scale-[1.01]">
                        <MessageSquare size="18" class="text-indigo-600"/>
                        <span class="truncate font-medium text-gray-800">Чат {{ chat.id }}</span>
                    </router-link>
                    <button
                        @click.prevent="confirmDeleteChat(chat.id)"
                        class="absolute right-2 opacity-0 group-hover:opacity-100 transition-opacity p-1 rounded-full hover:bg-red-100 transform transition-all duration-300 hover:scale-110"
                        title="Delete chat"
                    >
                        <Trash2 size="16" class="text-red-500" />
                    </button>
                </div>
            </div>
            <div v-if="localChats.length === 0" class="text-center py-4 text-gray-500 bg-gradient-to-br from-indigo-50 via-white to-blue-50 rounded-xl p-6 shadow-md">
                Нет чатов
            </div>
        </div>

        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-2xl p-6 max-w-sm mx-auto shadow-xl transform transition-all duration-300">
                <div class="h-2 bg-gradient-to-r from-indigo-400 to-purple-600 rounded-t-lg mb-4"></div>
                <h3 class="text-lg font-medium mb-4 text-gray-800">Удаление чата</h3>
                <p class="mb-4 text-gray-700">Вы уверены что хотите удалить чат? Это действие невозможно отменить.</p>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 border border-indigo-200 rounded-lg hover:bg-gradient-to-r from-indigo-50 to-blue-50 transition-all duration-300 text-gray-700"
                    >
                        Назад
                    </button>
                    <button
                        @click="deleteChat"
                        class="px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 text-white rounded-lg hover:from-red-600 hover:to-red-700 transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg"
                    >
                        Удалить
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { MessageSquare, Trash2 } from "lucide-vue-next";
import { ref, computed, watchEffect } from "vue";
import { useRouter } from "vue-router";
import axios from "axios";

export default {
    name: "ChatList",
    components: { MessageSquare, Trash2 },
    props: {
        chats: {
            type: [Array, Object],
            required: true
        },
        activeChatId: {
            type: [String, Number],
            default: null
        },
        theme: {
            type: String,
            required: true
        }
    },
    emits: ['close-menu', 'chat-deleted'],
    setup(props, { emit }) {
        const router = useRouter();
        const activeChatId = ref(props.activeChatId || null);
        const showDeleteModal = ref(false);
        const chatToDelete = ref(null);
        const COLLECTIONS_STORAGE_KEY = 'chat_collections';

        const localChats = ref([]);

        const processedChats = computed(() => {
            let chats = [];

            if (Array.isArray(props.chats)) {
                chats = props.chats;
            } else if (props.chats && props.chats.data && Array.isArray(props.chats.data)) {
                chats = props.chats.data;
            }

            return chats.filter(chat => !chat.is_system);
        });

        watchEffect(() => {
            localChats.value = processedChats.value;
        });

        watchEffect(() => {
            activeChatId.value = props.activeChatId;
        });

        const selectChat = (chatId) => {
            activeChatId.value = chatId;
            router.push(`/chat/${chatId}`);
            emit('close-menu');
        };

        const confirmDeleteChat = (chatId) => {
            chatToDelete.value = chatId;
            showDeleteModal.value = true;
        };

        const removeCollectionFromStorage = (chatId) => {
            try {
                const storedCollections = localStorage.getItem(COLLECTIONS_STORAGE_KEY);
                if (storedCollections) {
                    const collections = JSON.parse(storedCollections);
                    if (collections[chatId]) {
                        delete collections[chatId];
                        localStorage.setItem(COLLECTIONS_STORAGE_KEY, JSON.stringify(collections));
                        console.log(`Collection for chat ${chatId} removed from localStorage`);
                    }
                }
            } catch (error) {
                console.error('Error removing collection from localStorage:', error);
            }
        };

        const deleteChat = async () => {
            try {
                // Delete chat from the server
                await axios.delete(`/api/v1/chats/${chatToDelete.value}`);

                // Try to delete the collection from the server
                try {
                    await axios.delete(`/api/v1/collection/${chatToDelete.value}`);
                    console.log(`Collection for chat ${chatToDelete.value} deleted from server`);
                } catch (collectionError) {
                    console.log(`No collection found for chat ${chatToDelete.value} or error deleting:`, collectionError);
                }

                // Remove collection from localStorage
                removeCollectionFromStorage(chatToDelete.value);

                // Update local state
                localChats.value = localChats.value.filter(chat => chat.id !== chatToDelete.value);
                const updatedChats = processedChats.value.filter(chat => chat.id !== chatToDelete.value);

                // Emit event to parent component
                emit('chat-deleted', {
                    deletedChatId: chatToDelete.value,
                    updatedChats: updatedChats
                });

                // Navigate away if the deleted chat was active
                if (chatToDelete.value === activeChatId.value) {
                    await router.push('/chat');
                }

                // Reset modal state
                showDeleteModal.value = false;
                chatToDelete.value = null;
            } catch (error) {
                console.error("Error deleting chat:", error);
            }
        };

        return {
            selectChat,
            activeChatId,
            localChats,
            confirmDeleteChat,
            deleteChat,
            showDeleteModal,
            chatToDelete
        };
    },
};
</script>
<style scoped>
.chat-list-container {
    display: flex;
    flex-direction: column;
    height: 100%;
    width: 100%;
}

.chat-list-items {
    flex: 1;
    min-height: 0;
    padding: 0.5rem;
}

.router-link-active {
    font-weight: 600;
    background: linear-gradient(to right, rgba(99, 102, 241, 0.1), rgba(59, 130, 246, 0.1));
    border-left: 3px solid #6366f1;
}

.overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: rgba(99, 102, 241, 0.3) transparent;
    scroll-behavior: smooth;
}

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

.group:hover .opacity-0 {
    opacity: 1;
}

.group {
    transition: all 0.3s ease;
}

.group:hover {
    transform: translateX(2px);
}
</style>
