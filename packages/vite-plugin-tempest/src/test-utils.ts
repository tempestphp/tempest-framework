import { type MockInstance, vi } from 'vitest'
import * as config from './config'
import type { TempestViteConfiguration } from './types'

export function mockTempestConfiguration(mock: Partial<TempestViteConfiguration> = {}): MockInstance<() => Promise<TempestViteConfiguration>> {
	const spy = vi.spyOn(config, 'loadTempestConfiguration')

	spy.mockResolvedValue({
		build_directory: 'build',
		bridge_file_name: 'vite-tempest',
		manifest: 'manifest.json',
		entrypoints: [],
		...mock,
	})

	return spy
}
