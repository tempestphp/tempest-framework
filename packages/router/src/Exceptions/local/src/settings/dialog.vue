<script setup lang="ts">
import type { FormSubmitEvent } from '@nuxt/ui'
import { reactive, useTemplateRef } from 'vue'
import { schema, type Settings, settings } from './settings'

const $emit = defineEmits<{
	close: []
}>()

const form = useTemplateRef('form')

const state = reactive<Settings>({
	editor: settings.value.editor,
	openEditorTemplate: settings.value.openEditorTemplate,
})

async function submit(event: FormSubmitEvent<Settings>) {
	settings.value.editor = event.data.editor
	settings.value.openEditorTemplate = event.data.openEditorTemplate

	$emit('close')
}
</script>

<template>
	<u-modal :close="false" :ui="{ footer: 'justify-end' }">
		<template v-slot:body>
			<u-form
				:state
				:schema
				@submit="submit"
				ref="form"
				class="flex flex-col gap-6"
				:ui="{ footer: 'justify-end' }"
			>
				<!-- Editor -->
				<u-form-field
					label="Editor"
					description="Select your preferred code editor for opening files."
					name="editor"
				>
					<u-select
						:items="
							[
								{ label: 'Code', value: 'vscode', icon: 'simple-icons:visualstudiocode' },
								{ label: 'Zed', value: 'zed', icon: 'simple-icons:zedindustries' },
								{
									label: 'PhpStorm',
									value: 'phpstorm',
									icon: 'simple-icons:phpstorm',
								},
								{ label: 'Custom', value: 'custom', icon: 'tabler:settings' },
							]
						"
						placeholder="No editor selected"
						icon="tabler:code"
						v-model="state.editor"
						class="mt-1 w-full"
						required
					/>
				</u-form-field>
				<!-- Editor template -->
				<u-form-field
					label="Editor command"
					description="The command used to open files in your selected editor. Use {file} as a placeholder for the file path, and {line} for the line number."
					name="openEditorTemplate"
					v-if="state.editor === 'custom'"
				>
					<u-input
						v-model="state.openEditorTemplate"
						required
						class="w-full font-mono"
						placeholder="zed://file/{file}:{line}"
					/>
				</u-form-field>
				<!-- Theme -->
				<u-form-field label="Color mode">
					<u-color-mode-select class="w-full" />
				</u-form-field>
			</u-form>
		</template>
		<template v-slot:footer="{ close }">
			<u-button label="Cancel" variant="secondary" @click="close" />
			<u-button type="submit" label="Save" @click="form?.submit()" />
		</template>
	</u-modal>
</template>
