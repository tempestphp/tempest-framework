import { afterEach, expect, test, vi } from 'vitest'
import { loadTempestConfiguration } from './config'
import { mockTempestConfiguration, writeFixture } from './test-utils'

afterEach(() => {
	delete process.env.TEMPEST_PLUGIN_CONFIGURATION_PATH
	delete process.env.TEMPEST_PLUGIN_CONFIGURATION_OVERRIDE

	vi.restoreAllMocks()
})

test('the configuration can be loaded', async () => {
	mockTempestConfiguration()

	const config = await loadTempestConfiguration()

	expect(config).toHaveProperty('build_directory')
	expect(config).toHaveProperty('bridge_file_name')
	expect(config).toHaveProperty('manifest')
	expect(config).toHaveProperty('entrypoints')
})

test('the configuration can be overriden', async () => {
	process.env.TEMPEST_PLUGIN_CONFIGURATION_OVERRIDE = JSON.stringify({
		build_directory: 'build/from-override',
		bridge_file_name: 'vite-override',
		manifest: 'override-manifest.json',
		entrypoints: ['foo.js'],
	})

	const config = await loadTempestConfiguration()

	expect(config.build_directory).toBe('build/from-override')
	expect(config.bridge_file_name).toBe('vite-override')
	expect(config.manifest).toBe('override-manifest.json')
	expect(config.entrypoints).toEqual(['foo.js'])
})

test('the configuration can be loaded from a file path environment variable', async () => {
	const path = await writeFixture(
		'tempest-vite.test.json',
		JSON.stringify({
			build_directory: 'build/from-file',
			bridge_file_name: 'test-bridge',
			manifest: 'test-manifest.json',
			entrypoints: ['resources/js/test.js'],
		}),
	)

	process.env.TEMPEST_PLUGIN_CONFIGURATION_PATH = path

	const config = await loadTempestConfiguration()

	expect(config.build_directory).toBe('build/from-file')
	expect(config.bridge_file_name).toBe('test-bridge')
	expect(config.manifest).toBe('test-manifest.json')
	expect(config.entrypoints).toEqual(['resources/js/test.js'])
})
