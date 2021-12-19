import { defineConfig } from 'vite'
import { resolve } from 'path';
import vue from '@vitejs/plugin-vue';
import WindiCSS from 'vite-plugin-windicss';

// https://vitejs.dev/config/
export default defineConfig({
    plugins: [vue(), WindiCSS()],
    base: '/',
	resolve: {
		alias: {
			'@': resolve(__dirname, './src'),
		}
	},
    build: {
        outDir: '../public',
        rollupOptions: {
            input: {
                consumer: resolve(__dirname, 'index.html'),
                admin: resolve(__dirname, 'admin/index.html')
            },
			output: {
				manualChunks(id) {
					if(id.includes('naive')) {
						return 'naive';
					}
				}
			}
        }
    },
    server: {
        watch: {
            usePolling: true
        }
    }
})
