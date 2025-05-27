<template>
    <div class="max-w-md mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Вход в систему</h2>

        <form class="space-y-4" @submit.prevent="handleLogin">
            <div>
                <label class="block text-gray-700 mb-2" for="email">Email</label>
                <input
                    type="email"
                    id="email"
                    v-model="form.email"
                    placeholder="Ваш email"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    :class="{'border-red-500': errors.email}"
                    :disabled="authStore.loading"
                >
                <p v-if="errors.email" class="mt-1 text-sm text-red-500">{{ errors.email }}</p>
            </div>

            <div>
                <label class="block text-gray-700 mb-2" for="password">Пароль</label>
                <input
                    type="password"
                    id="password"
                    v-model="form.password"
                    placeholder="Ваш пароль"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
                    :class="{'border-red-500': errors.password}"
                    :disabled="authStore.loading"
                >
                <p v-if="errors.password" class="mt-1 text-sm text-red-500">{{ errors.password }}</p>
            </div>

            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <input type="checkbox" id="remember" v-model="form.remember"
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500" :disabled="authStore.loading">
                    <label for="remember" class="ml-2 block text-gray-700">Запомнить меня</label>
                </div>

                <div>
                    <router-link :to="{name:'passwordRequest'}" class="text-sm text-indigo-600 hover:underline">Забыли
                        пароль?
                    </router-link>
                </div>
            </div>

            <button
                type="submit"
                class="w-full bg-indigo-600 text-white py-2 px-4 rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 transition duration-200"
                :disabled="authStore.loading"
            >
                <span v-if="authStore.loading">
                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline-block"
                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Вход...
                </span>
                <span v-else>Войти</span>
            </button>

            <div v-if="authStore.error"
                 class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
                {{ authStore.error }}
            </div>
        </form>

        <div class="mt-6 text-center">
            <p class="text-gray-600">
                Нет аккаунта?
                <router-link :to="{name:'register'}" class="text-indigo-600 hover:underline">Зарегистрироваться
                </router-link>
            </p>
        </div>
    </div>
</template>

<script setup>
import {ref, reactive} from 'vue';
import {useRouter} from 'vue-router';
import {useAuthStore} from '../../stores/authStore.js';

const router = useRouter();
const authStore = useAuthStore();

// Форма и валидация
const form = reactive({
    email: '',
    password: '',
    remember: false
});

const errors = reactive({
    email: '',
    password: ''
});

// Проверка формы
const validateForm = () => {
    let isValid = true;

    // Сброс ошибок
    errors.email = '';
    errors.password = '';

    // Проверка email
    if (!form.email) {
        errors.email = 'Email обязателен';
        isValid = false;
    } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
        errors.email = 'Некорректный формат email';
        isValid = false;
    }

    // Проверка пароля
    if (!form.password) {
        errors.password = 'Пароль обязателен';
        isValid = false;
    } else if (form.password.length < 6) {
        errors.password = 'Пароль должен быть не менее 6 символов';
        isValid = false;
    }

    return isValid;
};

// Обработка входа
const handleLogin = async () => {
    if (!validateForm()) return;

    try {
        const success = await authStore.login({
            email: form.email,
            password: form.password,
            remember: form.remember
        });

        if (success) {
            await router.push({name: 'chat'});
        }
    } catch (error) {
        console.error('Login component error:', error);
    }
};
</script>
