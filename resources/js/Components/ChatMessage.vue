<template>
    <div :class="['flex mb-4', isAi ? 'justify-start' : 'justify-end']">
        <div
            :class="['max-w-[70%] rounded-lg p-4 border border-accent',
                isAi ? 'bg-primary text-gray-900' : 'bg-primary text-gray-900']">
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
                    return `<pre class="hljs rounded-md bg-red p-4 my-2 overflow-x-auto"><code>${highlighted}</code></pre>`;
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
}

.message-content pre {
  background: rgba(0,0,0,0.1) !important;
  transition: background-color 0.3s;
}

.dark .message-content pre {
  background: rgba(255,255,255,0.1) !important;
}

.message-content code {
  font-family: 'Fira Code', monospace;
  font-size: 0.9em;
}

.think-block {
  padding: 0.5rem;
  border-left: 2px solid #ddd;
  margin: 0.5rem 0;
  color: #6b7280;
  font-style: italic;
}
</style>