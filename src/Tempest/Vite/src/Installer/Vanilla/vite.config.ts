import { defineConfig } from 'vite'
import tempest from 'vite-plugin-tempest'

export default defineConfig({
	plugins: [
		tempest(),
	]
})
