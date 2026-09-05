<div class="cc-admin-testimonials">

    <div class="cc-admin-testimonials__orb"></div>

    <div class="cc-admin-testimonials__container">

        {{-- Header --}}
        <div class="cc-admin-testimonials__header">

            <div>
                <span class="cc-admin-eyebrow">
                    TESTIMONIALS / MODERATION
                </span>

                <h1>
                    Client <span>love.</span>
                </h1>

                <p>
                    Review testimonials submitted by your clients
                    before they appear publicly on the website.
                </p>
            </div>

            <div class="cc-admin-testimonials__stats">

                <div class="cc-admin-stat">
                    <span>Pending</span>

                    <strong>
                        {{ \App\Models\Testimonial::pending()->count() }}
                    </strong>
                </div>

                <div class="cc-admin-stat">
                    <span>Published</span>

                    <strong>
                        {{ \App\Models\Testimonial::approved()->count() }}
                    </strong>
                </div>

                <div class="cc-admin-stat">
                    <span>Total</span>

                    <strong>
                        {{ \App\Models\Testimonial::count() }}
                    </strong>
                </div>

            </div>

        </div>


        {{-- Flash --}}
        @if (session('testimonial_success'))
            <div class="cc-admin-alert">
                <span>✓</span>

                {{ session('testimonial_success') }}
            </div>
        @endif


        {{-- Toolbar --}}
        <div class="cc-admin-toolbar">

            <div class="cc-admin-filters">

                <button
                    type="button"
                    wire:click="$set('status', 'pending')"
                    class="{{ $status === 'pending' ? 'is-active' : '' }}"
                >
                    Pending
                </button>

                <button
                    type="button"
                    wire:click="$set('status', 'approved')"
                    class="{{ $status === 'approved' ? 'is-active' : '' }}"
                >
                    Approved
                </button>

                <button
                    type="button"
                    wire:click="$set('status', 'denied')"
                    class="{{ $status === 'denied' ? 'is-active' : '' }}"
                >
                    Denied
                </button>

                <button
                    type="button"
                    wire:click="$set('status', 'all')"
                    class="{{ $status === 'all' ? 'is-active' : '' }}"
                >
                    All
                </button>

            </div>

            <div class="cc-admin-search">

                <svg
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="1.7"
                >
                    <circle cx="11" cy="11" r="7"></circle>
                    <path d="m20 20-4-4"></path>
                </svg>

                <input
                    type="search"
                    wire:model.live.debounce.400ms="search"
                    placeholder="Search testimonials..."
                >

            </div>

        </div>


        {{-- Testimonials --}}
        <div class="cc-admin-testimonials__list">

            @forelse ($testimonials as $testimonial)

                <article
                    wire:key="testimonial-{{ $testimonial->id }}"
                    class="cc-admin-testimonial"
                >

                    <div class="cc-admin-testimonial__top">

                        <div class="cc-admin-testimonial__person">

                            <div class="cc-admin-testimonial__avatar">
                                {{ $testimonial->initials }}
                            </div>

                            <div>

                                <h2>
                                    {{ $testimonial->name }}
                                </h2>

                                <p>
                                    {{ $testimonial->display_role ?: 'Client' }}
                                </p>

                                @if ($testimonial->email)
                                    <small>
                                        {{ $testimonial->email }}
                                    </small>
                                @endif

                            </div>

                        </div>


                        <div class="cc-admin-testimonial__status">

                            @if ($testimonial->isPending())
                                <span class="status status--pending">
                                    Pending
                                </span>
                            @elseif ($testimonial->isApproved())
                                <span class="status status--approved">
                                    Approved
                                </span>
                            @else
                                <span class="status status--denied">
                                    Denied
                                </span>
                            @endif

                        </div>

                    </div>


                    <div class="cc-admin-testimonial__rating">
                        @for ($i = 1; $i <= 5; $i++)
                            <span
                                class="{{ $i <= $testimonial->rating ? 'is-active' : '' }}"
                            >
                                ★
                            </span>
                        @endfor
                    </div>


                    <blockquote>
                        “{{ $testimonial->message }}”
                    </blockquote>


                    <div class="cc-admin-testimonial__meta">

                        <span>
                            Submitted
                            {{ $testimonial->created_at->format('M j, Y g:i A') }}
                        </span>

                        @if ($testimonial->company)
                            <span>
                                {{ $testimonial->company }}
                            </span>
                        @endif

                    </div>


                    <div class="cc-admin-testimonial__actions">

                        @if (! $testimonial->isApproved())

                            <button
                                type="button"
                                wire:click="approve({{ $testimonial->id }})"
                                wire:loading.attr="disabled"
                                class="cc-action cc-action--approve"
                            >
                                <span>✓</span>
                                Approve
                            </button>

                        @endif


                        @if (! $testimonial->isDenied())

                            <button
                                type="button"
                                wire:click="deny({{ $testimonial->id }})"
                                wire:loading.attr="disabled"
                                class="cc-action cc-action--deny"
                            >
                                <span>×</span>
                                Deny
                            </button>

                        @endif


                        <button
                            type="button"
                            wire:click="confirmDelete({{ $testimonial->id }})"
                            class="cc-action cc-action--delete"
                        >
                            <span>⌫</span>
                            Delete
                        </button>

                    </div>

                </article>

            @empty

                <div class="cc-admin-empty">

                    <div class="cc-admin-empty__icon">
                        ✓
                    </div>

                    <h3>
                        No testimonials found.
                    </h3>

                    <p>
                        There aren't any testimonials matching
                        your current filters.
                    </p>

                </div>

            @endforelse

        </div>


        {{-- Pagination --}}
        @if ($testimonials->hasPages())
            <div class="cc-admin-pagination">
                {{ $testimonials->links() }}
            </div>
        @endif

    </div>


    {{-- Delete modal --}}
    @if ($showDeleteModal)

        <div
            class="cc-admin-modal-backdrop"
            wire:click="cancelDelete"
        >

            <div
                class="cc-admin-modal"
                wire:click.stop
            >

                <div class="cc-admin-modal__icon">
                    !
                </div>

                <h3>
                    Delete testimonial?
                </h3>

                <p>
                    This permanently removes the testimonial.
                    This action cannot be undone.
                </p>

                <div class="cc-admin-modal__actions">

                    <button
                        type="button"
                        wire:click="cancelDelete"
                        class="cc-modal-btn cc-modal-btn--cancel"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        wire:click="delete"
                        wire:loading.attr="disabled"
                        class="cc-modal-btn cc-modal-btn--delete"
                    >
                        Delete permanently
                    </button>

                </div>

            </div>

        </div>

    @endif

</div>
@push('styles')
<style>

.cc-admin-testimonials {
    --lime: #ADD322;
    --purple: #7c3aed;
    --violet: #6d28d9;

    position: relative;

    min-height: 100vh;

    padding: 80px 0;

    overflow: hidden;

    color: #fff;

    background:
        radial-gradient(
            900px circle at 10% 0%,
            rgba(124,58,237,.18),
            transparent 60%
        ),
        radial-gradient(
            700px circle at 95% 70%,
            rgba(91,33,182,.15),
            transparent 62%
        ),
        linear-gradient(
            135deg,
            #020104,
            #090411 45%,
            #030205
        );
}

.cc-admin-testimonials__orb {
    position: absolute;

    width: 600px;
    height: 600px;

    top: 10%;
    right: -300px;

    border-radius: 50%;

    background:
        radial-gradient(
            circle,
            rgba(124,58,237,.12),
            transparent 70%
        );

    filter: blur(90px);

    pointer-events: none;
}

.cc-admin-testimonials__container {
    position: relative;

    z-index: 1;

    width: min(1250px, calc(100% - 40px));

    margin: 0 auto;
}


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

.cc-admin-testimonials__header {
    display: flex;

    justify-content: space-between;

    align-items: flex-end;

    gap: 40px;

    margin-bottom: 45px;
}

.cc-admin-eyebrow {
    color: var(--lime);

    font-size: 10px;

    font-weight: 700;

    letter-spacing: .16em;
}

.cc-admin-testimonials__header h1 {
    margin: 14px 0;

    font-size: clamp(42px, 5vw, 68px);

    line-height: .95;

    letter-spacing: -.055em;
}

.cc-admin-testimonials__header h1 span {
    color: var(--lime);
}

.cc-admin-testimonials__header p {
    max-width: 540px;

    margin: 0;

    color: rgba(255,255,255,.4);

    font-size: 14px;

    line-height: 1.7;
}


/*
|--------------------------------------------------------------------------
| Stats
|--------------------------------------------------------------------------
*/

.cc-admin-testimonials__stats {
    display: flex;

    gap: 10px;
}

.cc-admin-stat {
    min-width: 105px;

    padding: 17px;

    border: 1px solid rgba(255,255,255,.07);

    border-radius: 14px;

    background:
        linear-gradient(
            145deg,
            rgba(255,255,255,.035),
            rgba(255,255,255,.01)
        );
}

.cc-admin-stat span {
    display: block;

    margin-bottom: 7px;

    color: rgba(255,255,255,.3);

    font-size: 9px;

    text-transform: uppercase;

    letter-spacing: .1em;
}

.cc-admin-stat strong {
    color: #fff;

    font-size: 25px;
}


/*
|--------------------------------------------------------------------------
| Alert
|--------------------------------------------------------------------------
*/

.cc-admin-alert {
    display: flex;

    align-items: center;

    gap: 10px;

    margin-bottom: 25px;

    padding: 13px 16px;

    border: 1px solid rgba(173,211,34,.15);

    border-radius: 12px;

    color: rgba(255,255,255,.7);

    background: rgba(173,211,34,.045);

    font-size: 12px;
}

.cc-admin-alert span {
    color: var(--lime);
}


/*
|--------------------------------------------------------------------------
| Toolbar
|--------------------------------------------------------------------------
*/

.cc-admin-toolbar {
    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 20px;
}

.cc-admin-filters {
    display: flex;

    gap: 5px;

    padding: 5px;

    border: 1px solid rgba(255,255,255,.06);

    border-radius: 12px;

    background: rgba(0,0,0,.25);
}

.cc-admin-filters button {
    padding: 9px 14px;

    border: 0;

    border-radius: 8px;

    color: rgba(255,255,255,.35);

    background: transparent;

    cursor: pointer;

    font-family: inherit;

    font-size: 11px;

    transition: .2s ease;
}

.cc-admin-filters button:hover {
    color: rgba(255,255,255,.7);
}

.cc-admin-filters button.is-active {
    color: #070807;

    background: var(--lime);
}

.cc-admin-search {
    position: relative;

    width: 280px;
}

.cc-admin-search svg {
    position: absolute;

    top: 50%;
    left: 13px;

    width: 15px;
    height: 15px;

    color: rgba(255,255,255,.25);

    transform: translateY(-50%);
}

.cc-admin-search input {
    width: 100%;

    padding: 11px 14px 11px 38px;

    border: 1px solid rgba(255,255,255,.07);

    border-radius: 10px;

    outline: none;

    color: #fff;

    background:
        linear-gradient(
            145deg,
            rgba(30,12,45,.65),
            rgba(4,2,8,.8)
        );

    font-family: inherit;

    font-size: 12px;
}

.cc-admin-search input:focus {
    border-color: rgba(173,211,34,.3);

    box-shadow:
        0 0 0 3px rgba(173,211,34,.035);
}


/*
|--------------------------------------------------------------------------
| Testimonial cards
|--------------------------------------------------------------------------
*/

.cc-admin-testimonials__list {
    display: grid;

    gap: 14px;
}

.cc-admin-testimonial {
    position: relative;

    padding: 26px;

    border: 1px solid rgba(255,255,255,.07);

    border-radius: 18px;

    background:
        radial-gradient(
            600px circle at 100% 0%,
            rgba(124,58,237,.08),
            transparent 65%
        ),
        linear-gradient(
            145deg,
            rgba(22,8,35,.9),
            rgba(5,3,9,.96)
        );

    box-shadow:
        0 20px 60px rgba(0,0,0,.2);

    transition:
        border-color .25s ease,
        transform .25s ease;
}

.cc-admin-testimonial:hover {
    border-color: rgba(124,58,237,.16);

    transform: translateY(-2px);
}

.cc-admin-testimonial__top {
    display: flex;

    align-items: flex-start;

    justify-content: space-between;

    gap: 20px;
}

.cc-admin-testimonial__person {
    display: flex;

    align-items: center;

    gap: 13px;
}

.cc-admin-testimonial__avatar {
    display: grid;

    place-items: center;

    width: 48px;
    height: 48px;

    border: 1px solid rgba(173,211,34,.2);

    border-radius: 50%;

    color: var(--lime);

    background:
        linear-gradient(
            135deg,
            rgba(173,211,34,.08),
            rgba(124,58,237,.12)
        );

    font-size: 11px;

    font-weight: 700;
}

.cc-admin-testimonial__person h2 {
    margin: 0 0 3px;

    color: rgba(255,255,255,.9);

    font-size: 14px;
}

.cc-admin-testimonial__person p {
    margin: 0;

    color: rgba(255,255,255,.4);

    font-size: 11px;
}

.cc-admin-testimonial__person small {
    display: block;

    margin-top: 3px;

    color: rgba(255,255,255,.22);

    font-size: 10px;
}

.status {
    display: inline-flex;

    align-items: center;

    padding: 6px 10px;

    border-radius: 999px;

    font-size: 9px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .08em;
}

.status--pending {
    color: #fbbf24;

    background: rgba(251,191,36,.08);
}

.status--approved {
    color: var(--lime);

    background: rgba(173,211,34,.08);
}

.status--denied {
    color: #f87171;

    background: rgba(248,113,113,.07);
}


/*
|--------------------------------------------------------------------------
| Rating / message
|--------------------------------------------------------------------------
*/

.cc-admin-testimonial__rating {
    display: flex;

    gap: 3px;

    margin: 23px 0 12px;
}

.cc-admin-testimonial__rating span {
    color: rgba(255,255,255,.08);

    font-size: 13px;
}

.cc-admin-testimonial__rating span.is-active {
    color: var(--lime);
}

.cc-admin-testimonial blockquote {
    max-width: 900px;

    margin: 0;

    color: rgba(255,255,255,.72);

    font-size: 16px;

    line-height: 1.7;
}


/*
|--------------------------------------------------------------------------
| Meta
|--------------------------------------------------------------------------
*/

.cc-admin-testimonial__meta {
    display: flex;

    gap: 18px;

    margin-top: 20px;

    color: rgba(255,255,255,.22);

    font-size: 10px;
}


/*
|--------------------------------------------------------------------------
| Actions
|--------------------------------------------------------------------------
*/

.cc-admin-testimonial__actions {
    display: flex;

    justify-content: flex-end;

    gap: 7px;

    margin-top: 22px;

    padding-top: 18px;

    border-top: 1px solid rgba(255,255,255,.05);
}

.cc-action {
    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 9px 13px;

    border: 1px solid rgba(255,255,255,.07);

    border-radius: 9px;

    cursor: pointer;

    font-family: inherit;

    font-size: 10px;

    font-weight: 600;

    transition: .2s ease;
}

.cc-action span {
    font-size: 14px;
}

.cc-action--approve {
    color: var(--lime);

    background: rgba(173,211,34,.04);
}

.cc-action--approve:hover {
    border-color: rgba(173,211,34,.3);

    background: rgba(173,211,34,.09);
}

.cc-action--deny {
    color: #fbbf24;

    background: rgba(251,191,36,.025);
}

.cc-action--deny:hover {
    border-color: rgba(251,191,36,.25);

    background: rgba(251,191,36,.07);
}

.cc-action--delete {
    color: #f87171;

    background: rgba(248,113,113,.025);
}

.cc-action--delete:hover {
    border-color: rgba(248,113,113,.25);

    background: rgba(248,113,113,.07);
}


/*
|--------------------------------------------------------------------------
| Empty
|--------------------------------------------------------------------------
*/

.cc-admin-empty {
    padding: 80px 30px;

    text-align: center;

    border: 1px solid rgba(255,255,255,.07);

    border-radius: 18px;

    background:
        linear-gradient(
            145deg,
            rgba(22,8,35,.8),
            rgba(5,3,9,.9)
        );
}

.cc-admin-empty__icon {
    display: grid;

    place-items: center;

    width: 55px;
    height: 55px;

    margin: 0 auto 18px;

    border: 1px solid rgba(173,211,34,.18);

    border-radius: 50%;

    color: var(--lime);

    background: rgba(173,211,34,.05);
}

.cc-admin-empty h3 {
    margin: 0;

    font-size: 20px;
}

.cc-admin-empty p {
    margin: 10px 0 0;

    color: rgba(255,255,255,.3);

    font-size: 12px;
}


/*
|--------------------------------------------------------------------------
| Delete modal
|--------------------------------------------------------------------------
*/

.cc-admin-modal-backdrop {
    position: fixed;

    inset: 0;

    z-index: 1000;

    display: grid;

    place-items: center;

    padding: 20px;

    background: rgba(0,0,0,.72);

    backdrop-filter: blur(12px);
}

.cc-admin-modal {
    width: min(430px, 100%);

    padding: 30px;

    border: 1px solid rgba(255,255,255,.09);

    border-radius: 20px;

    background:
        radial-gradient(
            400px circle at 100% 0%,
            rgba(124,58,237,.12),
            transparent 65%
        ),
        linear-gradient(
            145deg,
            #180a25,
            #060309
        );

    box-shadow:
        0 40px 120px rgba(0,0,0,.65);
}

.cc-admin-modal__icon {
    display: grid;

    place-items: center;

    width: 44px;
    height: 44px;

    margin-bottom: 20px;

    border: 1px solid rgba(248,113,113,.18);

    border-radius: 50%;

    color: #f87171;

    background: rgba(248,113,113,.06);

    font-weight: 700;
}

.cc-admin-modal h3 {
    margin: 0;

    font-size: 23px;

    letter-spacing: -.03em;
}

.cc-admin-modal p {
    margin: 10px 0 25px;

    color: rgba(255,255,255,.38);

    font-size: 12px;

    line-height: 1.7;
}

.cc-admin-modal__actions {
    display: flex;

    justify-content: flex-end;

    gap: 8px;
}

.cc-modal-btn {
    padding: 10px 15px;

    border-radius: 9px;

    cursor: pointer;

    font-family: inherit;

    font-size: 11px;

    font-weight: 600;
}

.cc-modal-btn--cancel {
    border: 1px solid rgba(255,255,255,.07);

    color: rgba(255,255,255,.5);

    background: transparent;
}

.cc-modal-btn--delete {
    border: 1px solid rgba(248,113,113,.18);

    color: #fff;

    background: rgba(248,113,113,.12);
}

.cc-modal-btn--delete:hover {
    background: rgba(248,113,113,.18);
}


/*
|--------------------------------------------------------------------------
| Responsive
|--------------------------------------------------------------------------
*/

@media (max-width: 850px) {

    .cc-admin-testimonials__header {
        flex-direction: column;

        align-items: flex-start;
    }

    .cc-admin-testimonials__stats {
        width: 100%;
    }

    .cc-admin-stat {
        flex: 1;
    }

}

@media (max-width: 650px) {

    .cc-admin-testimonials {
        padding: 55px 0;
    }

    .cc-admin-testimonials__container {
        width: min(100% - 24px, 1250px);
    }

    .cc-admin-toolbar {
        flex-direction: column;

        align-items: stretch;
    }

    .cc-admin-filters {
        overflow-x: auto;
    }

    .cc-admin-search {
        width: 100%;
    }

    .cc-admin-testimonial {
        padding: 20px;
    }

    .cc-admin-testimonial__top {
        flex-direction: column;
    }

    .cc-admin-testimonial__actions {
        justify-content: stretch;

        flex-wrap: wrap;
    }

    .cc-action {
        flex: 1;

        justify-content: center;
    }

}

</style>
@endpush