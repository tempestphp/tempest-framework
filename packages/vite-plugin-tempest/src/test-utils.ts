import fs from 'node:fs'
import path from 'node:path'
import { fileURLToPath } from 'node:url'
import { afterEach, type MockInstance, vi } from 'vitest'
import * as config from './config'
import type { TempestViteConfiguration } from './types'

const __dirname = path.dirname(fileURLToPath(import.meta.url))
const fixtures = path.join(__dirname, 'fixtures')

afterEach(() => {
	fs.rmSync(fixtures, { force: true, recursive: true })
})

export function mockTempestConfiguration(
	mock: Partial<TempestViteConfiguration> = {},
): MockInstance<() => Promise<TempestViteConfiguration>> {
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

export async function writeFixture(name: string, content: string) {
	const filePath = path.join(fixtures, 'tempest-vite.test.json')

	fs.mkdirSync(fixtures, { recursive: true })
	fs.writeFileSync(filePath, content)

	return filePath
}
