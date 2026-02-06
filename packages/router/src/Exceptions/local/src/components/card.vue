<script setup lang="ts">
defineProps<{
	icon: string
	title: string
}>()
</script>

<template>
	<div class="bg-elevated border border-default rounded-md">
		<div class="flex items-center gap-x-2.5 p-3 pb-0.5">
			<div class="bg-accented p-1 border border-accented rounded-sm">
				<u-icon :name="icon" class="size-3.5" />
			</div>
			<span class="font-mono font-semibold text-highlighted text-sm uppercase" v-text="title" />
		</div>
		<div class="p-3">
			<slot />
		</div>
	</div>
</template>
