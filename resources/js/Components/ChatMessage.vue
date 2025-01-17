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



local.INFO: Ollama response: {"model":"qwen2.5:1.5b","created_at":"2025-01-17T12:19:06.038013198Z","message":{"role":"assistant","content":"Вот десять сгенерированных вопросов для нейросети:

1. Как работает процесс обучения искусственного интеллекта?
2. Объясните принципы нахождения ближайшего к центру в сети.
3. Что такое глубокое обучение и как оно помогает улучшить AI-системы?
4. Расскажите о важных этапах обучения нейронных сетей.
5. Как искусственный интеллект \"воображает\" и создавал бы мир, если он мог это делать?
6. Соберите примеры классификации объектов на основе их атрибутов.
7. Упростите понятие \"оптимизации функции потерь\".
8. Поясните основные принципы строительства рекуррентных нейронных сетей.
9. Объясните, почему важна стабильность в обучении нейросетей.
10. Расскажите об особенностях и преимуществах архитектурных решений для обработки больших данных.

Эти вопросы направлены на глубокое понимание процессов обучения и работы искусственного интеллекта, используя разные аспекты нейронных сетей."},"done_reason":"stop","done":true,"total_duration":13941991164,"load_duration":16733027,"prompt_eval_count":45,"prompt_eval_duration":241000000,"eval_count":291,"eval_duration":13674000000}
