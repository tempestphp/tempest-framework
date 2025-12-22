<script setup lang="ts">
import { computed } from 'vue'
import { highlight } from '../highlight'

const $props = defineProps<{
	context?: Record<string, any>
}>()

const json = computed(() => JSON.stringify($props.context ?? '', null, 2))
</script>

<template>
	<card title="Exception context" icon="tabler:info-circle">
		<div
			v-if="Object.values(context ?? {}).length > 0"
			class="p-2 overflow-auto"
			v-html="highlight(json, 'json')"
		/>
		<div v-else class="flex justify-center items-center p-8 pt-4 font-mono uppercase">
			<span class="text-muted">No context</span>
		</div>
	</card>
</template>
