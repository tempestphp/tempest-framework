<x-bench-layout :title="$title">
    <h1>{{ $heading }}</h1>
    <ul>
        <li :foreach="$items as $item">{{ $item }}</li>
    </ul>
</x-bench-layout>
