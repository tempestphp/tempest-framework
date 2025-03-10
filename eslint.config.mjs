import defineEslintConfig from '@innocenzi/eslint-config'

export default defineEslintConfig({
	toml: {
		overrides: {
			'toml/array-element-newline': 'off',
		},
	},
	ignores: ['.github', 'public', '*.json', '**/composer.json'],
})
