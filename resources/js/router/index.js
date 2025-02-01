import { createRouter, createWebHistory } from 'vue-router';
import { onMounted } from 'vue';

import App from '../Components/App.vue';
import { useRouterStore } from "../stores/routerStore.js";
import Contact from "../Components/Contact.vue";
import About from "../Components/About.vue";
import Chat from "../Components/Chat.vue";



const routes = [
    // {
    //     path: '/',
    //     name: 'Root',
    //     component: App,
    // },
    // {
    //     path: '/groups',
    //     name: 'Groups',
    //     component: Groups,
    //     // beforeEnter: async (to, from, next) => {
    //     //     const userStore = useUserStore();
    //     //     try {
    //     //         await userStore.checkAuth();
    //     //         if (userStore.isAuthenticated) {
    //     //             next();
    //     //         } else {
    //     //             next('/');
    //     //         }
    //     //     } catch (error) {
    //     //         //console.error("Ошибка при проверке аутентификации:", error.message);
    //     //         if (error.response.status === 401) {
    //     //             next('/');
    //     //         } else {
    //     //             next();
    //     //         }
    //     //     }
    //     // },
    //     children: [
    //         {
    //             path: '/groups/all',
    //             name: 'GroupsView',
    //             component: GroupsView
    //         },
    //     ]
    // },
    {
        path: '/',
        name: 'Main',
        component: App,
    },
    {
        path: '/chat/:chatId?',
        name: 'Chat',
        component: Chat,
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
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes,
});

router.beforeEach((to, from, next) => {
    const routerStore = useRouterStore();
    if (to.path === '/') {
        routerStore.setLastVisitedRoute('/');
    } else {
        routerStore.setLastVisitedRoute(to.path);
    }
    next();
});

export default router;
