import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import type { TempestViteConfiguration } from './types'
import { exec, php } from './utils'

const TEMPEST_BIN = 'tempest'
const VITE_CONFIG_COMMAND = 'vite:config'

export async function loadTempestConfiguration(): Promise<TempestViteConfiguration> {
	const path = process.env.TEMPEST_PLUGIN_CONFIGURATION_PATH
	if (path) {
		try {
			const filePath = resolve(process.cwd(), path)
			const fileContent = readFileSync(filePath, 'utf-8')

			return JSON.parse(fileContent)
		} catch (e) {
			console.error(`[vite-plugin-tempest] Error: Failed to read or parse the file at [${path}].`)

			throw e
		}
	}

	try {
		const override = process.env.TEMPEST_PLUGIN_CONFIGURATION_OVERRIDE
		if (override) {
			return JSON.parse(override)
		}

		const { stdout } = await exec(`${php.value} ${TEMPEST_BIN} ${VITE_CONFIG_COMMAND}`)
		const json = stdout.match(/\{.*\}/s)

		return JSON.parse(json?.[0] as string)
	} catch (e) {
		console.error(`Could not load configuration from [${php.value} ${TEMPEST_BIN} ${VITE_CONFIG_COMMAND}].`)

		if ((e as any).stdout) {
			console.error((e as any).stdout)
		}

		throw e
	}
}
