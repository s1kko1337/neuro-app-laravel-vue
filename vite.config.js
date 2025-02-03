import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig({
    server: {
        strictPort : true ,
        host : '0.0.0.0',
        port: 5173,
        origin : "http://127.0.0.1:5173" ,
    },
    plugins: [
        vue(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
    build: {
        outDir: 'public/build',
        assetsDir: '',
        manifest: true,
    },
});
