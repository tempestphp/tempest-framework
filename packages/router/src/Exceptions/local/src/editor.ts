// TODO: make configurable, store locally
const configuredEditor: 'vscode' | 'phpstorm' | 'zed' = 'vscode'

export function openFileInEditor(file: string, line?: number) {
	const filePath = encodeURIComponent(file)
	const lineNumber = line ?? 1

	switch (configuredEditor) {
		case 'vscode':
			window.open(`vscode://file/${filePath}:${lineNumber}`, '_self')
			break
		case 'phpstorm':
			window.open(`phpstorm://open?file=${filePath}&line=${lineNumber}`, '_self')
			break
		case 'zed':
			window.open(`zed://file/${filePath}:${lineNumber}`, '_self')
			break
	}
}
