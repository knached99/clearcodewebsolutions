    <div class="cc-container">

        <div class="cc-section-intro cc-section-intro--work">
            <div>
                <span class="cc-eyebrow">
                    02 / SELECTED WORK
                </span>

                <h2>
                    Work that makes
                    <span>an impression.</span>
                </h2>
            </div>

            <a href="#contact" class="cc-text-link">
                Start a project
                <span>↗</span>
            </a>
        </div>

        <div class="cc-work-grid">

            @forelse ($works as $work)

    <article class="cc-project {{ $loop->first ? 'cc-project--large' : '' }}">

        <a href="{{ route('work.show', $work) }}">

            <div class="cc-project__image">

                @if ($work->thumbnail_url)
                    <img
                        src="{{ $work->thumbnail_url }}"
                        alt="{{ $work->title }}"
                        loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                    >
                @endif

            </div>

            <div class="cc-project__overlay"></div>

            <div class="cc-project__content">

                <div>
                    <span class="cc-project__category">
                        {{ $work->category ?? 'WEB DEVELOPMENT' }}
                    </span>

                    <h3>
                        {{ $work->title }}
                    </h3>
                </div>

                <span class="cc-project__arrow">↗</span>

            </div>

        </a>

    </article>

@empty

    <div class="cc-work-empty">
        <span class="cc-eyebrow">
            02 / SELECTED WORK
        </span>

        <h3 class="text-white font-bold">
            We're building something worth showing.
        </h3>

        <p class="text-lg text-white">
            Our portfolio is currently being updated.
            Check back soon to see what we're working on.
        </p>

        <a href="#contact" class="cc-text-link">
            Start a project
            <span>↗</span>
        </a>
    </div>

@endforelse

        </div>

        @if ($hasMore)
            <div class="cc-work-more">
                <a href="{{ route('projects.index') }}" class="cc-text-link">
                    View all projects
                    <span>↗</span>
                </a>
            </div>
        @endif

    </div>
