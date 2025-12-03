import { readFileSync } from 'node:fs'
import { resolve } from 'node:path'
import type { TempestViteConfiguration } from './types'
import { exec, php } from './utils'

const TEMPEST_BIN = 'tempest'
const VITE_CONFIG_COMMAND = 'vite:config'

export async function loadTempestConfiguration(): Promise<TempestViteConfiguration> {
	return await loadConfigurationFromEnvironmentOverride()
		?? await loadConfigurationFromEnvironmentPath()
		?? await loadConfigurationFromTempestConsole()
}

async function loadConfigurationFromEnvironmentOverride(): Promise<TempestViteConfiguration | undefined> {
	const override = process.env.TEMPEST_PLUGIN_CONFIGURATION_OVERRIDE

	if (!override) {
		return undefined
	}

	try {
		return JSON.parse(override)
	} catch (error) {
		console.error(
			`[vite-plugin-tempest] Could not parse configuration override from TEMPEST_PLUGIN_CONFIGURATION_OVERRIDE.`,
		)
		throw error
	}
}

async function loadConfigurationFromEnvironmentPath(): Promise<TempestViteConfiguration | undefined> {
	const path = process.env.TEMPEST_PLUGIN_CONFIGURATION_PATH

	if (!path) {
		return undefined
	}

	try {
		const filePath = resolve(process.cwd(), path)
		const fileContent = readFileSync(filePath, 'utf-8')

		return JSON.parse(fileContent)
	} catch (error) {
		console.error(`[vite-plugin-tempest] Failed to read or parse the file at [${path}].`)
		throw error
	}
}

async function loadConfigurationFromTempestConsole(): Promise<TempestViteConfiguration> {
	try {
		const { stdout } = await exec(`${php.value} ${TEMPEST_BIN} ${VITE_CONFIG_COMMAND}`)
		const json = stdout.match(/\{.*\}/s)

        if (!json?.[0]) {
            throw new Error('Could not find valid JSON in Tempest console output')
        }

        return JSON.parse(json[0])
	} catch (error) {
		console.error(
			`[vite-plugin-tempest] Could not load configuration from [${php.value} ${TEMPEST_BIN} ${VITE_CONFIG_COMMAND}].`,
		)

		if ((error as any).stdout) {
			console.error((error as any).stdout)
		}

		throw error
	}
}
