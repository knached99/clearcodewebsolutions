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
<script>
    (() => {
        const timezone = Intl.DateTimeFormat().resolvedOptions().timeZone;

        if (!timezone) {
            return;
        }

        const current = document.cookie
            .split('; ')
            .find(row => row.startsWith('client_timezone='))
            ?.split('=')[1];

        if (decodeURIComponent(current ?? '') !== timezone) {
            document.cookie =
                `client_timezone=${encodeURIComponent(timezone)}; path=/; max-age=${60 * 60 * 24 * 365}; SameSite=Lax`;

            window.location.reload();
        }
    })();
</script>

@endpush
</x-layouts::app.sidebar>