<template>
    <div class="flex-1 bg-neutral-300 relative h-full">
        <div class="">
            Это чат
        </div>
        <div class="absolute right-2 top-2">
            <button type="button"
                    @click="exitHandler">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="size-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 16.811c0 .864-.933 1.406-1.683.977l-7.108-4.061a1.125 1.125 0 0 1 0-1.954l7.108-4.061A1.125 1.125 0 0 1 21 8.689v8.122ZM11.25 16.811c0 .864-.933 1.406-1.683.977l-7.108-4.061a1.125 1.125 0 0 1 0-1.954l7.108-4.061a1.125 1.125 0 0 1 1.683.977v8.122Z" />
                </svg>
            </button>
        </div>
    </div>
</template>

<script>
import { useRouter } from 'vue-router';

export default {
    name: 'Chat',
    setup() {
        const router = useRouter();

        const exitHandler = async () => {
            await router.push('/main');
        };

        return {
            exitHandler
        };
    }
}
</script>
