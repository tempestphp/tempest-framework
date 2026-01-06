import { useLocalStorage } from '@vueuse/core'
import { type } from 'arktype'
import Dialog from './dialog.vue'
import { useOverlay, useToast } from '@nuxt/ui/composables';

const overlay = useOverlay()

export const settingsDialog = overlay.create(Dialog)

export const schema = type({
	editor: type('"vscode" | "phpstorm" | "zed" | "custom" | undefined').optional(),
	openEditorTemplate: type('string | undefined').optional(),
})

export type Editor = typeof schema.infer['editor']
export type Settings = typeof schema.inferIn

export const settings = useLocalStorage<Settings>('tempest:exceptions:settings', {})

export async function openFileInEditor(file: string, line?: number) {
	if (!settings.value.editor) {
		await settingsDialog.open()
	}

	if (!settings.value.editor) {
		const toast = useToast()
		toast.add({
			title: 'No editor configured in settings',
			color: 'warning',
			progress: false,
			icon: 'tabler:exclamation-circle',
		})

		return
	}

	const filePath = encodeURIComponent(file)
	const lineNumber = line ?? 1
	const template = {
		vscode: 'vscode://file/{file}:{line}',
		phpstorm: 'phpstorm://open?file={file}&line={line}',
		zed: 'zed://file/{file}:{line}',
		custom: settings.value.openEditorTemplate ?? '',
	}

	const url = template[settings.value.editor]
		?.replace('{file}', filePath)
		?.replace('{line}', lineNumber.toString())

	window.open(url, '_self')
}
