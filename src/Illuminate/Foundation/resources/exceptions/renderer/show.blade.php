<x-kenoura-exceptions-renderer::layout :$exception>
    <div class="renderer container mx-auto lg:px-8">
        <x-kenoura-exceptions-renderer::navigation :$exception />

        <main class="px-6 pb-12 pt-6">
            <div class="container mx-auto">
                <x-kenoura-exceptions-renderer::header :$exception />

                <x-kenoura-exceptions-renderer::trace-and-editor :$exception />

                <x-kenoura-exceptions-renderer::context :$exception />
            </div>
        </main>
    </div>
</x-kenoura-exceptions-renderer::layout>
