import { reactive } from 'vue'
import type { Stacktrace } from './components/stacktrace/stacktrace'

interface InitializingStore {
	step: 'initializing'
}
interface ReadyStore {
	step: 'ready'
	exception: ExceptionState
}

export interface ExceptionState {
	stacktrace: Stacktrace
	context: Record<string, any>
	rootPath: string
	request: {
		uri: string
		method: string
		headers: Record<string, string>
		body?: string
	}
	response: {
		status: number
	}
	resources: {
		memoryPeakUsage: number
		executionTimeMs: number
	}
	versions: {
		php: string
		tempest: string
	}
}

export const store = reactive<InitializingStore | ReadyStore>({
	step: 'initializing',
})

export function initializeExceptionStore(data: ExceptionState) {
	store.step = 'ready'

	if (store.step === 'ready') {
		console.log(data)
		store.exception = {
			...data,
			stacktrace: JSON.parse(data.stacktrace as any) as unknown as ExceptionState['stacktrace'],
		}
	}
}
