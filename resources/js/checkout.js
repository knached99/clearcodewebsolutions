document.addEventListener('DOMContentLoaded', function () {
    const paymentForm = document.getElementById('payment-form');
    
    if (!paymentForm) return;

    const stripeKey = paymentForm.dataset.key;
    const clientSecret = paymentForm.dataset.secret;
    const stripe = Stripe(stripeKey);
    const elements = stripe.elements();

    const style = {
        base: {
            color: '#374151',
            fontFamily: '"Instrument Sans", sans-serif',
            fontSmoothing: 'antialiased',
            fontSize: '16px',
            '::placeholder': { color: '#9ca3af' }
        },
        invalid: {
            color: '#ef4444',
            iconColor: '#ef4444'
        }
    };

    // Create split elements
    const cardNumber = elements.create('cardNumber', { style: style, showIcon: true });
    const cardExpiry = elements.create('cardExpiry', { style: style });
    const cardCvc = elements.create('cardCvc', { style: style });

    cardNumber.mount('#cardNumber-element');
    cardExpiry.mount('#cardExpiry-element');
    cardCvc.mount('#cardCvc-element');

    // DOM Elements for Visual Card
    const visualCard = document.getElementById('visual-card');
    const displayNum = document.getElementById('display-number');
    const displayName = document.getElementById('display-name');
    const displayExp = document.getElementById('display-expiry');
    const displayCvv = document.getElementById('display-cvv');
    const nameInput = document.getElementById('card-name');
    
    const errDiv = document.getElementById('card-errors');
    const submitBtn = document.getElementById('submit-button');
    const btnText = document.getElementById('button-text');
    const btnSpinner = document.getElementById('button-spinner');

    // Name syncing
    nameInput.addEventListener('input', (e) => {
        displayName.textContent = e.target.value.toUpperCase() || 'YOUR NAME';
    });

    // Real-time syncing from Stripe Elements to Visual Card
    cardNumber.on('change', (event) => {
        if (event.error) {
            errDiv.textContent = event.error.message;
            errDiv.classList.remove('hidden');
        } else {
            errDiv.classList.add('hidden');
        }
        
        // Unfortunately, Stripe doesn't expose the full typed card number for security.
        // We can only get the brand and last4. We'll simulate typing by listening, but 
        // fallback to displaying just last 4 if full string isn't available.
        // Actually, Stripe doesn't provide the raw string at all on 'change', only empty status or complete status + brand + last4.
        // To make it look interactive, we can just show dots until complete, then show last4.
        if (event.complete) {
            displayNum.textContent = `•••• •••• •••• ${event.value ? event.value.last4 : '••••'}`;
        } else if (event.empty) {
            displayNum.textContent = '•••• •••• •••• ••••';
        }
    });

    cardExpiry.on('change', (event) => {
        if (event.complete) {
            // Once complete, we can't extract the exact MM/YY string easily from event,
            // we'll just indicate it's valid.
            displayExp.textContent = '**/**';
        } else if (event.empty) {
            displayExp.textContent = 'MM/YY';
        }
    });

    cardCvc.on('change', (event) => {
        if (event.complete) displayCvv.textContent = '***';
        else if (event.empty) displayCvv.textContent = '•••';
    });

    // FLIP ANIMATION ON CVC FOCUS
    cardCvc.on('focus', () => visualCard.classList.add('flipped'));
    cardCvc.on('blur', () => visualCard.classList.remove('flipped'));

    // Handle form submission
    paymentForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        btnText.textContent = 'Processing...';
        btnSpinner.classList.remove('hidden');
        submitBtn.disabled = true;

        // PaymentIntent or SetupIntent (Subscriptions create invoices that generate payment intents)
        const { error, paymentIntent } = await stripe.confirmCardPayment(clientSecret, {
            payment_method: {
                card: cardNumber,
                billing_details: {
                    name: nameInput.value,
                }
            }
        });

        if (error) {
            errDiv.textContent = error.message;
            errDiv.classList.remove('hidden');
            btnText.textContent = 'Pay';
            btnSpinner.classList.add('hidden');
            submitBtn.disabled = false;
        } else {
            // Success
            window.location.reload(); // Reloading will hit the 'Paid' state in Livewire
        }
    });
});
