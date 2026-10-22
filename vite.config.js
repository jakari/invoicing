import { defineConfig } from 'vite';
import path from 'path'

export default defineConfig({
    root: 'client',
    resolve: {
        alias: {
            '/*': path.resolve(__dirname, './client'),
        },
    },
    build: {
        // Relative to the root; Ktor serves it (WEB_ROOT)
        outDir: '../dist',
        emptyOutDir: true,
        rollupOptions: {
            output: {
                assetFileNames: "assets/[name].[ext]",
            }
        },
        sourcemap: true
    },
    server: {
        proxy: {
            // ./gradlew run
            '/api/': 'http://localhost:8080',
        }
    }
});
