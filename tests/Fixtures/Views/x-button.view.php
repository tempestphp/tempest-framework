<x-component name="x-button">
    <a class="
        font-bold
        p-4 py-2
        border-2
        border-b-4
        bg-gradient-to-b from-white/10 to-white/0
        rounded-[7px] border-white
        text-center
    " href="<?= $uri ?? '#' ?>">
        <x-slot />
    </a>
</x-component>