<template>
    <div class="bg-gradient-to-br from-indigo-50 via-white to-blue-50 min-h-screen py-12">
        <!-- Хедер с анимированным фоном -->
        <header class="relative py-12 mb-10 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-purple-600 opacity-90"></div>
            <div class="absolute inset-0">
                <div class="wave-pattern"></div>
            </div>
            <div class="container mx-auto px-6 relative z-10">
                <h1 class="text-3xl md:text-4xl font-bold text-white text-center mb-3">
                    Обновление пароля
                </h1>
                <p class="text-lg text-indigo-100 text-center max-w-3xl mx-auto">
                    Создайте новый безопасный пароль для вашей учетной записи
                </p>
            </div>
        </header>

        <!-- Основной контент -->
        <div class="container mx-auto px-6">
            <div class="max-w-md mx-auto">
                <div
                    class="bg-white rounded-2xl shadow-xl overflow-hidden transform transition-all duration-300 hover:shadow-2xl">
                    <div class="h-2 bg-gradient-to-r from-indigo-400 to-purple-600"></div>

                    <div class="p-8">
                        <div v-if="successMessage"
                             class="mb-6 bg-green-50 border-l-4 border-green-500 p-4 rounded-r-lg text-green-700 animate-fade-in">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3 text-green-500" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M5 13l4 4L19 7"></path>
                                </svg>
                                <p>{{ successMessage }}</p>
                            </div>
                        </div>

                        <div v-if="errorMessage"
                             class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg text-red-700 animate-fade-in">
                            <div class="flex items-center">
                                <svg class="w-6 h-6 mr-3 text-red-500" fill="none" stroke="currentColor"
                                     viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <p>{{ errorMessage }}</p>
                            </div>
                        </div>

                        <form @submit.prevent="submitForm" v-if="!successMessage" class="space-y-6">
                            <input type="hidden" v-model="token">

                            <div class="relative">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email:</label>
                                <div class="relative">
                                    <input
                                        type="email"
                                        id="email"
                                        v-model="email"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 transition-all duration-300"
                                        :class="{ 'border-red-500 ring-red-500': errors.email }"
                                        placeholder="Введите ваш email"
                                        required
                                    />
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="errors.email" class="mt-1 text-sm text-red-600 animate-fade-in">
                                    {{ errors.email[0] }}
                                </div>
                            </div>

                            <div class="relative">
                                <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Новый
                                    пароль:</label>
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
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
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
                                <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Подтверждение
                                    пароля:</label>
                                <div class="relative">
                                    <input
                                        type="password"
                                        id="password_confirmation"
                                        v-model="password_confirmation"
                                        class="w-full pl-10 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-gray-700 transition-all duration-300"
                                        placeholder="Повторите пароль"
                                        required
                                    />
                                    <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor"
                                             viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
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
                                    <svg v-if="loading" class="animate-spin -ml-1 mr-2 h-5 w-5 text-white"
                                         xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                                stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor"
                                              d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    Обновить пароль
                                </button>
                            </div>
                        </form>

                        <div class="mt-6 text-center" v-if="successMessage">
                            <router-link to="/login"
                                         class="inline-flex items-center px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg shadow transition-all duration-300 transform hover:scale-105">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path>
                                </svg>
                                Перейти на страницу входа
                            </router-link>
                        </div>

                        <div class="mt-8 flex items-center justify-center space-x-2" v-if="!successMessage">
                            <div class="h-px bg-gray-200 w-full"></div>
                            <span class="text-sm text-gray-400 whitespace-nowrap px-3">или</span>
                            <div class="h-px bg-gray-200 w-full"></div>
                        </div>

                        <div class="mt-4 text-center" v-if="!successMessage">
                            <router-link to="/forgot-password"
                                         class="inline-flex items-center text-indigo-600 hover:text-indigo-800 transition duration-300">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                     xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Запросить новую ссылку
                            </router-link>
                        </div>
                    </div>
                </div>

                <!-- Справочная информация -->
                <div class="mt-8 bg-white rounded-xl shadow-md p-6">
                    <h3 class="text-lg font-medium text-gray-800 mb-4 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Рекомендации по безопасности
                    </h3>
                    <ul class="space-y-3 text-sm text-gray-600">
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-indigo-400 mr-2 flex-shrink-0 mt-0.5" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Используйте не менее 8 символов</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-indigo-400 mr-2 flex-shrink-0 mt-0.5" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Комбинируйте буквы верхнего и нижнего регистра, цифры и специальные символы</span>
                        </li>
                        <li class="flex items-start">
                            <svg class="w-5 h-5 text-indigo-400 mr-2 flex-shrink-0 mt-0.5" fill="none"
                                 stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                      d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span>Не используйте один и тот же пароль для разных сайтов</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';
import {useAuthStore} from "../../stores/authStore.js";

export default {
    name: 'PasswordResetForm',
    setup() {
        return useAuthStore().user;
    },

    data() {
        return {
            token: '',
            email: '',
            password: '',
            password_confirmation: '',
            loading: false,
            successMessage: '',
            errorMessage: '',
            errors: {}
        }
    },
    created() {
        // Извлекаем токен из URL
        this.token = this.$route.params.token;

        // Декодируем email из query-параметра, если он есть
        const queryEmail = this.$route.query.email;
        if (queryEmail) {
            this.email = decodeURIComponent(queryEmail);
        }

        if (!this.token) {
            this.errorMessage = 'Неверная или просроченная ссылка для сброса пароля';
        }
    },
    methods: {
        async submitForm() {
            this.loading = true;
            this.errorMessage = '';
            this.successMessage = '';
            this.errors = {};

            try {
                const response = await axios.post('/api/v1/reset-password', {
                    token: this.token,
                    email: this.email,
                    password: this.password,
                    password_confirmation: this.password_confirmation
                });

                if (response.data.status === 'passwords.reset') {
                    this.successMessage = response.data.message || 'Ваш пароль успешно обновлен';
                    this.password = '';
                    this.password_confirmation = '';
                } else {
                    this.errorMessage = response.data.message || 'Что-то пошло не так при обновлении пароля';
                }
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                } else {
                    this.errorMessage = error.response?.data?.message || 'Произошла ошибка при обновлении пароля';
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
