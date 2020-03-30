// noinspection JSUnusedGlobalSymbols
function configStripe(pubkey) {
    var stripe = Stripe(pubkey);
    var elements = stripe.elements();

// Set up Stripe.js and Elements to use in checkout form
    var style = {
        base: {
            color: "#32325d",
        },

    };

    var card = elements.create("card", {style: style});
    card.mount("#card-element");

    card.addEventListener('change', function (event) {
        var displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    var form = document.getElementById('payment-form');
    form.addEventListener('submit', function (event) {
        event.preventDefault();

        var btn = document.getElementById('submitter');
        btn.enabled = false;
        btn.innerText = 'Processing...';

        stripe.createToken(card).then(function (result) {
            if (result.error) {
                // Inform the customer that there was an error.
                var errorElement = document.getElementById('card-errors');
                errorElement.textContent = result.error.message;
                btn.enabled = true;
                btn.innerText = 'Submit Payment';
            } else {
                // Send the token to your server.
                stripeTokenHandler(result.token);
            }
        });
    });
}

function stripeTokenHandler(token) {
    // Insert the token ID into the form so it gets submitted to the server
    var form = document.getElementById('payment-form');
    var hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'stripe_token');
    hiddenInput.setAttribute('value', token.id);
    form.appendChild(hiddenInput);
    // Submit the form
    form.submit();
}
