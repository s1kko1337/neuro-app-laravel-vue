<template>
    <select
        class="w-48 p-2 rounded-lg border bg-secondary border-secondary text-primary"
    >
        <option
            v-for="model in models"
            :key="model.value"
            :value="model.value"
        >
            {{ model.label }}
        </option>
    </select>
</template>

<script>
import {ref, onMounted} from "vue";

export default {
    name: "ModelSelector",
    setup(props) {
        const fetchModels = () => {
            return [
                {value: "gpt4", label: "GPT-4"},
                {value: "gpt3.5", label: "GPT-3.5"},
                {value: "claude", label: "Claude"},
            ];
        };

        const models = ref([]);
        onMounted(() => {
            models.value = fetchModels();
        });

        return {
            models,
        };
    },
};
</script>
