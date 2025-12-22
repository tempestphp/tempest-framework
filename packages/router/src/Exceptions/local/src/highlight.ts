import { createHighlighterCore, type ThemeInput } from 'shiki/core'
import { createJavaScriptRawEngine } from 'shiki/engine/javascript'

const tempest: ThemeInput = {
	name: 'tempest',
	type: 'dark',
	colors: {
		'editor.background': 'var(--code-background)',
		'editor.foreground': 'var(--code-foreground)',
		'editorLineNumber.foreground': 'var(--code-gutter)',
		'editorGutter.background': 'var(--code-background)',
		'editor.selectionBackground': 'var(--code-highlight)',
		'editor.lineHighlightBackground': 'var(--code-highlight)',
	},
	tokenColors: [
		{
			scope: [
				'comment',
				'punctuation.definition.comment',
				'comment.block',
				'comment.line',
			],
			settings: {
				foreground: 'var(--code-comment)',
				fontStyle: 'italic',
			},
		},
		{
			scope: [
				'keyword',
				'storage.type',
				'storage.modifier',
				'keyword.control',
				'keyword.operator',
				'keyword.other',
			],
			settings: {
				foreground: 'var(--code-keyword)',
			},
		},
		{
			scope: [
				'variable.parameter',
				'variable.other',
				'variable.language',
				'entity.name.variable',
			],
			settings: {
				foreground: 'var(--code-variable)',
			},
		},
		{
			scope: [
				'entity.name.type',
				'entity.name.class',
				'support.type',
				'support.class',
			],
			settings: {
				foreground: 'var(--code-type)',
			},
		},
		{
			scope: [
				'entity.name.function',
				'support.function',
				'meta.function-call',
			],
			settings: {
				foreground: 'var(--code-generic)',
			},
		},
		{
			scope: [
				'string',
				'string.quoted',
				'string.template',
				'constant.character',
				'constant.numeric',
				'constant.language',
				'constant.other',
			],
			settings: {
				foreground: 'var(--code-value)',
			},
		},
		{
			scope: [
				'entity.other.attribute-name',
				'support.other.variable',
				'variable.other.property',
			],
			settings: {
				foreground: 'var(--code-property)',
			},
		},
		{
			scope: [
				'punctuation',
				'meta.brace',
				'punctuation.definition.string',
				'punctuation.definition.variable',
				'punctuation.definition.parameters',
				'punctuation.definition.array',
			],
			settings: {
				foreground: 'var(--code-foreground)',
			},
		},
		{
			scope: [
				'markup.bold',
			],
			settings: {
				fontStyle: 'bold',
			},
		},
		{
			scope: [
				'markup.italic',
			],
			settings: {
				fontStyle: 'italic',
			},
		},
		{
			scope: [
				'markup.inserted',
			],
			settings: {
				foreground: 'var(--code-gutter-addition)',
			},
		},
		{
			scope: [
				'markup.deleted',
			],
			settings: {
				foreground: 'var(--code-gutter-deletion)',
			},
		},
	],
}

export const highlighter = await createHighlighterCore({
	themes: [
		tempest,
	],
	langs: [
		import('@shikijs/langs-precompiled/php'),
		import('@shikijs/langs-precompiled/json'),
		import('@shikijs/langs-precompiled/sql'),
	],
	engine: createJavaScriptRawEngine(),
})

export function highlight(code: string, lang: string) {
	return highlighter.codeToHtml(code, { lang, theme: 'tempest' })
}
