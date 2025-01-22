<template>
    <h1
        class="text-3xl md:text-4xl font-bold text-gray-900"
    >
        {{ displayText }}
        <span class="animate-pulse">|</span>
    </h1>
</template>

<script>
import { ref, onMounted, watchEffect } from "vue";

export default {
    name: "TypewriterText",
    props: {
        text: {
            type: String,
            required: true,
        },
        speed: {
            type: Number,
            default: 50,
        },
    },
    setup(props) {
        const displayText = ref("");
        const currentIndex = ref(0);

        const typeText = () => {
            if (currentIndex.value < props.text.length) {
                setTimeout(() => {
                    displayText.value += props.text[currentIndex.value];
                    currentIndex.value++;
                    typeText();
                }, props.speed);
            }
        };

        onMounted(() => {
            typeText();
        });

        return {
            displayText,
        };
    },
};
</script>
