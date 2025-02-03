<template>
    <div :class="['flex mb-4', isAi ? 'justify-start' : 'justify-end']">
        <div
            :class="['max-w-[70%] rounded-lg p-4 border border-accent',
                isAi ? 'bg-accent text-primary' : 'bg-secondary text-gray-900']">
            <p class="text-gray-900">
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
        }
    },
    setup(props) {
        return {
            ...props,
        };
    },
};
</script>
