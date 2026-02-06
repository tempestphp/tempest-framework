<script setup lang="ts">
import { computed } from 'vue'
import { highlighter } from '../../highlight'
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
	if ($props.formatted) {
		$props.frame.arguments.forEach((argument, index) => {
			if (index > 0) {
				result.push({ html: '<span style="color: var(--code-foreground)">, </span>' })
				result.push({ html: '<br />    ' })
			} else {
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
	}

	// Add spread to indicate that there are arguments
	if (!$props.formatted && $props.frame.arguments.length > 0) {
		result.push({ html: '<span style="color: var(--code-foreground)">...</span>' })
	}

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
			<span v-if="part.argument" class="whitespace-pre">
				<span v-if="formatted" v-text="`    `" />
				<span v-html="part.html" />
			</span>
			<!-- Other part -->
			<span v-else v-html="part.html" />
		</template>
	</span>
</template>
