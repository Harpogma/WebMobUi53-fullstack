import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';
import { quasar, transformAssetUrls } from "@quasar/vite-plugin";

export default defineConfig({
    plugins: [
        vue({
            template: { transformAssetUrls },
        }),
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/poll-dashboard.js",
                "resources/js/poll-dashboard-integrated.js",
            ],
            refresh: true,
        }),
        tailwindcss(),
        quasar(),
    ],
    server: {
        host: true,
        hmr: {
            host: "localhost",
        },
        watch: {
            ignored: ["**/storage/framework/views/**"],
        },
    },
    resolve: {
        alias: {
            "@": "/src",
        },
    },
});
