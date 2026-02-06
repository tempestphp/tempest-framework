<script setup lang="ts">
import { computed } from 'vue'
import { highlight } from '../../highlight'
import { openFileInEditor } from '../../settings/settings'
import type { CodeSnippet } from './stacktrace'

const $props = defineProps<{
	snippet: CodeSnippet
	file: string
}>()

const lines = computed(() => {
	return Object.entries($props.snippet.lines).map(([lineNum, code]) => ({
		number: Number(lineNum),
		code,
		highlighted: highlight(code, 'php'),
		isHighlighted: Number(lineNum) === $props.snippet.highlightedLine,
	}))
})
</script>

<template>
	<div class="flex flex-col min-w-0 overflow-x-auto font-mono">
		<ul class="flex flex-col w-max min-w-full">
			<li
				v-for="line in lines"
				:key="line.number"
				:class="
					[
						'flex transition-colors items-center text-sm py-1 group cursor-pointer hover:bg-accented! grow w-full',
						line.isHighlighted && 'bg-error-400/15',
						!line.isHighlighted && 'even:bg-elevated dark:even:bg-accented/20',
					]
				"
				@click="openFileInEditor(file, line.number)"
			>
				<div class="px-3 w-12 text-dimmed text-right select-none shrink-0" v-text="line.number" />
				<div class="flex-1 pr-3 min-w-0">
					<div v-html="line.highlighted" />
				</div>
				<div class="opacity-0 group-hover:opacity-100 transition-all group-hover:-translate-x-3">
					<u-icon name="tabler:code" class="text-dimmed" />
				</div>
			</li>
		</ul>
	</div>
</template>
