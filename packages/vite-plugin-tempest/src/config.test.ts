import { expect, test } from 'vitest'
import { loadTempestConfiguration } from './config'
import { mockTempestConfiguration } from './test-utils'

test('the configuration can be loaded', async () => {
	mockTempestConfiguration()

	const config = await loadTempestConfiguration()

	expect(config).toHaveProperty('build_directory')
	expect(config).toHaveProperty('bridge_file_name')
	expect(config).toHaveProperty('manifest')
	expect(config).toHaveProperty('entrypoints')
})
