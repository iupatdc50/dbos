/*
 * Setup elements in fomr that processes credit card, uses Stripe to verify card and return
 * token to server for further processing.
 *
 * The following coding conventions apply in forms using these functions:
 *      <form> must have id="payment-form"
 *      Submit button must have id=submitter"
 *      <div> where the Stripe CC elements are rendered must have id="card-element"
 *      <div> where the Stripe CC error messages are displayed must have id="card-errors"
 */
// noinspection JSUnusedGlobalSymbols
function configStripe(pubkey) {
    const stripe = Stripe(pubkey);
    const elements = stripe.elements();

    const style = {
        base: {
            color: "#32325d",
        },

    };

    let card = elements.create("card", {style: style});
    card.mount("#card-element");

    card.addEventListener('change', function (event) {
        let displayError = document.getElementById('card-errors');
        if (event.error) {
            displayError.textContent = event.error.message;
        } else {
            displayError.textContent = '';
        }
    });

    let form = document.getElementById('payment-form');
    form.addEventListener('submit',  tokenizer = function(event) {
        event.preventDefault();

        let btn = document.getElementById('submitter');
        btn.enabled = false;
        btn.innerText = 'Processing...';

        stripe.createToken(card).then(function (result) {
            if (result.error) {
                // Inform the customer that there was an error.
                let errorElement = document.getElementById('card-errors');
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
    let form = document.getElementById('payment-form');
    let hiddenInput = document.createElement('input');
    hiddenInput.setAttribute('type', 'hidden');
    hiddenInput.setAttribute('name', 'stripe_token');
    hiddenInput.setAttribute('value', token.id);
    form.appendChild(hiddenInput);
    // Submit the form
    form.submit();
}

function formValidation() {
    $("#payment-form").validate({
        errorClass: "help-block",
        errorElement: "p",
        highlight: function ( element ) {
            $( element ).parents( ".form-group" ).addClass( "has-error" ).removeClass( "has-success" );
        },
        unhighlight: function (element) {
            $( element ).parents( ".form-group" ).addClass( "has-success" ).removeClass( "has-error" );
        }
    });
}