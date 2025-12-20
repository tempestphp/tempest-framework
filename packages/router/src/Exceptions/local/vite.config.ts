import ui from '@nuxt/ui/vite'
import tailwindcss from '@tailwindcss/vite'
import vue from '@vitejs/plugin-vue'
import { defineConfig } from 'vite'
import { viteSingleFile } from 'vite-plugin-singlefile'

export default defineConfig({
	plugins: [
		vue(),
		tailwindcss(),
		ui({
			ui: {
				colors: {
					primary: 'blue',
					neutral: 'zinc',
				},
				card: {
					defaultVariants: {
						variant: 'soft',
					},
				},
				badge: {
					defaultVariants: {
						variant: 'soft',
					},
				},
				button: {
					slots: {
						base: 'not-disabled:cursor-pointer',
					},
					defaultVariants: {
						variant: 'soft',
					},
				},
				tooltip: {
					slots: {
						content: 'px-4 py-2 h-auto bg-default/90 text-toned font-mono',
					},
				},
			},
		}),
		viteSingleFile(),
	],
	build: {
		watch: {
			exclude: 'dist/**', // prevent infinite loops while using --watch
		},
		rollupOptions: {
			input: ['./src/entrypoint/main.ts'],
			output: {
				inlineDynamicImports: true,
				assetFileNames: '[name][extname]',
				entryFileNames: '[name].js',
			},
		},
	},
})
