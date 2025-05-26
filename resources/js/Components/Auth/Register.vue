<template>
  <div class="max-w-md mx-auto">
    <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center">Регистрация</h2>

    <form class="space-y-4" @submit.prevent="handleRegister">
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-gray-700 mb-2" for="name">Имя</label>
          <input
              type="text"
              id="name"
              v-model="form.name"
              placeholder="Имя"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              :class="{'border-red-500': errors.name}"
          >
          <p v-if="errors.name" class="mt-1 text-sm text-red-500">{{ errors.name }}</p>
        </div>

        <div>
          <label class="block text-gray-700 mb-2" for="surname">Фамилия</label>
          <input
              type="text"
              id="surname"
              v-model="form.surname"
              placeholder="Фамилия"
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
              :class="{'border-red-500': errors.surname}"
          >
          <p v-if="errors.surname" class="mt-1 text-sm text-red-500">{{ errors.surname }}</p>
        </div>
      </div>

      <div>
        <label class="block text-gray-700 mb-2" for="email">Email</label>
        <input
            type="email"
            id="email"
            v-model="form.email"
            placeholder="Ваш email"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            :class="{'border-red-500': errors.email}"
        >
        <p v-if="errors.email" class="mt-1 text-sm text-red-500">{{ errors.email }}</p>
      </div>

      <div>
        <label class="block text-gray-700 mb-2" for="password">Пароль</label>
        <input
            type="password"
            id="password"
            v-model="form.password"
            placeholder="Создайте пароль"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            :class="{'border-red-500': errors.password}"
        >
        <p v-if="errors.password" class="mt-1 text-sm text-red-500">{{ errors.password }}</p>
      </div>

      <div>
        <label class="block text-gray-700 mb-2" for="password_confirmation">Подтверждение пароля</label>
        <input
            type="password"
            id="password_confirmation"
            v-model="form.password_confirmation"
            placeholder="Подтвердите пароль"
            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition"
            :class="{'border-red-500': errors.password_confirmation}"
        >
        <p v-if="errors.password_confirmation" class="mt-1 text-sm text-red-500">{{ errors.password_confirmation }}</p>
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
                    Регистрация...
                </span>
        <span v-else>Зарегистрироваться</span>
      </button>

      <div v-if="authStore.error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative">
        {{ authStore.error }}
      </div>
    </form>

    <div class="mt-6 text-center">
      <p class="text-gray-600">
        Уже есть аккаунт?
        <router-link :to="{name:'login'}" class="text-indigo-600 hover:underline">Войти</router-link>
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
  name: '',
  surname: '',
  email: '',
  password: '',
  password_confirmation: '',
});

const errors = reactive({
  name: '',
  surname: '',
  email: '',
  password: '',
  password_confirmation: '',
});

// Проверка формы
const validateForm = () => {
  let isValid = true;

  // Сброс ошибок
  Object.keys(errors).forEach(key => {
    errors[key] = '';
  });

  // Проверка имени
  if (!form.name) {
    errors.name = 'Имя обязательно';
    isValid = false;
  }

  // Проверка фамилии
  if (!form.surname) {
    errors.surname = 'Фамилия обязательна';
    isValid = false;
  }

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

  // Проверка подтверждения пароля
  if (!form.password_confirmation) {
    errors.password_confirmation = 'Подтвердите пароль';
    isValid = false;
  } else if (form.password !== form.password_confirmation) {
    errors.password_confirmation = 'Пароли не совпадают';
    isValid = false;
  }

  return isValid;
};

// Обработка регистрации
const handleRegister = async () => {
  if (!validateForm()) return;

  const success = await authStore.register({
    name: form.name,
    surname: form.surname,
    email: form.email,
    password: form.password,
    password_confirmation: form.password_confirmation
  });

  if (success) {
    router.push({name: 'emailVerification'});
  }
};
</script>
