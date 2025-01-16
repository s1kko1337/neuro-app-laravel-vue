<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="w-96 rounded-lg shadow-lg bg-secondary p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-primary">Model Settings</h2>
                <button
                    @click="onClose"
                    class="p-1 rounded-lg text-secondary bg-primary hover:bg-accent hover:text-primary"
                >
                    <X size="20" />
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block mb-2 text-primary">Temperature</label>
                    <input type="range" min="0" max="100" class="w-full" />
                </div>
                <div>
                    <label class="block mb-2 text-primary">Max Length</label>
                    <input
                        type="number"
                        class="w-full p-2 rounded-lg border bg-secondary border-primary focus:border-accent outline-none"
                    />
                </div>
                <div>
                    <label class="block mb-2 text-primary">Available Tools</label>
                    <div class="space-y-2">
                        <label
                            v-for="tool in tools"
                            :key="tool"
                            class="flex items-center space-x-2"
                        >
                            <input type="checkbox" class="rounded" />
                            <span class="text-primary">{{ tool }}</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent, ref } from "vue";
import { X } from "lucide-vue-next";

export default defineComponent({
    name: "ModelSettings",
    props: {
        onClose: {
            type: Function,
            required: true,
        },
    },
    setup(props) {
        const tools = ref([
            "Web Search",
            "Calculator",
            "Code Interpreter",
            "Image Analysis",
        ]);

        const onClose = () => {
            props.onClose();
        };

        return {
            tools,
            onClose,
        };
    },
    components: {
        X,
    },
});
</script>
