<script setup lang="ts">
import { highlight } from '../highlight'

const $props = defineProps<{
	body?: string
}>()
</script>

<template>
	<card title="Request body" icon="tabler:http-post">
		<div v-if="body" class="p-2" v-html="highlight(body, 'json')" />
		<div v-else class="flex justify-center items-center p-8 pt-4 font-mono uppercase">
			<span class="text-muted">No request body</span>
		</div>
	</card>
</template>
