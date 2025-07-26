import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { expect, test } from 'vitest'
import { loadTempestConfiguration } from './config'
import { mockTempestConfiguration } from './test-utils'

test('the configuration can be loaded', async () => {
	const spy = mockTempestConfiguration()

	try {
		const config = await loadTempestConfiguration()

		expect(config).toHaveProperty('build_directory')
		expect(config).toHaveProperty('bridge_file_name')
		expect(config).toHaveProperty('manifest')
		expect(config).toHaveProperty('entrypoints')
	} finally {
		spy.mockRestore()
	}
})

test('the configuration can be overriden', async () => {
	process.env.TEMPEST_PLUGIN_CONFIGURATION_OVERRIDE = JSON.stringify({
		build_directory: 'build',
		bridge_file_name: 'vite-tempest',
		manifest: 'manifest.json',
		entrypoints: [],
	})

	const config = await loadTempestConfiguration()

	expect(config).toHaveProperty('build_directory')
	expect(config).toHaveProperty('bridge_file_name')
	expect(config).toHaveProperty('manifest')
	expect(config).toHaveProperty('entrypoints')
})

test('the configuration can be loaded from a file path environment variable', async () => {
	const __dirname = path.dirname(fileURLToPath(import.meta.url))
	const tempConfigPath = path.join(__dirname, 'tempest-vite.test.json')
	const mockConfig = {
		build_directory: 'build/from-file',
		bridge_file_name: 'test-bridge',
		manifest: 'test-manifest.json',
		entrypoints: ['resources/js/test.js'],
	}

	try {
		fs.writeFileSync(tempConfigPath, JSON.stringify(mockConfig))
		process.env.TEMPEST_PLUGIN_CONFIGURATION_PATH = tempConfigPath

		const config = await loadTempestConfiguration()

		expect(config.build_directory).toBe('build/from-file')
		expect(config.bridge_file_name).toBe('test-bridge')
		expect(config.manifest).toBe('test-manifest.json')
		expect(config.entrypoints).toEqual(['resources/js/test.js'])
	} finally {
		fs.rmSync(tempConfigPath, { force: true })
		delete process.env.TEMPEST_PLUGIN_CONFIGURATION_PATH
	}
})
