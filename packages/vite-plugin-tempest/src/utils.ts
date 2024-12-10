import { exec as sync } from 'node:child_process'
import type { AddressInfo } from 'node:net'
import { promisify } from 'node:util'
import { loadEnv } from 'vite'

export const exec = promisify(sync)

export const php = lazy(() => {
	const env = getEnv()

	return (env.PHP_EXECUTABLE_PATH ?? 'php').split(' ')
})

export function isIpv6(address: AddressInfo): boolean {
	// In node >=18.0 <18.4 this was an integer value. This was changed in a minor version.
	// See: https://github.com/laravel/vite-plugin/issues/103
	// @ts-expect-error-next-line
	return address.family === 'IPv6' || address.family === 6
}

function lazy<T>(getter: () => T): { value: T } {
	return {
		get value() {
			const value = getter()
			Object.defineProperty(this, 'value', { value })
			return value
		},
	}
}

function getEnv() {
	return { ...process.env, ...loadEnv('mock', process.cwd(), '') }
}
