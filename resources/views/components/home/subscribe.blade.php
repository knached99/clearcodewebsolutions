<div class="subscribe">

    <div class="color-layer color-layer-medium-gradient"></div>

    <div class="subscribe__container">

        <div class="container-fluid px-4">

            <div class="row gx-5">

                <div class="col-12 col-lg-5">

                    <div class="subscribe__content">

                        <div class="subscribe__title">

                            <p class="title">
                                Subscribe to our
                                <span>newsletter</span>
                            </p>

                            <p class="subtitle">
                                Stay up to date with Clear Code Web Solutions.
                                Get the latest news, services, and updates.
                            </p>

                        </div>

                        <div class="form-container">

                            {{-- Success --}}
                            <div class="reply-group subscription-ok">

                                <i class="fa-solid fa-circle-check reply-group__icon light"></i>

                                <p class="reply-group__title light">
                                    Done!
                                </p>

                                <span class="reply-group__text light">
                                    Thanks for subscribing!
                                </span>

                            </div>

                            {{-- Error --}}
                            <div class="reply-group subscription-error">

                                <i class="fa-solid fa-face-sad-tear reply-group__icon light"></i>

                                <p class="reply-group__title light">
                                    Oops!
                                </p>

                                <span class="reply-group__text light">
                                    Something went wrong. Please try again later.
                                </span>

                            </div>

                            {{-- Form --}}
                            <form
                                class="subscribe-form form form-light"
                                method="POST"
                                action="#"
                            >

                                @csrf

                                <input
                                    name="email"
                                    type="email"
                                    placeholder="Email Address*"
                                    required
                                >

                                <button
                                    class="btn btn-solid-light"
                                    type="submit"
                                >

                                    <span class="btn-caption">
                                        Subscribe
                                    </span>

                                </button>

                            </form>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>