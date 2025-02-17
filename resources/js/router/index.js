import { createRouter, createWebHistory } from 'vue-router';
import { onMounted } from 'vue';

import App from '../Components/App.vue';
import { useRouterStore } from "../stores/routerStore.js";
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
