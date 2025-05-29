<template>
    <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="w-96 rounded-2xl shadow-lg bg-white p-6 border border-indigo-100">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-semibold text-gray-800">Настройки модели</h2>
                <button
                    @click="onClose"
                    class="p-2 rounded-lg hover:bg-indigo-50 text-indigo-600 transition-colors duration-300"
                >
                    <X size="20"/>
                </button>
            </div>
            <div class="space-y-6">
                <div class="bg-gradient-to-br from-indigo-50 via-white to-blue-50 p-4 rounded-xl border border-indigo-100 shadow-sm">
                    <label class="block mb-3 text-gray-800 font-medium">Температура</label>
                    <div class="flex items-center space-x-4">
                        <input
                            type="range"
                            min="0"
                            max="100"
                            v-model="temperature"
                            @input="updateTemperature"
                            class="flex-1 accent-indigo-600"
                        />
                        <input
                            type="text"
                            v-model="temperature"
                            @input="handleManualInput"
                            class="w-20 p-2 border border-indigo-200 rounded-lg text-center bg-white text-gray-800 focus:border-indigo-400 focus:ring-2 focus:ring-indigo-200 outline-none transition-all duration-300"
                        />
                    </div>
                </div>
                <div class="flex justify-end mt-4">
                    <button
                        @click="onClose"
                        class="px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg"
                    >
                        Применить
                    </button>
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
/* Custom scrollbar styling */
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

/* Range input styling */
input[type="range"] {
    -webkit-appearance: none;
    height: 6px;
    background: linear-gradient(to right, #6366f1, #a855f7);
    border-radius: 3px;
}

input[type="range"]::-webkit-slider-thumb {
    -webkit-appearance: none;
    width: 18px;
    height: 18px;
    background: white;
    border: 2px solid #6366f1;
    border-radius: 50%;
    cursor: pointer;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    transition: all 0.2s ease;
}

input[type="range"]::-webkit-slider-thumb:hover {
    transform: scale(1.1);
    box-shadow: 0 3px 6px rgba(0, 0, 0, 0.15);
}
</style>
