import './bootstrap';
import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from "./router/index.js";
import  App from './Components/App.vue'
const pinia = createPinia();

createApp(App).use(router).use(pinia).mount("#app");
