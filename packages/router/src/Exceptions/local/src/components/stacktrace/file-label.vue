<script setup lang="ts">
import { computed } from 'vue'
import { openFileInEditor } from '../../settings/settings'

const $props = defineProps<{
	absoluteFile: string
	relativeFile?: string
	line: number
}>()

const file = computed(() => {
	if (!$props.relativeFile || $props.relativeFile.length > $props.absoluteFile.length) {
		return $props.absoluteFile
	}

	return $props.relativeFile
})
</script>

<template>
	<u-tooltip :text="absoluteFile">
		<code
			class="font-mono text-dimmed decoration-dashed decoration-transparent hover:decoration-neutral-500 underline underline-offset-4 truncate transition-colors cursor-pointer"
			@click.stop="openFileInEditor(absoluteFile, line)"
		>
			<span v-text="file" />
			<span>:</span>
			<span class="text-muted" v-text="line" />
		</code>
	</u-tooltip>
</template>
