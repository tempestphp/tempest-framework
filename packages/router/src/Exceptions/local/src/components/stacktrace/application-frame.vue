<script setup lang="ts">
import codeSnippet from './code-snippet.vue'
import FileLabel from './file-label.vue'
import type { StacktraceFrame } from './stacktrace'
import SymbolCall from './symbol-call.vue'

const $props = defineProps<{
	frame: StacktraceFrame
}>()
</script>

<template>
	<u-collapsible class="border border-default rounded-md overflow-hidden" default-open>
		<!-- Folder header -->
		<template v-slot="{ open }">
			<div class="flex items-center gap-x-2 bg-accented/60 data-[state=open]:bg-accented/80 hover:bg-accented/80 px-4 py-3 text-dimmed text-sm transition-colors cursor-pointer">
				<!-- Open indicator -->
				<div class="mr-1 shrink-0">
					<div
						class="rounded-full size-2 shrink-0"
						:class="open ? 'bg-(--ui-text-muted)/80' : 'bg-(--ui-text-dimmed)/60'"
					/>
				</div>
				<div class="flex justify-between items-center gap-x-4 grow">
					<!-- Symbol -->
					<symbol-call :frame class="grow" />
					<!-- File -->
					<file-label
						class="min-w-[20%] text-right shrink-0"
						:relative-file="frame.relativeFile"
						:absolute-file="frame.absoluteFile"
						:line="frame.line"
					/>
				</div>
			</div>
		</template>
		<!-- Folder content -->
		<template v-slot:content>
			<div class="bg-muted py-1 border-default border-t">
				<code-snippet v-if="frame.snippet" :snippet="frame.snippet" :file="frame.absoluteFile" />
			</div>
		</template>
	</u-collapsible>
</template>
