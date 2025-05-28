<template>
    <div class="max-w-lg mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Подтверждение электронной почты</h2>

        <!-- Индикатор загрузки -->
        <div v-if="loading" class="flex justify-center my-8 flex-col items-center">
            <svg class="animate-spin h-10 w-10 text-indigo-600" xmlns="http://www.w3.org/2000/svg"
                 fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            <p class="mt-4 text-gray-600">Подтверждение вашего адреса электронной почты...</p>
        </div>

        <!-- Успешное подтверждение -->
        <div v-else-if="verified" class="bg-green-50 border-l-4 border-green-500 text-green-700 p-6 rounded-lg shadow-md mb-6">
            <div class="flex items-center mb-4">
                <svg class="h-8 w-8 text-green-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <h3 class="text-xl font-bold">Успешное подтверждение!</h3>
            </div>
            <p class="mb-4">Ваш адрес электронной почты {{ verifiedEmail }} был успешно подтвержден. Теперь вы можете использовать все функции приложения.</p>
            <div class="flex space-x-4">
                <button @click="redirectToDashboard"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
                    Перейти в чаты
                </button>
                <button @click="redirectToHome"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                    На главную
                </button>
            </div>
        </div>

        <!-- Ошибка подтверждения -->
        <div v-else class="bg-red-50 border-l-4 border-red-500 text-red-700 p-6 rounded-lg shadow-md mb-6">
            <div class="flex items-center mb-4">
                <svg class="h-8 w-8 text-red-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-xl font-bold">Ошибка подтверждения</h3>
            </div>
            <p class="mb-4 font-medium">{{ errorMessage }}</p>

            <div v-if="errorDetails" class="mb-4">
                <p class="mb-2">Подробная информация об ошибке:</p>
                <pre class="bg-red-100 p-3 rounded text-sm overflow-auto max-h-32">{{ errorDetails }}</pre>
            </div>

            <p class="mb-4">Возможные причины:</p>
            <ul class="list-disc pl-5 mb-4">
                <li>Срок действия ссылки истек</li>
                <li>Ссылка уже была использована</li>
                <li>Вы не авторизованы в системе</li>
                <li>Ссылка была изменена или повреждена</li>
            </ul>

            <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-4">
                <button v-if="authStore.isAuthenticated" @click="redirectToVerificationStatus"
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-opacity-50">
                    Проверить статус верификации
                </button>
                <button v-else @click="redirectToLogin"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
                    Войти в систему
                </button>
                <button @click="redirectToHome"
                        class="px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-opacity-50">
                    На главную
                </button>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/authStore.js';
import axios from 'axios';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

// Состояния
const loading = ref(true);
const verified = ref(false);
const errorMessage = ref('');
const errorDetails = ref('');
const verifiedEmail = ref('');

// Выполняем прямой запрос к API для верификации
onMounted(async () => {
    try {
        console.log('EmailVerifyLink: Начало проверки');
        console.log('Параметры URL:', route.params, route.query);

        // Проверяем, есть ли параметры в URL
        if (!route.params.id || !route.params.hash) {
            errorMessage.value = 'Отсутствуют необходимые параметры для верификации';
            loading.value = false;
            return;
        }

        // Если пользователь не авторизован
        if (!authStore.isAuthenticated) {
            errorMessage.value = 'Для подтверждения электронной почты необходимо войти в систему';
            loading.value = false;
            return;
        }

        const id = route.params.id;
        const hash = route.params.hash;
        const expires = route.query.expires;
        const signature = route.query.signature;

        // Прямой запрос к API верификации
        // ВАЖНО: использовать точный API URL
        const url = `/api/v1/email/verify/${id}/${hash}?expires=${expires}&signature=${signature}`;
        console.log('Отправка запроса на верификацию:', url);

        // Устанавливаем токен в заголовок запроса
        const token = authStore.token;
        if (!token) {
            throw new Error('Токен авторизации отсутствует');
        }

        axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;

        // Отправляем запрос на верификацию
        const response = await axios.get(url);
        console.log('Ответ API верификации:', response.data);

        // Если верификация успешна
        if (response.data && response.data.verified) {
            verified.value = true;
            verifiedEmail.value = response.data.email || authStore.userEmail || '';

            // Обновляем информацию о пользователе
            await authStore.fetchUser();
        } else {
            throw new Error('Сервер вернул неожиданный ответ');
        }
    } catch (error) {
        console.error('Ошибка при верификации email:', error);

        errorMessage.value = 'Произошла ошибка при подтверждении email';

        if (error.response) {
            // Получаем детальную информацию об ошибке от сервера
            errorDetails.value = `Статус: ${error.response.status}\nДанные: ${JSON.stringify(error.response.data, null, 2)}`;
            errorMessage.value = error.response.data?.message || errorMessage.value;
        } else if (error.request) {
            errorDetails.value = 'Нет ответа от сервера. Проверьте подключение к интернету.';
        } else {
            errorDetails.value = error.message || 'Неизвестная ошибка';
        }
    } finally {
        loading.value = false;
    }
});

// Перенаправление в панель управления
const redirectToDashboard = () => {
    router.push({ name: 'ChatList' });
};

// Перенаправление на страницу входа
const redirectToLogin = () => {
    router.push({ name: 'login' });
};

// Перенаправление на главную страницу
const redirectToHome = () => {
    router.push({ name: 'Main' });
};

// Перенаправление на страницу проверки статуса верификации
const redirectToVerificationStatus = () => {
    router.push({ name: 'emailVerification' });
};
</script>
