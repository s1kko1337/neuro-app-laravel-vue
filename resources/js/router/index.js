
import { createRouter, createWebHistory } from 'vue-router';
import Register from '../Components/Auth/Register.vue';
import Login from '../Components/Auth/Login.vue';
import EmailVerification from "../Components/Mail/EmailVerification.vue";
import EmailVerifyLink from "../Components/Mail/EmailVerifyLink.vue";
import { useAuthStore } from '../stores/authStore.js';
import PasswordResetRequest from "../Components/Auth/PasswordResetRequest.vue";
import PasswordChange from "../Components/Auth/PasswordChange.vue";
import PasswordResetForm from "../Components/Auth/PasswordResetForm.vue";

import App from '../Components/App.vue';
import Contact from "../Components/Contact.vue";
import About from "../Components/About.vue";
import Chat from "../Components/Chat.vue";

const routes = [
    {
        path: '/',
        name: 'Main',
        component: App,
    },
    {
        path: '/chat',
        name: 'ChatList',
        component: Chat,
        meta: {
            requiresAuth: true,
            requiresVerification: true
        }
    },
    {
        path: '/chat-survey',
        name: 'ChatSurvey',
        component: () => import('../Components/Voice/ChatSurvey.vue'),
        meta: {
            requiresAuth: true,
            requiresVerification: true
        }
    },
    {
        path: '/chat/:chatId',
        name: 'ChatDetail',
        component: Chat,
        meta: {
            requiresAuth: true,
            requiresVerification: true
        }
    },
    {
        path: '/about',
        name: 'About',
        component: About,
    },
    {
        path: '/contact',
        name: 'Contact',
        component: Contact,
    },
    {
        path: '/login',
        name: 'login',
        component: Login,
    },
    {
        path: '/register',
        name: 'register',
        component: Register,
    },
    {
        path: '/email/verify',
        name: 'emailVerification',
        component: EmailVerification,
    },
    {
        path: '/email/verify/:id/:hash',
        name: 'emailVerifyLink',
        component: EmailVerifyLink,
    },
    {
        path: '/forgot-password',
        name: 'passwordRequest',
        component: PasswordResetRequest,
        meta: {
            auth: false
        }
    },
    {
        path: '/reset-password/:token',
        name: 'passwordReset',
        component: PasswordResetForm,
        meta: {
            auth: false
        }
    },
    {
        path: '/profile/password',
        name: 'passwordChange',
        component: PasswordChange,
        meta: {
            auth: true
        }
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

// Добавляем навигационные хуки
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore();

    if (!authStore.initialized) {
        await authStore.initialize();
    }
    if (to.name === 'emailVerifyLink') {
        next();
        return;
    }

    // Проверяем, требует ли маршрут аутентификации
    if (to.matched.some(record => record.meta.requiresAuth)) {
        // Если пользователь не авторизован, перенаправляем на страницу входа
        if (!authStore.isAuthenticated) {
            next({ name: 'login' });
            return;
        }

        // Если маршрут требует верификации почты
        if (to.matched.some(record => record.meta.requiresVerification)) {
            try {
                // Проверяем статус верификации
                if (!authStore.isVerified) {
                    const verificationStatus = await authStore.checkVerificationStatus();

                    // Если email не подтвержден, перенаправляем на страницу верификации
                    if (!verificationStatus.verified) {
                        next({ name: 'emailVerification' });
                        return;
                    }
                }
            } catch (error) {
                console.error('Ошибка при проверке статуса верификации:', error);
                // При ошибке проверки перенаправляем на страницу верификации для безопасности
                next({ name: 'emailVerification' });
                return;
            }
        }
    }

    // Если пользователь авторизован и пытается перейти на страницу входа или регистрации
    if (authStore.isAuthenticated && (to.name === 'login' || to.name === 'register')) {
        next({ name: 'ChatList' });
        return;
    }

    next();
});

export default router;
