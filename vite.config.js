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
        // Relative to the root
        outDir: '../build',
        rollupOptions: {
            output: {
                assetFileNames: "assets/[name].[ext]",
            }
        },
        sourcemap: true
    },
    server: {
        proxy: {
            '/api/': 'http://invoicing.tunk.io:8081',
            '/bundles': 'http://invoicing.tunk.io:8081',
        }
    }
});
