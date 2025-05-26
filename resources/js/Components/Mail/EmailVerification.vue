<template>
    <div class="max-w-lg mx-auto">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Статус подтверждения почты</h2>

        <!-- Индикатор загрузки -->
        <div v-if="loading" class="flex justify-center my-8">
            <svg class="animate-spin h-10 w-10 text-indigo-600" xmlns="http://www.w3.org/2000/svg"
                 fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor"
                      d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
        </div>

        <!-- Успешное подтверждение -->
        <div v-else-if="verified" class="bg-green-50 border-l-4 border-green-500 text-green-700 p-6 rounded-lg shadow-md mb-6">
            <div class="flex items-center mb-4">
                <svg class="h-8 w-8 text-green-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
                <h3 class="text-xl font-bold">Почта подтверждена!</h3>
            </div>
            <p class="mb-4">Ваш адрес электронной почты подтвержден. Вы можете использовать все функции приложения.</p>
            <button @click="redirectToDashboard"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
                Перейти в панель управления
            </button>
        </div>

        <!-- Ошибка проверки статуса -->
        <div v-else-if="error" class="bg-red-50 border-l-4 border-red-500 text-red-700 p-6 rounded-lg shadow-md mb-6">
            <div class="flex items-center mb-4">
                <svg class="h-8 w-8 text-red-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-xl font-bold">Ошибка проверки статуса</h3>
            </div>
            <p class="mb-4">{{ errorMessage }}</p>
            <div v-if="authStore.isAuthenticated" class="mt-4">
                <button @click="checkVerificationStatus"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50 mr-2">
                    Проверить снова
                </button>
            </div>
            <div v-else class="mt-4">
                <p class="mb-2">Пожалуйста, войдите в систему, чтобы проверить статус подтверждения.</p>
                <button @click="redirectToLogin"
                        class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50">
                    Войти в систему
                </button>
            </div>
        </div>

        <!-- Ожидание подтверждения -->
        <div v-else class="bg-blue-50 border-l-4 border-blue-500 text-blue-700 p-6 rounded-lg shadow-md mb-6">
            <div class="flex items-center mb-4">
                <svg class="h-8 w-8 text-blue-500 mr-3" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <h3 class="text-xl font-bold">Требуется подтверждение</h3>
            </div>
            <p class="mb-4">Пожалуйста, подтвердите свой адрес электронной почты, перейдя по ссылке, которую мы вам отправили.</p>
            <p class="mb-4">Если вы не получили письмо, проверьте папку "Спам" или нажмите кнопку ниже, чтобы отправить новую ссылку.</p>
            <button @click="resendVerification"
                    class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-opacity-50"
                    :disabled="resending">
                <span v-if="resending">Отправка...</span>
                <span v-else>Отправить ссылку повторно</span>
            </button>
        </div>

        <!-- Дополнительная информация -->
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-700 mb-4">Зачем нужно подтверждение?</h3>
            <p class="text-gray-600 mb-4">
                Подтверждение электронной почты помогает нам убедиться, что вы:
            </p>
            <ul class="list-disc pl-5 text-gray-600 mb-4">
                <li>Действительно владеете указанным адресом</li>
                <li>Сможете получать важные уведомления</li>
                <li>Защитите свой аккаунт от несанкционированного доступа</li>
            </ul>
            <p class="text-gray-600">
                После подтверждения электронной почты вы получите полный доступ ко всем возможностям приложения.
            </p>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../stores/authStore.js';

const router = useRouter();
const authStore = useAuthStore();

// Состояния
const loading = ref(true);
const verified = ref(false);
const error = ref(false);
const errorMessage = ref('');
const resending = ref(false);

// Проверка статуса подтверждения при загрузке
onMounted(async () => {
    try {
        // Проверяем, инициализирован ли auth store
        if (!authStore.initialized) {
            await authStore.initialize();
        }

        // Если пользователь не авторизован
        if (!authStore.isAuthenticated) {
            error.value = true;
            errorMessage.value = 'Для проверки статуса подтверждения электронной почты необходимо войти в систему.';
            loading.value = false;
            return;
        }

        // Проверяем статус верификации
        await checkVerificationStatus();
    } catch (err) {
        console.error('Ошибка при инициализации:', err);
        error.value = true;
        errorMessage.value = 'Произошла ошибка при инициализации. Пожалуйста, попробуйте позже.';
        loading.value = false;
    }
});

// Проверка статуса подтверждения электронной почты
const checkVerificationStatus = async () => {
    loading.value = true;
    error.value = false;
    errorMessage.value = '';

    if (!authStore.isAuthenticated) {
        error.value = true;
        errorMessage.value = 'Для проверки статуса подтверждения необходимо войти в систему.';
        loading.value = false;
        return;
    }

    try {
        console.log('Проверка статуса верификации email...');
        const response = await authStore.checkVerificationStatus();
        console.log('Ответ проверки статуса:', response);

        if (response) {
            verified.value = response.verified;
        } else {
            error.value = true;
            errorMessage.value = 'Не удалось проверить статус подтверждения.';
        }
    } catch (err) {
        console.error('Ошибка при проверке статуса:', err);
        error.value = true;
        errorMessage.value = err.response?.data?.message || 'Не удалось проверить статус подтверждения.';
    } finally {
        loading.value = false;
    }
};

// Повторная отправка ссылки для подтверждения почты
const resendVerification = async () => {
    if (!authStore.isAuthenticated) {
        error.value = true;
        errorMessage.value = 'Для повторной отправки ссылки необходимо войти в систему.';
        return;
    }

    resending.value = true;
    try {
        console.log('Отправка нового письма для верификации...');
        const response = await authStore.resendVerificationEmail();
        console.log('Ответ отправки:', response);

        alert('Новая ссылка подтверждения была отправлена на вашу электронную почту.');
    } catch (err) {
        console.error('Ошибка при отправке:', err);
        alert('Произошла ошибка при отправке ссылки подтверждения: ' + (err.response?.data?.message || 'Неизвестная ошибка'));
    } finally {
        resending.value = false;
    }
};

// Перенаправление в панель управления
const redirectToDashboard = () => {
    router.push({ name: 'Main' });
};

// Перенаправление на страницу входа
const redirectToLogin = () => {
    router.push({ name: 'login' });
};
</script>
