<script setup lang="ts">
import { useFavicon, useTitle } from '@vueuse/core'
import { onMounted, useTemplateRef } from 'vue'
import noise from './assets/noise.svg?url'
import logo from './assets/tempest-logo.svg'
import Context from './sections/context.vue'
import Headers from './sections/headers.vue'
import RequestBody from './sections/request-body.vue'
import Stacktrace from './sections/stacktrace.vue'
import Summary from './sections/summary.vue'
import { settingsDialog } from './settings/settings'
import { store } from './store'

const background = useTemplateRef('background')

useFavicon(logo)
useTitle(() => store.step === 'initializing' ? 'Exception' : store.exception.stacktrace.message)
onMounted(() => background.value!.style.backgroundImage = `url("${noise}")`)
</script>

<template>
	<u-app>
		<div ref="background" class="z-[-1] absolute inset-0 bg-repeat pointer-events-none" />
		<u-container>
			<main v-if="store.step === 'ready'" class="my-32">
				<Summary
					:tempest-version="store.exception.versions.tempest"
					:php-version="store.exception.versions.php"
					:exception="store.exception"
					:uri="store.exception.request.uri"
					:method="store.exception.request.method"
					:status="store.exception.response.status"
					:execution-time="store.exception.resources.executionTimeMs"
					:memory-peak-usage="store.exception.resources.memoryPeakUsage"
					class="mb-4"
				/>
				<Stacktrace :exception="store.exception" class="mt-12" />
				<Context :context="store.exception.context" class="mt-12" />
				<RequestBody :body="store.exception.request.body" class="mt-12" />
				<Headers
					:headers="store.exception.request.headers"
					:exception="store.exception"
					class="mt-12"
				/>
				<u-footer class="mt-12">
					<u-button
						icon="tabler:adjustments"
						variant="secondary"
						class="text-dimmed hover:text-highlighted"
						@click="settingsDialog.open()"
					/>
				</u-footer>
			</main>
		</u-container>
	</u-app>
</template>
