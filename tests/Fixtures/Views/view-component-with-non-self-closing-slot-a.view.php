<x-component name="x-view-component-with-non-self-closing-slot-a">
    A: <x-slot name="other"/>
    B: <x-slot name="other" />
    C: <x-slot name="other"></x-slot>

    A: <x-slot/>
    B: <x-slot />
    C: <x-slot></x-slot>
</x-component>
