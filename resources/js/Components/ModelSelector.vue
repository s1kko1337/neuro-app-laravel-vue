<template>
    <select
        class="w-48 p-2 rounded-lg border bg-secondary border-secondary text-primary"
        @change="selectModelHandler"
    >
        <option
            v-for="(model, modelIndex) in models"
            :key="modelIndex"
            :value="model.name"
        >
            {{ model.name }}
        </option>
    </select>
</template>

<script>
import { ref, onMounted } from "vue";
import axios from "axios";

export default {
    name: "ModelSelector",
    setup(props, { emit }) {
        const models = ref([]);
        const currentModel = ref("");

        onMounted(async () => {
            await fetchModels();
            if (models.value.length > 0) {
                currentModel.value = models.value[0].name;
                emit("model-selected", currentModel.value);
            }
        });

        const fetchModels = async () => {
            try {
                let response = await axios.get('/api/models');
                models.value = response.data.models;
            } catch (e) {
                console.error("Error fetching models:", e);
            }
        };

        const selectModelHandler = async (event) => {
            currentModel.value = event.target.value;
            emit("model-selected", currentModel.value);
        };

        return {
            models,
            selectModelHandler,
        };
    },
};
</script>
