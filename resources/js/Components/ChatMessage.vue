<template>
    <div :class="['flex mb-4', isAi ? 'justify-start' : 'justify-end']">
        <div
            :class="[
                'max-w-[70%] rounded-lg p-4 border shadow-md transition-all duration-300 transform hover:shadow-lg',
                isAi
                    ? 'bg-gradient-to-br from-indigo-50 via-white to-blue-50 border-indigo-100 text-gray-800'
                    : 'bg-gradient-to-r from-indigo-600 to-purple-600 border-transparent text-white'
            ]"
        >
            <div class="message-content" v-html="processedMessage"></div>
        </div>
    </div>
</template>

<script>
import hljs from 'highlight.js';
import { computed } from 'vue';

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
        const processedMessage = computed(() => {
            // Обработка блока <think>
            if (props.message.startsWith('<think>') && props.message.endsWith('</think>')) {
                const content = props.message
                    .replace(/^<think>/, '')
                    .replace(/<\/think>$/, '')
                    .trim();

                // Форматирование текста внутри блока <think>
                const formattedContent = content
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>')
                    .replace(/```([\s\S]*?)```/g, (_, code) => {
                        const highlighted = hljs.highlightAuto(code).value;
                        return `<pre class="hljs rounded-md p-4 my-2 overflow-x-auto"><code>${highlighted}</code></pre>`;
                    })
                    .replace(/\n/g, '<br>');

                return `<div class="think-block italic text-gray-500">${formattedContent}</div>`;
            }

            // Обработка обычного текста и кода
            const parts = props.message.split(/(```[\s\S]*?```)/g);

            return parts.map((part, index) => {
                if (index % 2 === 1) { // Код в дипсике начинается с тройных комментов
                    const codeContent = part
                        .replace(/^```(\w+)?\n?/, '')
                        .replace(/\n?```$/, '');

                    const highlighted = hljs.highlightAuto(codeContent).value;
                    return `<pre class="hljs rounded-md p-4 my-2 overflow-x-auto shadow-sm"><code>${highlighted}</code></pre>`;
                }

                return part
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/\n/g, '<br>')
                    .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
                    .replace(/\*(.*?)\*/g, '<em>$1</em>');
            }).join('');
        });

        return {
            processedMessage,
            isAi: props.isAi
        };
    },
};
</script>

<style>
@import 'highlight.js/styles/github.css';

.message-content {
    line-height: 1.6;
    word-break: break-word;
}

.message-content pre {
    background: rgba(0,0,0,0.05) !important;
    transition: all 0.3s ease;
    border-radius: 0.5rem;
    margin: 0.75rem 0;
    border: 1px solid rgba(99, 102, 241, 0.2);
}

.dark .message-content pre {
    background: rgba(255,255,255,0.1) !important;
    border-color: rgba(255,255,255,0.2);
}

.message-content code {
    font-family: 'Fira Code', monospace;
    font-size: 0.9em;
}

.think-block {
    padding: 0.75rem;
    border-left: 3px solid rgba(99, 102, 241, 0.5);
    margin: 0.75rem 0;
    color: #6b7280;
    font-style: italic;
    background-color: rgba(99, 102, 241, 0.05);
    border-radius: 0.25rem;
    transition: all 0.3s ease;
}

/* Custom scrollbar styling for code blocks */
.message-content pre::-webkit-scrollbar {
    width: 6px;
    height: 6px;
}

.message-content pre::-webkit-scrollbar-track {
    background: transparent;
}

.message-content pre::-webkit-scrollbar-thumb {
    background-color: rgba(99, 102, 241, 0.3);
    border-radius: 3px;
}

.message-content pre::-webkit-scrollbar-thumb:hover {
    background-color: rgba(99, 102, 241, 0.5);
}
</style>
