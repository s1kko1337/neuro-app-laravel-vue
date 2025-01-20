<template>
    <div class="space-y-2">
        <h2 class="text-sm font-semibold mb-4">
            Recent Chats
        </h2>
        <div v-for="chat in chats" :key="chat.id">
            <button @click="selectChat(chat.id)" class="flex items-center space-x-2 w-full px-3 py-2 rounded-lg hover:bg-primary hover:text-secondary ease-in-out transition-colors duration-300">
                <MessageSquare size="18" />
                <span class="truncate">{{ chat.id }}</span>
            </button>
        </div>
    </div>
</template>

<script>
import { MessageSquare } from "lucide-vue-next";

export default {
    name: "ChatList",
    components: { MessageSquare },
    props: {
        chats: {
            type: Array,
            required: true
        }
    },
    setup(props, { emit }) {
        const selectChat = (chatId) => {
            emit('select-chat', chatId);
        };

        return {
            selectChat
        };
    },
};
</script>
