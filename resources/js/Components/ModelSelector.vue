<template>
    <select
        class="w-48 p-2 rounded-lg border bg-secondary border-secondary text-primary"
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
import {ref, onMounted} from "vue";

export default {
    name: "ModelSelector",
    setup(props) {
        const models = ref([]);

        const fetchModels = async () => {
            try {
                let response = await axios.get('/chat/models')
                models.value = response.data.models
                console.log(response.data.message)
            } catch (e) {
                if (e.response && e.response.data) {
                    console.log(e.response.data)
                } else {
                    console.log(e.message)
                }
            }
        };

        onMounted(() => {
            models.value = fetchModels();
        });

        return {
            models,
        };
    },
};
</script>
