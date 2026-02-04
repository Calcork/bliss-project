import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [react()],
    publicDir: false,
    build: {
        outDir: 'public/js/react',
        emptyOutDir: false,
        rollupOptions: {
            input: 'app/twig/react/main.tsx',
            output: {
                entryFileNames: 'main.js',
            },
        },
    },
});
