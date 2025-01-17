<template>
    <div class="space-y-2">
        <h2 class="text-sm font-semibold mb-4">
            Recent Chats
        </h2>
        <div v-for="chat in chats" :key="chat.id">
            <button class="flex items-center space-x-2 w-full px-3 py-2 rounded-lg hover:bg-primary hover:text-secondary ease-in-out transition-colors duration-300">
                <MessageSquare size="18" />
                <span class="truncate">{{ chat.name }}</span>
            </button>
        </div>
    </div>
</template>

<script>
import {ref, computed} from "vue";
import {MessageSquare} from "lucide-vue-next";

export default {
    name: "ChatList",
    components: {MessageSquare},
    setup() {
        const chats = ref([
            {id: 1, name: "General Chat"},
            {id: 2, name: "Project Discussion"},
            {id: 3, name: "Quick Questions"},
        ]);

        const createChatHandler = () => {
            chats.value.push({name: 'New chat'});
        }

        return {
            chats,
            createChatHandler
        };
    },
};
</script>

<style scoped>

</style>
