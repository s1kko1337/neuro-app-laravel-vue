<template>
    <div class="chat-list-container">
        <h2 class="text-sm font-semibold mb-4 text-gray-900">
            Recent Chats
        </h2>
        <div class="chat-list-items overflow-y-auto">
            <div v-for="chat in localChats" :key="chat.id" class="relative group">
                <div class="flex items-center justify-between">
                    <router-link :to="`/chat/${chat.id}`"
                                 :class="{'border border-accent bg-accent bg-opacity-10': activeChatId == chat.id}"
                                 class="flex items-center space-x-2 w-full px-3 py-2 rounded-lg hover:bg-accent hover:bg-opacity-20 transition-all mb-1">
                        <MessageSquare size="18"/>
                        <span class="truncate">Chat {{ chat.id }}</span>
                    </router-link>
                    <button
                        @click.prevent="confirmDeleteChat(chat.id)"
                        class="absolute right-2 opacity-0 group-hover:opacity-100 transition-opacity p-1 rounded-full hover:bg-red-100"
                        title="Delete chat"
                    >
                        <Trash2 size="16" class="text-red-500" />
                    </button>
                </div>
            </div>
            <div v-if="localChats.length === 0" class="text-center py-4 text-gray-500">
                No chats yet
            </div>
        </div>

        <div v-if="showDeleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
            <div class="bg-white rounded-lg p-6 max-w-sm mx-auto">
                <h3 class="text-lg font-medium mb-4">Delete Chat</h3>
                <p class="mb-4">Are you sure you want to delete this chat? This action cannot be undone.</p>
                <div class="flex justify-end space-x-3">
                    <button
                        @click="showDeleteModal = false"
                        class="px-4 py-2 border rounded-lg hover:bg-gray-100"
                    >
                        Cancel
                    </button>
                    <button
                        @click="deleteChat"
                        class="px-4 py-2 bg-red-500 text-white rounded-lg hover:bg-red-600"
                    >
                        Delete
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

        const localChats = ref([]);

        const processedChats = computed(() => {
            if (Array.isArray(props.chats)) {
                return props.chats; // Already an array
            } else if (props.chats && props.chats.data && Array.isArray(props.chats.data)) {
                return props.chats.data;
            }
            return [];
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

        const deleteChat = async () => {
            try {
                await axios.delete(`/api/v1/chats/${chatToDelete.value}`);

                localChats.value = localChats.value.filter(chat => chat.id !== chatToDelete.value);

                const updatedChats = processedChats.value.filter(chat => chat.id !== chatToDelete.value);

                emit('chat-deleted', {
                    deletedChatId: chatToDelete.value,
                    updatedChats: updatedChats
                });

                if (chatToDelete.value === activeChatId.value) {
                    await router.push('/chat');
                }

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
}

.router-link-active {
    font-weight: 600;
}

.overflow-y-auto {
    scrollbar-width: thin;
    scrollbar-color: rgba(156, 163, 175, 0.5) transparent;
}

.overflow-y-auto::-webkit-scrollbar {
    width: 6px;
}

.overflow-y-auto::-webkit-scrollbar-track {
    background: transparent;
}

.overflow-y-auto::-webkit-scrollbar-thumb {
    background-color: rgba(156, 163, 175, 0.5);
    border-radius: 3px;
}

.group:hover .opacity-0 {
    opacity: 1;
}
</style>
