<script setup lang="ts">
import { UseClipboard } from '@vueuse/components'

const $props = defineProps<{
	headers: Record<string, string>
}>()
</script>

<template>
	<card title="Request headers" icon="tabler:http-head">
		<ul class="flex flex-col gap-y-2.5 font-mono">
			<li
				v-for="(headerValue, headerName) in headers"
				:key="headerName"
				class="flex items-baseline gap-x-2"
			>
				<!-- Header name -->
				<use-clipboard v-slot="{ copy, copied }" :source="headerName">
					<u-tooltip :text="headerName">
						<span
							class="truncate transition-colors cursor-pointer"
							:class="['text-muted uppercase', copied && 'text-success']"
							v-text="headerName"
							@click="copy()"
						/>
					</u-tooltip>
				</use-clipboard>
				<!-- Separator -->
				<div class="border-muted border-b-2 border-dotted min-w-[15%] grow" />
				<!-- Header value -->
				<use-clipboard v-slot="{ copy, copied }" :source="headerValue">
					<u-tooltip :text="headerValue">
						<span
							class="truncate transition-colors cursor-pointer"
							:class="copied ? 'text-success' : undefined"
							v-text="headerValue"
							@click="copy()"
						/>
					</u-tooltip>
				</use-clipboard>
			</li>
		</ul>
	</card>
</template>
