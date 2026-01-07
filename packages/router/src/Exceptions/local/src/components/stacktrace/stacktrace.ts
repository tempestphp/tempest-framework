import { highlight } from '../../highlight'

export interface CodeSnippet {
	lines: Record<number, string>
	highlightedLine: number
}

export interface Argument {
	name: string | number
	compact: string
}

export interface StacktraceFrame {
	line: number
	class?: string
	function?: string
	type: '::' | '->'
	isVendor: boolean
	snippet?: CodeSnippet
	absoluteFile: string
	relativeFile: string
	arguments: Argument[]
	index: number
}

export interface Stacktrace {
	message?: string
	exceptionClass: string
	frames: StacktraceFrame[]
	line: number
	absoluteFile: string
	relativeFile: string
}

interface SymbolOptions {
	formatted?: boolean
	highlighted?: boolean
}

export function getSymbolCall(frame: StacktraceFrame, options: SymbolOptions = {}): string {
	if (!frame.class) {
		return frame.function ?? ''
	}

	const args = frame.arguments.map((argument) => `${argument.name}: ${argument.compact}`)
	const formattedArgs = args.length > 0
		? (options?.formatted ? `(\n    ${args.join(',\n    ')}\n);` : `(${args.join(',')});`)
		: '();'

	const symbol = `${frame.class}${frame.type ?? ''}${frame.function ?? ''}${formattedArgs}`

	return options.highlighted
		? highlight(symbol, 'php')
		: symbol
}
