<script setup lang="ts">
import { UseClipboard } from '@vueuse/components'
import { type ExceptionState } from '../store'

const $props = defineProps<{
	exception: ExceptionState
	phpVersion: string
	tempestVersion: string
	uri: string
	method: string
	status: number
	executionTime: number
	memoryPeakUsage: number
}>()

function toFileSize(
	bytes: number,
	precision: number = 0,
	useBinaryPrefix: boolean = false,
): string {
	const base = useBinaryPrefix ? 1024 : 1000
	const units = useBinaryPrefix
		? ['B', 'KiB', 'MiB', 'GiB', 'TiB', 'PiB', 'EiB', 'ZiB', 'YiB', 'RiB', 'QiB']
		: ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB', 'RB', 'QB']

	let i = 0
	while ((bytes / base) > 0.9 && i < (units.length - 1)) {
		bytes /= base
		i++
	}

	return `${bytes.toFixed(precision)} ${units[i]}`
}
</script>

<template>
	<div class="flex flex-col gap-y-8">
		<div class="flex justify-between items-center gap-x-6">
			<!-- Class and file -->
			<div class="flex flex-col gap-y-2 min-w-0">
				<!-- Exception class name -->
				<u-tooltip :text="exception.stacktrace.exceptionClass">
					<span
						class="font-semibold text-3xl truncate"
						v-text="exception.stacktrace.exceptionClass"
					/>
				</u-tooltip>
				<!-- File -->
				<div class="flex items-center gap-x-2">
					<file-label
						:relative-file="exception.stacktrace.relativeFile"
						:absolute-file="exception.stacktrace.absoluteFile"
						:line="exception.stacktrace.line"
					/>
				</div>
			</div>
			<!-- Badges -->
			<div class="flex flex-col items-end gap-2 font-mono shrink-0">
				<!-- URL and method -->
				<use-clipboard v-slot="{ copy, copied }" :source="uri">
					<u-tooltip :text="`Click to copy`">
						<u-badge
							:color="copied ? 'success' : 'neutral'"
							size="md"
							:label="uri"
							@click="copy"
							class="cursor-pointer select-none"
						>
							<template v-slot:leading>
								<span class="text-muted" v-text="method" />
							</template>
						</u-badge>
					</u-tooltip>
				</use-clipboard>
				<div class="flex items-center gap-x-2">
					<!-- Status -->
					<u-badge
						size="md"
						:color="
							$props.status < 400
								? 'neutral'
								: ($props.status >= 400 && $props.status < 500 ? 'warning' : 'error')
						"
						:label="$props.status"
					>
						<template v-slot:leading>
							<span class="text-muted">STATUS</span>
						</template>
					</u-badge>
					<!-- Execution time -->
					<u-tooltip
						:text="`The total execution time from kernel boot to response was ${executionTime} milliseconds`"
					>
						<u-badge color="neutral" size="md" :label="executionTime.toFixed() + ' ms'">
							<template v-slot:leading>
								<span class="text-muted">TIME</span>
							</template>
						</u-badge>
					</u-tooltip>
					<!-- Peak memory usage -->
					<u-tooltip :text="`The peak memory usage during execution was ${memoryPeakUsage} bytes`">
						<u-badge color="neutral" size="md" :label="toFileSize(memoryPeakUsage)">
							<template v-slot:leading>
								<span class="text-muted">MEM</span>
							</template>
						</u-badge>
					</u-tooltip>
					<!-- Tempest version -->
					<u-badge color="neutral" size="md" :label="tempestVersion">
						<template v-slot:leading>
							<span class="text-muted">TEMPEST</span>
						</template>
					</u-badge>
					<!-- PHP version -->
					<u-badge color="neutral" size="md" :label="phpVersion">
						<template v-slot:leading>
							<span class="text-muted">PHP</span>
						</template>
					</u-badge>
				</div>
			</div>
		</div>
		<!-- Error -->
		<span
			v-if="exception.stacktrace.message"
			class="font-light text-xl whitespace-pre-line"
			v-text="exception.stacktrace.message"
		/>
	</div>
</template>
