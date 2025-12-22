<script setup lang="ts">
import { computed } from 'vue'
import { highlight, highlighter } from '../../highlight'
import type { Argument, StacktraceFrame } from './stacktrace'

const $props = defineProps<{
	frame: StacktraceFrame
	formatted?: boolean
}>()

interface HighlightedPart {
	html: string
	argument?: Argument
}

const parts = computed<HighlightedPart[]>(() => {
	if (!$props.frame.class) {
		return [{ html: $props.frame.function ?? '' }]
	}

	const result: HighlightedPart[] = []

	// Build the call signature (class, type operator, function name)
	const callSignature = `${$props.frame.class}${$props.frame.type ?? ''}${
		$props.frame.function ?? ''
	}`

	// Get grammar state after the call signature with opening parenthesis
	const grammarState = highlighter.getLastGrammarState(`${callSignature}(`, {
		lang: 'php',
		theme: 'tempest',
	})

	// Highlight the call signature
	const callHtml = highlighter.codeToHtml(callSignature, {
		lang: 'php',
		theme: 'tempest',
	})

	// Extract just the inner HTML (remove wrapper <pre> and <code> tags)
	const callMatch = callHtml.match(/<code[^>]*>(.*?)<\/code>/s)
	result.push({ html: (callMatch?.[1] ?? callSignature) })

	// Add opening parenthesis
	result.push({ html: '<span style="color: var(--code-foreground)">(</span>' })

	// Highlight each argument individually using the grammar state
	$props.frame.arguments.forEach((argument, index) => {
		if (index > 0) {
			result.push({ html: '<span style="color: var(--code-foreground)">, </span>' })
			if ($props.formatted) {
				result.push({ html: '<br />    ' })
			}
		} else if ($props.formatted) {
			result.push({ html: '<br />    ' })
		}

		const argCode = `${argument.name}: ${argument.compact}`
		const argHtml = highlighter.codeToHtml(argCode, {
			lang: 'php',
			theme: 'tempest',
			grammarState,
		})

		// Extract just the inner HTML
		const argMatch = argHtml.match(/<code[^>]*>(.*?)<\/code>/s)
		result.push({
			html: (argMatch?.[1] ?? argCode),
			argument,
		})
	})

	// Add closing parenthesis and semicolon
	if ($props.formatted && $props.frame.arguments.length > 0) {
		result.push({ html: '<br />' })
	}
	result.push({ html: '<span style="color: var(--code-foreground)">);</span>' })

	return result
})
</script>

<template>
	<span class="font-mono">
		<template v-for="(part, index) in parts" :key="index">
			<!-- Argument with no serialized preview -->
			<span v-if="part.argument && !part.argument.json" class="whitespace-pre">
				<span v-if="formatted" v-text="`    `" />
				<span v-html="part.html" />
			</span>
			<!-- Serialized preview -->
			<u-modal v-else-if="part.argument" :scrollable="true" class="overflow-hidden" :close="false">
				<span class="whitespace-pre">
					<span v-if="formatted" v-text="`    `" />
					<span
						v-html="part.html"
						class="decoration-dashed decoration-neutral-400 hover:decoration-neutral-500 dark:decoration-neutral-600 underline underline-offset-4 transition-colors cursor-pointer"
					/>
				</span>
				<template v-slot:body>
					<div
						v-if="part.argument.json"
						v-html="highlight(part.argument.json, 'json')"
						class="overflow-auto text-sm"
					/>
				</template>
			</u-modal>
			<!-- Other part -->
			<span v-else v-html="part.html" />
		</template>
	</span>
</template>
