export interface TempestViteConfiguration {
	build_directory: string
	bridge_file_name: string
	manifest: string
	entrypoints: string[]
}

export type DevelopmentServerUrl = `${'http' | 'https'}://${string}:${number}`
