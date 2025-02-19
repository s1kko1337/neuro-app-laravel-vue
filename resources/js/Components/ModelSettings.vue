<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="w-96 rounded-lg shadow-lg bg-primary p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-900">Model Settings</h2>
                <button
                    @click="onClose"
                    class="p-1 rounded-lg text-secondary bg-primary hover:bg-accent hover:text-gray-900"
                >
                    <X size="20" class="text-gray-900"/>
                </button>
            </div>
            <div class="space-y-4">
                <div>
                    <label class="block mb-2 text-gray-900">Temperature</label>
                    <div class="flex items-center space-x-4">
                        <input 
                            type="range" 
                            min="0" 
                            max="100" 
                            v-model="temperature" 
                            @input="updateTemperature"
                            class="flex-1"
                        />
                        <input
                            type="text"
                            v-model="temperature"
                            @input="handleManualInput"
                            class="w-20 p-2 border rounded-lg text-center bg-secondary text-gray-900"
                        />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import { defineComponent, ref, watch } from "vue";
import { X } from "lucide-vue-next";

export default defineComponent({
    name: "ModelSettings",
    props: {
        onClose: {
            type: Function,
            required: true,
        },
    },
    setup(props, { emit }) {
        const temperature = ref(localStorage.getItem('temperature') || 80);

        const updateTemperature = () => {
            localStorage.setItem('temperature', temperature.value);
            emit('update:temperature', temperature.value); 
        };

        const handleManualInput = (event) => {
            let value = parseInt(event.target.value, 10);

            if (isNaN(value)) {
                value = 0;
            } else if (value < 0) {
                value = 0;
            } else if (value > 100) {
                value = 100;
            }

            temperature.value = value;
            updateTemperature();
        };

        const onClose = () => {
            props.onClose();
        };

        return {
            temperature,
            updateTemperature,
            handleManualInput,
            onClose,
        };
    },
    components: {
        X,
    },
});
</script>

<style scoped>
input[type="text"] {
    border: 1px solid #ccc;
    outline: none;
}

input[type="text"]:focus {
    border-color: #3b82f6; 
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2); 
}
</style>