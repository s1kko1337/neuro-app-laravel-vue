import {defineStore} from 'pinia';
import axios from 'axios';

// Настраиваем axios для работы с куками и CSRF
axios.defaults.withCredentials = true;

// Восстанавливаем токен из localStorage при инициализации
const storedToken = localStorage.getItem('token');
if (storedToken) {
    axios.defaults.headers.common['Authorization'] = `Bearer ${storedToken}`;
}

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        loading: false,
        error: null,
        initialized: false // Флаг инициализации хранилища
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        userEmail: (state) => state.user?.email,
        isVerified: (state) => !!state.user?.email_verified_at
    },

    actions: {
        // Инициализация хранилища при загрузке приложения
        async initialize() {
            if (this.initialized) return;

            this.loading = true;

            try {
                if (this.token) {
                    // Восстанавливаем заголовок Authorization
                    axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                    // Пробуем загрузить данные пользователя
                    await this.fetchUser();
                }
            } catch (error) {
                console.error('Auth initialization error:', error);
                // Если токен недействителен, очищаем состояние
                if (error.response?.status === 401) {
                    this.clearAuthState();
                }
            } finally {
                this.loading = false;
                this.initialized = true;
            }
        },

        // Вспомогательный метод для установки токена
        setToken(token) {
            this.token = token;
            localStorage.setItem('token', token);
            axios.defaults.headers.common['Authorization'] = `Bearer ${token}`;
        },

        // Вспомогательный метод для очистки состояния аутентификации
        clearAuthState() {
            this.user = null;
            this.token = null;
            localStorage.removeItem('token');
            delete axios.defaults.headers.common['Authorization'];
        },

        async login(credentials) {
            this.loading = true;
            this.error = null;

            try {
                // Получаем CSRF-cookie
                await axios.get('/sanctum/csrf-cookie');

                // Отправляем запрос на логин
                const response = await axios.post('/api/v1/login', credentials);

                // Сохраняем токен
                if (response.data && response.data.token) {
                    this.setToken(response.data.token);

                    // Получение информации о пользователе
                    await this.fetchUser();

                    return true;
                } else {
                    throw new Error('Токен не получен от сервера');
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Произошла ошибка при входе';
                console.error('Login error:', error);
                return false;
            } finally {
                this.loading = false;
            }
        },

        async register(userData) {
            this.loading = true;
            this.error = null;

            try {
                // Получаем CSRF-cookie
                await axios.get('/sanctum/csrf-cookie');

                // Отправляем запрос на регистрацию
                const response = await axios.post('/api/v1/register', userData);

                // Сохраняем токен
                if (response.data && response.data.token) {
                    this.setToken(response.data.token);

                    // Получение информации о пользователе
                    await this.fetchUser();

                    return true;
                } else {
                    throw new Error('Токен не получен от сервера');
                }
            } catch (error) {
                this.error = error.response?.data?.message || 'Произошла ошибка при регистрации';
                console.error('Register error:', error);
                return false;
            } finally {
                this.loading = false;
            }
        },

        async fetchUser() {
            if (!this.token) return null;

            try {
                // Получаем CSRF-cookie
                await axios.get('/sanctum/csrf-cookie');

                // Запрашиваем данные пользователя
                const response = await axios.get('/api/v1/user');

                if (response.data) {
                    this.user = response.data;
                    return this.user;
                }
                return null;
            } catch (error) {
                console.error('Fetch user error:', error);
                if (error.response?.status === 401) {
                    this.clearAuthState();
                }
                return null;
            }
        },

        async checkVerificationStatus() {
            if (!this.token) return null;

            this.loading = true;
            this.error = null;

            try {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                await axios.get('/sanctum/csrf-cookie');

                const response = await axios.get('/api/v1/email/verify');

                if (response.data && response.data.verified) {
                    await this.fetchUser();
                }

                return response.data;
            } catch (error) {
                console.error('Verification status check error:', error);
                this.error = error.response?.data?.message || 'Ошибка при проверке статуса верификации';
                throw error;
            } finally {
                this.loading = false;
            }
        },

        async resendVerificationEmail() {
            if (!this.token) return null;

            this.loading = true;
            this.error = null;

            try {
                axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                await axios.get('/sanctum/csrf-cookie');

                const response = await axios.post('/api/v1/email/verification-notification');

                return response.data;
            } catch (error) {
                console.error('Resend verification email error:', error);
                this.error = error.response?.data?.message || 'Ошибка при отправке письма для подтверждения';
                throw error;
            } finally {
                this.loading = false;
            }
        },
        async logout() {
            try {
                if (this.token) {
                    // Устанавливаем Authorization заголовок для запроса вывода
                    axios.defaults.headers.common['Authorization'] = `Bearer ${this.token}`;

                    // Получаем CSRF-cookie
                    await axios.get('/sanctum/csrf-cookie');

                    // Отправляем запрос на выход
                    await axios.post('/api/v1/logout');
                }
            } catch (error) {
                console.error('Logout error:', error);
            } finally {
                // В любом случае очищаем состояние аутентификации
                this.clearAuthState();
            }
        }
    },

    persist: {
        enabled: true,
        strategies: [
            {
                key: 'auth-store',
                storage: localStorage,
                paths: ['token', 'user'] // Сохраняем только эти поля
            }
        ]
    }
});
