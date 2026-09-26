<x-layouts::app.sidebar :title="$title ?? null">

    <flux:main class="min-h-0 overflow-y-auto">
        {{ $slot }}
    </flux:main>
@push('scripts')
<script>
document.addEventListener('livewire:init', () => {
    console.log('[Dashboard] Livewire initialized');

    Livewire.hook('request', ({
        uri,
        payload,
        respond,
        succeed,
        fail,
    }) => {
        console.log('[Dashboard] Livewire request START', {
            uri,
            payload,
        });

        respond(() => {
            console.log(
                '[Dashboard] Livewire response RECEIVED',
                uri
            );
        });

        succeed(({ status, json }) => {
            console.log(
                '[Dashboard] Livewire request SUCCESS',
                {
                    uri,
                    status,
                    json,
                }
            );
        });

        fail(({ status, content, preventDefault }) => {
            console.error(
                '[Dashboard] Livewire request FAILED',
                {
                    uri,
                    status,
                    content,
                }
            );
        });
    });
});
</script>
@endpush
</x-layouts::app.sidebar>