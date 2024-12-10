import { expect, test } from 'vitest'
import { loadConfiguration } from './bridge'

test('the configuration can be loaded', async () => {
	const config = await loadConfiguration()

	expect(config).toHaveProperty('build_directory')
	expect(config).toHaveProperty('bridge_file_name')
	expect(config).toHaveProperty('manifest')
	expect(config).toHaveProperty('entrypoints')
})
