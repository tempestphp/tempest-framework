import { afterEach, describe, expect, it, vi } from 'vitest'
import tempest from './plugin'
import { mockTempestConfiguration } from './test-utils'

describe('tempest', () => {
	afterEach(() => {
		vi.restoreAllMocks()
	})

	it('it returns a Vite plugin', () => {
		mockTempestConfiguration()

		const plugin = tempest()

		expect(plugin).not.toBeNull()
	})

	it('it outputs a config', async () => {
		mockTempestConfiguration({
			build_directory: 'build/custom',
			bridge_file_name: 'custom-bridge',
			manifest: 'custom-manifest.json',
			entrypoints: ['src/main.ts'],
		})

		const plugin = tempest()

		// @ts-expect-error typing
		const config = await plugin.config({}, { command: 'build' })

		expect(config.base).toBe('/build/custom/')
		expect(config.build.manifest).toBe('custom-manifest.json')
		expect(config.build.rollupOptions).toEqual({ input: [
			'src/main.ts',
		] })
	})
})
