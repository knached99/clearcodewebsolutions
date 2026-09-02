import Sortable from 'sortablejs';
import './ticket-editor';

document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', ({ el }) => {
        initTicketBoards(el);
    });

    initTicketBoards();
});

function initTicketBoards(root = document) {
    root.querySelectorAll('[data-ticket-column]').forEach((column) => {

        if (column._sortable) {
            return;
        }

        column._sortable = new Sortable(column, {
            group: 'tickets',
            animation: 180,
            ghostClass: 'opacity-30',
            dragClass: 'rotate-1',
            forceFallback: false,

            onEnd(event) {
                const ticketId = event.item.dataset.ticketId;
                const newStatus = event.to.dataset.ticketColumn;

                if (!ticketId || !newStatus) {
                    return;
                }

                Livewire.find(
                    event.to.closest('[wire\\:id]').getAttribute('wire:id')
                ).call(
                    'moveTicket',
                    ticketId,
                    newStatus
                );
            },
        });
    });
}