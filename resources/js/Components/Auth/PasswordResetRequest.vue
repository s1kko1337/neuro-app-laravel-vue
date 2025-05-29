
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
                    Сброс пароля
                </h1>
                <p class="text-lg text-indigo-100 text-center max-w-3xl mx-auto">
                    Введите email-адрес вашей учетной записи, и мы вышлем ссылку для сброса пароля
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

                        <form @submit.prevent="submitForm" class="space-y-6" v-if="!successMessage">
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
                                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207"></path>
                                        </svg>
                                    </div>
                                </div>
                                <div v-if="errors.email" class="mt-1 text-sm text-red-600 animate-fade-in">
                                    {{ errors.email[0] }}
                                </div>
                                <p class="mt-2 text-sm text-gray-500">
                                    Мы отправим ссылку для сброса пароля на указанный адрес
                                </p>
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
                                    Отправить ссылку для сброса
                                </button>
                            </div>
                        </form>

                        <div v-if="successMessage" class="flex justify-center mt-6">
                            <button
                                @click="resetForm"
                                class="px-6 py-2 bg-indigo-100 hover:bg-indigo-200 text-indigo-700 rounded-lg transition-all duration-300"
                            >
                                Отправить снова
                            </button>
                        </div>

                        <div class="mt-8 flex items-center justify-center space-x-2">
                            <div class="h-px bg-gray-200 w-full"></div>
                            <span class="text-sm text-gray-400 whitespace-nowrap px-3">или</span>
                            <div class="h-px bg-gray-200 w-full"></div>
                        </div>

                        <div class="mt-6 text-center">
                            <router-link to="/login" class="inline-flex items-center text-indigo-600 hover:text-indigo-800 transition duration-300">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                                </svg>
                                Вернуться на страницу входа
                            </router-link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'PasswordResetRequest',
    data() {
        return {
            email: '',
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
                const response = await axios.post('/api/v1/forgot-password', {
                    email: this.email
                });

                this.successMessage = 'На указанный email отправлена ссылка для сброса пароля';
            } catch (error) {
                if (error.response && error.response.status === 422) {
                    this.errors = error.response.data.errors || {};
                } else {
                    this.errorMessage = error.response?.data?.message || 'Произошла ошибка при отправке запроса на сброс пароля';
                }
            } finally {
                this.loading = false;
            }
        },
        resetForm() {
            this.email = '';
            this.successMessage = '';
            this.errorMessage = '';
            this.errors = {};
        }
    }
}
</script>

<style scoped>

</style>
