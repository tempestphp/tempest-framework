<script setup lang="ts">
import { computed } from 'vue'
import Card from '../components/card.vue'
import ApplicationFrame from '../components/stacktrace/application-frame.vue'
import VendorFrames from '../components/stacktrace/vendor-frames.vue'
import { type ExceptionState } from '../store'

const $props = defineProps<{
	exception: ExceptionState
}>()

const groups = computed(() => {
	const frames = $props.exception.stacktrace.frames
	const result: Array<{ type: 'vendor' | 'application'; frames: typeof frames }> = []

	frames.forEach((frame) => {
		const isVendor = frame.isVendor ?? true
		const type = isVendor ? 'vendor' : 'application'
		const lastGroup = result[result.length - 1]

		if (lastGroup && lastGroup.type === type) {
			lastGroup.frames.push(frame)
		} else {
			result.push({ type, frames: [frame] })
		}
	})

	return result
})
</script>

<template>
	<card title="Stacktrace" icon="tabler:stack-2">
		<div class="flex flex-col gap-y-2">
			<template
				v-for="({ type, frames }, groupIndex) in groups"
				:key="`group_${groupIndex}_${type}`"
			>
				<template v-if="type === 'application'">
					<ApplicationFrame v-for="frame in frames" :key="`app_${frame.index}`" :frame />
				</template>
				<VendorFrames v-if="type === 'vendor'" :frames />
			</template>
		</div>
	</card>
</template>
