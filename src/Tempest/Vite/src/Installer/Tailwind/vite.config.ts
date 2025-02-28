import { defineConfig } from 'vite'
import tempest from 'vite-plugin-tempest'
import tailwindcss from '@tailwindcss/vite'

export default defineConfig({
	plugins: [
		tailwindcss(),
		tempest(),
	],
})
