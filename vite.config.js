import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";

const vitePort = Number.parseInt(process.env.VITE_DEV_SERVER_PORT || "5174", 10);

export default defineConfig({
    plugins: [
        laravel({
            input: ["resources/css/app.css", "resources/js/app.js"],
            refresh: true,
        }),
    ],
    server: {
        host: "127.0.0.1",
        port: Number.isFinite(vitePort) ? vitePort : 5174,
        strictPort: true,
        hmr: {
            host: "127.0.0.1",
        },
    },
});
