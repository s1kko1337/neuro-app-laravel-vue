import { defineStore } from "pinia";
import { ref } from 'vue';
import router from "@/router/index.js";

export const useUserStore = defineStore('user', () => {
    const user = ref(null);
    const isAuthenticated = ref(false);

    // const login = async (loginData) => {
    //     try {
    //         const response = await axios.post('/login', loginData);
    //         user.value = response.data.user;
    //         const encryptedPassword = CryptoJS.AES.encrypt(loginData.password, 'your-secret-key').toString();
    //         localStorage.setItem('isAuthenticated', 'true');
    //         isAuthenticated.value = true;
    //         showNotification(response.data.message, 1, 2000);
    //         router.push('/');
    //     } catch (e) {
    //         localStorage.setItem('isAuthenticated', 'false');
    //         isAuthenticated.value = false;
    //         if(e.response) {
    //             showNotification(e.response.data.error, 0, 2000)
    //         }
    //         throw(e);
    //     }
    // };
    //
    // const signin = async (signinData) => {
    //     try {
    //         const response = await axios.post('/signin', signinData);
    //         user.value = response.data.user;
    //         localStorage.setItem('isAuthenticated', 'true');
    //         isAuthenticated.value = true;
    //         showNotification(response.data.message, 1, 2000);
    //         router.push('/');
    //     } catch (e) {
    //         localStorage.setItem('isAuthenticated', 'false');
    //         isAuthenticated.value = false;
    //         if (e.response) {
    //             showNotification(e.response.data.error, 0, 2000);
    //         }
    //         throw(e);
    //     }
    // };
    //
    // const logout = async () => {
    //     try {
    //         await axios.post('/logout');
    //         user.value = null;
    //         localStorage.removeItem('encryptedPassword');
    //         localStorage.setItem('isAuthenticated', 'false');
    //         isAuthenticated.value = false;
    //         showNotification('Выход успешно выполнен!');
    //         router.push('/');
    //     } catch (e) {
    //         localStorage.setItem('isAuthenticated', 'true');
    //         isAuthenticated.value = true;
    //         //console.error('Ошибка при выходе: ', e);
    //         showNotification('Ошибка выхода', 0)
    //         throw(e);
    //     }
    // };
    //
    // const checkAuth = async () => {
    //     try {
    //         const response = await axios.get('/user'); // получение данных о пользователе
    //         user.value = response.data.user;
    //         isAuthenticated.value = localStorage.getItem('isAuthenticated') === 'true';
    //     } catch (e) {
    //         user.value = null;
    //         localStorage.setItem('isAuthenticated', 'false');
    //         isAuthenticated.value = false;
    //     }
    // };
    //
    // return { user, isAuthenticated, login, logout, checkAuth, signin };
});
