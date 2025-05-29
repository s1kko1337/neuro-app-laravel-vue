
<template>
    <div class="bg-gradient-to-br from-indigo-50 via-white to-blue-50 min-h-screen py-12 w-full">
        <!-- Хедер с анимированным фоном -->
        <header class="relative py-12 mb-10 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 opacity-90"></div>
            <div class="absolute inset-0">
                <div class="wave-pattern"></div>
            </div>
            <div class="container mx-auto px-6 relative z-10">
                <h1 class="text-3xl md:text-4xl font-bold text-white text-center mb-3">
                    Изменение пароля
                </h1>
                <p class="text-lg text-indigo-100 text-center max-w-3xl mx-auto">
                    Обновите ваш пароль для повышения безопасности учетной записи
                </p>
            </div>
        </header>

        <!-- Основной контент -->
        <div class="container mx-auto px-6">
            <div class="max-w-md mx-auto">
                <div class="bg-white rounded-2xl shadow-xl overflow-hidden transform transition-all duration-300 hover:shadow-2xl">
                    <div class="h-2 bg-gradient-to-r from-indigo-400 to-purple-600"></div>

                    <div class="p-8">
                        <div v-if="successMessage" class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg text-green-700 animate-fade-in">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                </svg>
                                <p>{{ successMessage }}</p>
                            </div>
                        </div>

                        <div v-if="errorMessage" class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg text-red-700 animate-fade-in">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p>{{ errorMessage }}</p>
                            </div>
                        </div>

                        <form @submit.prevent="submitForm" class="space-y-6">
                            <div class="relative">
                                <label for="current_password" class="block text-sm font-medium text-gray-700 mb-1">Текущий пароль:</label>
                                <div class="relative">
                                    <input
                                        type="password"
                                        id="current_password"
                                        v-model="current_password"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 transition-all duration-300"
                                        :class="{ 'border-red-500 ring-red-500': errors.current_password }"
                                        placeholder="Введите текущий пароль"
                                        required
                                    />
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="errors.current_password" class="mt-1 text-sm text-red-600 animate-fade-in">
                                    {{ errors.current_password[0] }}
                                </div>
                            </div>

                            <div class="relative">
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Новый пароль:</label>
                                <div class="relative">
                                    <input
                                        type="password"
                                        id="password"
                                        v-model="password"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 transition-all duration-300"
                                        :class="{ 'border-red-500 ring-red-500': errors.password }"
                                        placeholder="Минимум 8 символов"
                                        required
                                    />
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="errors.password" class="mt-1 text-sm text-red-600 animate-fade-in">
                                    {{ errors.password[0] }}
                                </div>
                                <p class="mt-2 text-sm text-gray-500">
                                    Используйте комбинацию букв, цифр и специальных символов
                                </p>
                            </div>

                            <div class="relative">
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Подтверждение нового пароля:</label>
                                <div class="relative">
                                    <input
                                        type="password"
                                        id="password_confirmation"
                                        v-model="password_confirmation"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 transition-all duration-300"
                                        placeholder="Повторите новый пароль"
                                        required
                                    />
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <button
                                    type="submit"
                                    class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white py-3 px-4 rounded-lg transition-all duration-300 transform hover:scale-[1.02] hover:shadow-lg flex items-center justify-center"
                                    :disabled="loading"
                                >
                                    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Изменить пароль
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Блок с рекомендациями по безопасности -->
                <div class="mt-8 space-y-6">
                    <!-- Рекомендации по безопасности -->
                    <div class="bg-white rounded-xl shadow-md p-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                            <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Рекомендации по безопасности
                        </h3>
                        <div class="space-y-3 text-sm text-gray-600">
                                <h3 class="font-medium text-indigo-700 mb-2">Что делать, если я забыл пароль?</h3>
                                <p class="text-gray-600">Вы можете воспользоваться функцией
                                    <router-link to="/forgot-password" class="text-indigo-600 hover:text-indigo-800 transition duration-300">
                                        восстановления пароля
                                    </router-link>, указав email, привязанный к вашей учетной записи.
                                </p>
                        </div>
                    </div>
                    </div>
                </div>
            </div>
        </div>
</template>

<script>
import axios from 'axios';
import { Info } from 'lucide-vue-next';

export default {
    name: 'PasswordChange',
    data() {
        return {
            current_password: '',
            password: '',
            password_confirmation: '',
            loading: false,
            successMessage: '',
            errorMessage: '',
            errors: {}
        }
    },
    methods: {
        async submitForm() {
            this.loading = true;
            this.errorMessage = '';
            this.successMessage = '';
            this.errors = {};

            try {
                const response = await axios.post('/api/v1/password/change', {
                    current_password: this.current_password,
                    password: this.password,
                    password_confirmation: this.password_confirmation
                });

                this.successMessage = 'Ваш пароль успешно изменен';
                this.current_password = '';
                this.password = '';
                this.password_confirmation = '';
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                } else {
                    this.errorMessage = error.response?.data?.message || 'Произошла ошибка при изменении пароля';
                }
            } finally {
                this.loading = false;
            }
        }
    }
}
</script>

<style scoped>
</style>
