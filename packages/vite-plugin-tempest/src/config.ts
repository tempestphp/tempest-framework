import type { TempestViteConfiguration } from './types'
import { exec, php } from './utils'

const TEMPEST_BIN = 'tempest'
const VITE_CONFIG_COMMAND = 'vite:config'

export async function loadTempestConfiguration(): Promise<TempestViteConfiguration> {
	try {
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
