<div class="cc-projects-page">

    {{-- Hero --}}
    <section class="cc-projects-hero">

        <div class="cc-container">

            <div class="cc-projects-hero__inner">

                <div>

                    <span class="cc-eyebrow">
                        02 / OUR WORK
                    </span>

                    <h1 class="cc-projects-hero__title">
                        Work that makes
                        <span>an impression.</span>
                    </h1>

                    <p class="cc-projects-hero__description">
                        A selection of websites, digital experiences,
                        and solutions we've designed and built.
                    </p>

                </div>

                <div class="cc-projects-hero__action">

                    <a
                        href="{{ url('/#contact') }}"
                        class="cc-text-link"
                    >
                        Start a project
                        <span>↗</span>
                    </a>

                </div>

            </div>

        </div>

    </section>


    {{-- Projects --}}
    <section class="cc-projects-list">

        <div class="cc-container">

            @if ($works->count())

                <div class="cc-projects-count">

                    <span>
                        {{ str_pad($works->count(), 2, '0', STR_PAD_LEFT) }}
                    </span>

                    <span>
                        PROJECTS
                    </span>

                </div>


                <div class="cc-projects-grid">

                    @foreach ($works as $work)

                        <article class="cc-project-card">

                            <a
                                href="{{ route('work.show', $work) }}"
                                class="cc-project-card__link"
                            >

                                <div class="cc-project-card__image">

                                    @if ($work->thumbnail_url)

                                        <img
                                            src="{{ $work->thumbnail_url }}"
                                            alt="{{ $work->title }}"
                                            loading="{{ $loop->first ? 'eager' : 'lazy' }}"
                                        >

                                    @else

                                        <div class="cc-project-card__placeholder">

                                            <span>
                                                {{ $work->title }}
                                            </span>

                                        </div>

                                    @endif

                                    <div class="cc-project-card__overlay"></div>

                                    <span class="cc-project-card__arrow">
                                        ↗
                                    </span>

                                </div>


                                <div class="cc-project-card__meta">

                                    <div>

                                        <span class="cc-project-card__category">
                                            {{ $work->category ?? 'WEB DEVELOPMENT' }}
                                        </span>

                                        <h2>
                                            {{ $work->title }}
                                        </h2>

                                    </div>

                                    <span class="cc-project-card__number">
                                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                                    </span>

                                </div>

                            </a>

                        </article>

                    @endforeach

                </div>

            @else

                <div class="cc-work-empty">

                    <span class="cc-eyebrow">
                        02 / OUR WORK
                    </span>

                    <h2>
                        We're building something
                        worth showing.
                    </h2>

                    <p>
                        Our portfolio is currently being updated.
                        Check back soon to see what we're working on.
                    </p>

                    <a
                        href="{{ url('/#contact') }}"
                        class="cc-text-link"
                    >
                        Start a project
                        <span>↗</span>
                    </a>

                </div>

            @endif

        </div>

    </section>


    {{-- CTA --}}
    <section class="cc-projects-cta">

        <div class="cc-container">

            <div class="cc-projects-cta__inner">

                <span class="cc-eyebrow">
                    HAVE A PROJECT IN MIND?
                </span>

                <h2>
                    Let's build something
                    <span>worth remembering.</span>
                </h2>

                <a
                    href="{{ url('/#contact') }}"
                    class="cc-text-link"
                >
                    Start a project
                    <span>↗</span>
                </a>

            </div>

        </div>

    </section>

</div>
