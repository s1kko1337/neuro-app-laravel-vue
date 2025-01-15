import { defineStore } from "pinia";

export const useRouterStore = defineStore('router', {
    state: () => ({
        lastVisitedRoute: localStorage.getItem('lastVisitedRoute') || '/',
    }),
    actions: {
        setLastVisitedRoute(route) {
            if (!route) {
                this.lastVisitedRoute = '/';
            } else {
                this.lastVisitedRoute = route;
            }
            localStorage.setItem('lastVisitedRoute', this.lastVisitedRoute);
        },
    },
});
