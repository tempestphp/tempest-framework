<script setup lang="ts">
import FileLabel from './file-label.vue'
import type { StacktraceFrame } from './stacktrace'
import SymbolCall from './symbol-call.vue'

const $props = defineProps<{
	frames: StacktraceFrame[]
}>()
</script>

<template>
	<u-collapsible
		class="group border border-default not-data-[state=open]:border-accented not-data-[state=open]:border-dashed rounded-md overflow-hidden"
	>
		<!-- Folder header -->
		<div class="flex items-center gap-x-2 data-[state=open]:bg-accented/80 hover:bg-accented/80 px-4 py-3 text-dimmed text-sm transition-colors cursor-pointer">
			<!-- Open indicator -->
			<div class="mr-1 shrink-0">
				<div class="rounded-full size-2 shrink-0 group-data-[state=open]:bg-(--ui-text-muted)/80 bg-(--ui-text-dimmed)/60" />
			</div>
			<div class="flex justify-between items-center font-mono grow">
				<span>{{ frames.length }} vendor frames</span>
			</div>
		</div>
		<!-- Folder content -->
		<template v-slot:content>
			<div class="flex flex-col bg-muted border-default border-t overflow-x-scroll">
				<div
					v-for="frame in frames"
					:key="`vendor_${frame.index}`"
					class="flex flex-col gap-y-5 px-4 py-3 border-b border-b-default"
				>
					<symbol-call :frame :formatted="true" class="text-sm" />
					<div class="flex justify-between items-center">
						<file-label
							:relative-file="frame.relativeFile"
							:absolute-file="frame.absoluteFile"
							:line="frame.line"
							class="text-xs"
						/>
						<span class="font-mono text-xs">
							<span class="text-dimmed">#</span>
							<span class="text-muted" v-text="frame.index" />
						</span>
					</div>
				</div>
			</div>
		</template>
	</u-collapsible>
</template>
