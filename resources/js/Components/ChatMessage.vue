<template>
    <div :class="['flex mb-4', isAi ? 'justify-start' : 'justify-end']">
        <div
            :class="['max-w-[70%] rounded-lg p-4',
                isAi ? 'bg-accent text-primary' : 'bg-secondary text-primary']">
            <p class="text-primary">
                {{ message }}
            </p>
        </div>
    </div>
</template>
<script>
export default {
    name: "ChatMessage",
    props: {
        message: {
            type: String,
            required: true,
        },
        isAi: {
            type: Boolean,
            required: true,
        },
    },
    setup(props) {
        return {
            ...props,
        };
    },
};
</script>
