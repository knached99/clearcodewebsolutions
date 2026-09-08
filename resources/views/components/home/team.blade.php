<section id="team" class="clearcode-team">

    <div class="clearcode-team__container">

        {{-- Header --}}
        <div class="clearcode-team__header">

            <div class="clearcode-team__eyebrow">
                <span>04</span>
                The people behind the code
            </div>

            <div class="clearcode-team__heading">

                <h2>
                    Small team.
                    <em>Big ideas.</em>
                </h2>

                <p>
                    We bring together strategy, design and development
                    to turn ambitious ideas into digital experiences
                    that actually perform.
                </p>

            </div>

        </div>


        {{-- Team --}}
        <div class="clearcode-team__grid">


            {{-- Person 1 --}}
            <article class="clearcode-team__member">
                <div class="clearcode-team__flip-card" x-data="{ flipped: false }" @click="flipped = !flipped">
                    <div class="clearcode-team__flip-inner" :class="flipped ? 'is-flipped' : ''">
                        <div class="clearcode-team__flip-front">
                            <div class="clearcode-team__image">
                                <img src="{{ asset('assets/img/team/khaled.jpg') }}" alt="Khaled">
                                <div class="clearcode-team__image-overlay"></div>
                                <span class="clearcode-team__number">
                                    01
                                </span>
                            </div>
                            <div class="clearcode-team__info">
                                <div>
                                    <h3>Khaled</h3>
                                    <p>
                                        Founder &amp; Lead Developer
                                    </p>
                                </div>
                                <span class="clearcode-team__arrow">
                                    →
                                </span>
                            </div>
                        </div>
                        <div class="clearcode-team__flip-back">
                            <div class="clearcode-team__back-content">
                                <h3>Khaled</h3>
                                <span class="back-role">Founder & Lead Developer</span>
                                <p>With over a decade of experience architecting enterprise-grade applications, Khaled
                                    leads the technical vision at ClearCode. A master of modern PHP, Laravel, and
                                    JavaScript ecosystems, he ensures every project is built for scale, speed, and
                                    bulletproof security.</p>
                                <div class="back-skills">
                                    <span class="back-skill">Web Development</span>
                                    <span class="back-skill">App Development</span>
                                    <span class="back-skill">Frontend Development</span>
                                    <span class="back-skill">Backend Development</span>
                                    <span class="back-skill">Cloud Infrastructure</span>
                                    <span class="back-skill">System Architecture</span>
                                </div>
                            </div>
                            <span class="clearcode-team__back-hint">tap to flip back</span>
                        </div>
                    </div>
                </div>
            </article>


            {{-- Person 2 --}}
            <article class="clearcode-team__member">
                <div class="clearcode-team__flip-card" x-data="{ flipped: false }" @click="flipped = !flipped">
                    <div class="clearcode-team__flip-inner" :class="flipped ? 'is-flipped' : ''">
                        <div class="clearcode-team__flip-front">
                            <div class="clearcode-team__image">
                                <img src="{{ asset('assets/img/team/karam.jpg') }}" alt="Karam">
                                <div class="clearcode-team__image-overlay"></div>
                                <span class="clearcode-team__number">
                                    02
                                </span>
                            </div>
                            <div class="clearcode-team__info">
                                <div>
                                    <h3>Karam</h3>
                                    <p>
                                        Cofounder &amp; Marketing Lead
                                    </p>
                                </div>
                                <span class="clearcode-team__arrow">
                                    →
                                </span>
                            </div>
                        </div>
                        <div class="clearcode-team__flip-back">
                            <div class="clearcode-team__back-content">
                                <h3>Karam</h3>
                                <span class="back-role">Cofounder & Marketing Lead</span>
                                <p>Karam brings razor-sharp analytical thinking and creative marketing strategy to every
                                    project. His proven track record in digital growth, conversion optimization, and
                                    brand positioning ensures our clients don't just get a beautiful website — they get
                                    a powerful business engine.</p>
                                <div class="back-skills">
                                    <span class="back-skill">Growth Strategy</span>
                                    <span class="back-skill">SEO & SEM</span>
                                    <span class="back-skill">Brand Strategy</span>
                                    <span class="back-skill">Analytics</span>
                                    <span class="back-skill">Conversion Optimization</span>
                                </div>
                            </div>
                            <span class="clearcode-team__back-hint">tap to flip back</span>
                        </div>
                    </div>
                </div>
            </article>

        </div>


        {{-- Bottom statement --}}
        <div class="clearcode-team__footer">

            <span class="clearcode-team__footer-label">
                Our philosophy
            </span>

            <p>
                Great work happens when
                <strong>good people</strong>
                build together.
            </p>

        </div>

    </div>

</section>
