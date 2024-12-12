import { expect, test } from 'vitest'
import { php } from './utils'

test('php is memoized', () => {
	process.env.PHP_EXECUTABLE_PATH = 'php8.4'
	expect(php.value).toBe('php8.4')
	process.env.PHP_EXECUTABLE_PATH = 'php'
	expect(php.value).toBe('php8.4')
})
