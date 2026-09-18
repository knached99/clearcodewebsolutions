function setupStripeCheckout() {
    const paymentForm = document.getElementById('payment-form');
    
    if (!paymentForm || paymentForm.dataset.initialized === 'true') return;
    paymentForm.dataset.initialized = 'true';

    const stripeKey = paymentForm.dataset.key;
    const clientSecret = paymentForm.dataset.secret;

    if (!stripeKey || !clientSecret) return;

    const stripe = Stripe(stripeKey);
    const elements = stripe.elements();

    const style = {
        base: {
            color: '#ffffff',
            fontFamily: '"Instrument Sans", ui-sans-serif, system-ui, sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '18px',
            '::placeholder': { color: '#64748b' }
        },
        invalid: {
            color: '#f87171',
            iconColor: '#f87171'
        }
    };

    const cardNumber = elements.create('cardNumber', { style: style, showIcon: false });
    const cardExpiry = elements.create('cardExpiry', { style: style });
    const cardCvc = elements.create('cardCvc', { style: style });

    cardNumber.mount('#cardNumber-element');
    cardExpiry.mount('#cardExpiry-element');
    cardCvc.mount('#cardCvc-element');

    // DOM Elements
    const visualCard = document.getElementById('visual-card');
    const displayNum = document.getElementById('display-number');
    const displayName = document.getElementById('display-name');
    const displayExp = document.getElementById('display-expiry');
    const displayCvv = document.getElementById('display-cvv');
    const brandLogo = document.getElementById('card-brand-logo');
    const nameInput = document.getElementById('card-name');
    
    const errDiv = document.getElementById('card-errors');
    const submitBtn = document.getElementById('submit-button');
    const btnText = document.getElementById('button-text');
    const btnSpinner = document.getElementById('button-spinner');

    // Reset spinner and ensure button is enabled initially
    if (btnSpinner) btnSpinner.style.display = 'none';
    if (submitBtn) submitBtn.disabled = false;

    // Brand icon mapper
    const brandLogos = {
        visa: '<i class="fa-brands fa-cc-visa text-blue-400"></i>',
        mastercard: '<i class="fa-brands fa-cc-mastercard text-orange-400"></i>',
        amex: '<i class="fa-brands fa-cc-amex text-blue-300"></i>',
        discover: '<i class="fa-brands fa-cc-discover text-orange-500"></i>',
        jcb: '<i class="fa-brands fa-cc-jcb text-green-400"></i>',
        diners: '<i class="fa-brands fa-cc-diners-club text-slate-300"></i>',
        unknown: '<i class="fa-brands fa-cc-stripe text-white/90"></i>'
    };

    // Live Name Sync
    if (nameInput && displayName) {
        nameInput.addEventListener('input', (e) => {
            displayName.textContent = e.target.value.trim().toUpperCase() || 'YOUR NAME';
        });
    }

    // Card Number Listener (Brand Detection & Completion)
    cardNumber.on('change', (event) => {
        if (event.error) {
            errDiv.textContent = event.error.message;
            errDiv.classList.remove('hidden');
        } else {
            errDiv.classList.add('hidden');
        }

        // Update Card Brand Icon dynamically
        if (event.brand && brandLogos[event.brand]) {
            brandLogo.innerHTML = brandLogos[event.brand];
        } else {
            brandLogo.innerHTML = brandLogos.unknown;
        }

        // Mask update on progress
        if (event.complete) {
            displayNum.textContent = '•••• •••• •••• ••••';
            displayNum.classList.add('text-indigo-200');
        } else {
            displayNum.textContent = '•••• •••• •••• ••••';
            displayNum.classList.remove('text-indigo-200');
        }
    });

    // Expiry Listener
    cardExpiry.on('change', (event) => {
        if (event.complete) {
            displayExp.textContent = 'VALID';
            displayExp.classList.add('text-emerald-400');
        } else {
            displayExp.textContent = 'MM/YY';
            displayExp.classList.remove('text-emerald-400');
        }
    });

    // CVC Focus & Flip Listener
    cardCvc.on('change', (event) => {
        if (event.complete) {
            displayCvv.textContent = '•••';
        }
    });

    cardCvc.on('focus', () => {
        if (visualCard) visualCard.classList.add('flipped');
    });

    cardCvc.on('blur', () => {
        if (visualCard) visualCard.classList.remove('flipped');
    });

    // Form Submit Handler
    paymentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        // Show Spinner & Disable Button during submission
        btnText.textContent = 'Processing Payment...';
        btnSpinner.style.display = 'inline-block';
        submitBtn.disabled = true;
        submitBtn.classList.add('opacity-75', 'cursor-not-allowed');

        const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardNumber,
                billing_details: {
                    name: nameInput ? nameInput.value : '',
                }
            }
        });

        if (error) {
            // Re-enable button and hide spinner on error
            errDiv.textContent = error.message;
            errDiv.classList.remove('hidden');
            btnText.textContent = 'Pay Now';
            btnSpinner.style.display = 'none';
            submitBtn.disabled = false;
            submitBtn.classList.remove('opacity-75', 'cursor-not-allowed');
        } else if (paymentIntent && paymentIntent.status === 'succeeded') {
            window.location.reload();
        }
    });
}

document.addEventListener('DOMContentLoaded', setupStripeCheckout);
document.addEventListener('livewire:navigated', setupStripeCheckout);
