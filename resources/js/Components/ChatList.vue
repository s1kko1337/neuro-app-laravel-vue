<template>
    <div class="flex-1">
        <h2 class="text-sm font-semibold mb-4 text-gray-900">
            Recent Chats
        </h2>
        <div class="space-y-2 h-full overflow-y-auto">
            <div v-for="chat in chats" :key="chat.id">
                <button @click="selectChat(chat.id)"
                        :class="{'border border-accent': activeChatId === chat.id}"
                        class="flex items-center space-x-2 w-full px-3 py-2 rounded-lg hover:bg-accent">
                    <MessageSquare size="18"/>
                    <span class="truncate">{{ chat.id }}</span>
                </button>
            </div>
        </div>
    </div>
</template>

<script>
import { MessageSquare } from "lucide-vue-next";
import {ref} from "vue";

export default {
    name: "ChatList",
    components: { MessageSquare },
    props: {
        chats: {
            type: Array,
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
    emits: ['select-chat', 'close-menu'],
    setup(props, { emit }) {
        const activeChatId = ref(null);
        const selectChat = (chatId) => {
            activeChatId.value = chatId;
            emit('select-chat', chatId);
            emit('close-menu');
        };

        return {
            selectChat,
            activeChatId
        };
    },
};
</script>
