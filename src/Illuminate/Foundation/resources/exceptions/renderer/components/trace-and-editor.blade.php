<x-kenoura-exceptions-renderer::card class="mt-6 overflow-x-auto">
    <div
        x-data="{
            includeVendorFrames: false,
            index: {{ $exception->defaultFrame() }},
        }"
    >
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3" x-clock>
            <x-kenoura-exceptions-renderer::trace :$exception />
            <x-kenoura-exceptions-renderer::editor :$exception />
        </div>
    </div>
</x-kenoura-exceptions-renderer::card>
