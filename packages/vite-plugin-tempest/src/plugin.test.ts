import { expect, test } from 'vitest'
import tempest from './plugin'

// TODO: assert against different `loadConfiguration`

test('it returns a Vite plugin', () => {
	const plugin = tempest()

	expect(plugin).not.toBeNull()
})

test('it outputs a config', async () => {
	const plugin = tempest()

	// @ts-expect-error typing
	const config = await plugin.config({}, { command: 'build' })

	expect(config.base).toBe('/build/')
	expect(config.build.manifest).toBe('manifest.json')
	expect(config.build.rollupOptions).toEqual({ input: [] })
})
