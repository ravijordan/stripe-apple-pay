<?php
require_once 'vendor/autoload.php';

// IMPORTANT: Replace with your actual Stripe Secret Key
\Stripe\Stripe::setApiKey('sk_test_51RMMxxxxxxxxxxx');

// Handle POST request to create a PaymentIntent
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $input = json_decode(file_get_contents('php://input'), true);

        if (!isset($input['action']) || $input['action'] !== 'create_payment_intent') {
            throw new Exception('Invalid action.');
        }

        $amount = $input['amount'] ?? 1999; // Default to $19.99
        $currency = $input['currency'] ?? 'aed';
        
        $customer = \Stripe\Customer::create([
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ]);

        // Create a PaymentIntent with the order amount and currency
        $paymentIntent = \Stripe\PaymentIntent::create([
            'amount' => $amount,
            'currency' => $currency,
            'customer' => $customer->id,
            'payment_method_types' => ['card'], // 'card' is necessary for Apple Pay
        ]);

        header('Content-Type: application/json');
        echo json_encode([
            'clientSecret' => $paymentIntent->client_secret
        ]);
    } catch (\Stripe\Exception\ApiErrorException $e) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode(['error' => ['message' => $e->getMessage(), 'type' => 'stripe_api_error']]);
    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode(['error' => ['message' => $e->getMessage(), 'type' => 'application_error']]);
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apple Pay Express Checkout</title>
    <script src="https://js.stripe.com/v3/"></script>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&amp;display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #fff;
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            text-align: center;
        }
        .container {
            background: rgba(255, 255, 255, 0.1);
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            max-width: 400px;
            width: 90%;
        }
        h1 {
            font-weight: 600;
            margin-bottom: 10px;
            color: #f0f0f0;
        }
        .product-info {
            margin-bottom: 25px;
        }
        .product-info img {
            max-width: 150px;
            border-radius: 10px;
            margin-bottom: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }
        .product-info h2 {
            font-size: 1.8em;
            margin: 10px 0;
            color: #fff;
        }
        .product-info p {
            font-size: 1.2em;
            color: #e0e0e0;
            margin-bottom: 20px;
        }
        #payment-request-button-container {
            margin-top: 20px;
            height: 48px; /* Default height for PRB */
        }
        /* Stripe's Payment Request Button will be styled via JS, but we can provide a fallback */
        #payment-request-button div {
            border-radius: 8px !important; /* Example to ensure our styles can affect it */
        }
        .status-message {
            margin-top: 20px;
            padding: 10px;
            border-radius: 8px;
            font-weight: 300;
        }
        .error-message {
            background-color: rgba(255, 0, 0, 0.2);
            color: #ffdddd;
            border: 1px solid rgba(255,0,0,0.3);
        }
        .info-message {
            background-color: rgba(0, 123, 255, 0.1);
            color: #cfe2ff;
            border: 1px solid rgba(0,123,255,0.2);
        }
        #apple-pay-not-supported {
            display: none; /* Hidden by default */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Express Checkout</h1>
        <div class="product-info">
            <img src="https://www.controlz.world/_next/image?url=https%3A%2F%2Fd2b8bsyigoug4c.cloudfront.net%2FdealofTheDay%2Fdeal-1747821851027-y699n81c9-iphone9desktop.png&w=640&q=100" alt="Product Image">
            <h2>iPhone 13</h2>
            <p>Price: $19.99</p>
        </div>

        <div id="payment-request-button-container">
            <div id="payment-request-button">
                <!-- Stripe Payment Request Button will be mounted here -->
            </div>
        </div>

        <div id="apple-pay-not-supported" class="status-message info-message">
            Apple Pay is not available on this browser or device.
        </div>
        <div id="payment-status" class="status-message"></div>
    </div>

    <script>
        // IMPORTANT: Replace with your actual Stripe Publishable key
        const stripe = Stripe('pk_test_XXXXXXXXXXXXXXXXXXXXXXX');
        const elements = stripe.elements();

        const purchase = {
            name: 'Super Cool Gadget',
            price: 1999, // amount in cents ($19.99)
            currency: 'usd',
            country: 'US' // Country for PaymentRequest
        };

        const paymentRequest = stripe.paymentRequest({
            country: purchase.country,
            currency: purchase.currency,
            total: {
                label: 'Total for ' + purchase.name,
                amount: purchase.price,
            },
            requestPayerName: true,
            requestPayerEmail: true,
            // To specifically target Apple Pay, we don't need to list 'applePay' in paymentMethodTypes.
            // Stripe's PaymentRequest object handles this. 'card' is the underlying method.
        });

        const prButton = elements.create('paymentRequestButton', {
            paymentRequest: paymentRequest,
            style: {
                paymentRequestButton: {
                    type: 'buy', // Use 'apple-pay' for an Apple Pay branded button
                    theme: 'dark',     // 'dark', 'light', or 'light-outline'
                    height: '48px',    // Recommended minimum height
                },
            }
        });

        const paymentStatusDiv = document.getElementById('payment-status');

        // Check if Apple Pay can be used
        paymentRequest.canMakePayment().then(function(result) {
            const applePayButtonContainer = document.getElementById('payment-request-button-container');
            const applePayNotSupportedMessage = document.getElementById('apple-pay-not-supported');

            if (result && result.applePay) { // Explicitly check for Apple Pay support
                prButton.mount('#payment-request-button');
                applePayButtonContainer.style.display = 'block';
                applePayNotSupportedMessage.style.display = 'none';
            } else {
                // Apple Pay is not available. You might want to offer other payment methods or inform the user.
                applePayButtonContainer.style.display = 'none';
                applePayNotSupportedMessage.style.display = 'block';
                console.log('Apple Pay not available.', result);
            }
        }).catch(function(err) {
            console.error("Error checking canMakePayment:", err);
            document.getElementById('apple-pay-not-supported').textContent = 'Error checking for Apple Pay availability.';
            document.getElementById('apple-pay-not-supported').style.display = 'block';
        });

        paymentRequest.on('paymentmethod', async (ev) => {
            paymentStatusDiv.textContent = 'Processing payment...';
            paymentStatusDiv.className = 'status-message info-message'; // Reset class
            paymentStatusDiv.style.display = 'block';

            try {
                // Create a PaymentIntent on the server
                const response = await fetch('https://example.com/apple-pay/index.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        action: 'create_payment_intent',
                        amount: purchase.price,
                        currency: purchase.currency
                    })
                });

                const {clientSecret, error: backendError} = await response.json();

                if (backendError) {
                    console.error('Backend error:', backendError);
                    paymentStatusDiv.textContent = `Error: ${backendError.message || 'Payment Intent creation failed.'}`;
                    paymentStatusDiv.className = 'status-message error-message';
                    ev.complete('fail');
                    // Optionally redirect or show more detailed error.
                    // window.location.href = `success.php?error=${encodeURIComponent(backendError.message || 'PI_creation_failed')}`;
                    return;
                }

                // Confirm the PaymentIntent with the PaymentMethod ID from the event.
                const {paymentIntent, error: stripeError} = await stripe.confirmCardPayment(
                    clientSecret,
                    {payment_method: ev.paymentMethod.id},
                    {handleActions: false} // Let Stripe Element handle actions if any (e.g. 3DS). For PRB, this is typical.
                );

                if (stripeError) {
                    // Show error to your customer (e.g., insufficient funds)
                    console.error('Stripe confirmation error:', stripeError);
                    paymentStatusDiv.textContent = `Payment failed: ${stripeError.message}`;
                    paymentStatusDiv.className = 'status-message error-message';
                    ev.complete('fail');
                    window.location.href = `https://example.com/apple-pay/success.php?error=${encodeURIComponent(stripeError.message || 'Payment_confirmation_failed')}`;
                } else if (paymentIntent.status === 'succeeded') {
                    // Show a success message to your customer
                    ev.complete('success');
                    window.location.href = `https://example.com/apple-pay/success.php?payment_intent_id=${paymentIntent.id}`;
                } else if (paymentIntent.status === 'requires_action' || paymentIntent.status === 'requires_confirmation') {
                    // This indicates further action is needed (e.g. 3D Secure)
                    // If `handleActions: false`, you might need to explicitly handle actions here.
                    // Or, ensure PaymentIntent is set up for automatic handling.
                    // For Apple Pay, this is less common if setup correctly.
                    console.warn('Payment requires action:', paymentIntent);
                    paymentStatusDiv.textContent = 'Payment requires further action. Please follow prompts.';
                    paymentStatusDiv.className = 'status-message info-message';
                    // Attempt to handle actions automatically
                    const {error: actionError } = await stripe.handleCardAction(clientSecret);
                    if(actionError) {
                        ev.complete('fail');
                        window.location.href = `https://example.com/apple-pay/success.php?error=${encodeURIComponent(actionError.message || 'Payment_action_failed')}`;
                    } else {
                        // Actions handled, customer might be redirected.
                        // If not redirected, confirmation might be needed again. This part can be complex.
                        // For simplicity here, we assume simple success/fail for typical Apple Pay.
                        paymentStatusDiv.textContent = 'Action completed. Please wait...';
                        // Re-query server or assume redirect. For this demo, we might just redirect to cancel.
                        // This scenario needs robust handling in production.
                         ev.complete('fail'); // Or retry confirmation after action
                         window.location.href = `https://example.com/apple-pay/success.php?error=payment_action_required_not_fully_handled&status=${paymentIntent.status}`;
                    }
                } else {
                    console.warn('Unexpected payment intent status:', paymentIntent.status);
                    paymentStatusDiv.textContent = `Payment status: ${paymentIntent.status}. Something went wrong.`;
                    paymentStatusDiv.className = 'status-message error-message';
                    ev.complete('fail');
                    window.location.href = `https://example.com/apple-pay/success.php?error=unexpected_payment_status&status=${paymentIntent.status}`;
                }
            } catch (e) {
                console.error('Exception during payment processing:', e);
                paymentStatusDiv.textContent = `Error: ${e.message || 'An unexpected error occurred.'}`;
                paymentStatusDiv.className = 'status-message error-message';
                if (ev && typeof ev.complete === 'function') {
                     ev.complete('fail');
                }
                window.location.href = `https://example.com/apple-pay/success.php?error=${encodeURIComponent(e.message || 'Exception_occurred')}`;
            }
        });

        paymentRequest.on('cancel', () => {
            // Handle payment cancellation by the user (e.g., closing the payment sheet)
            paymentStatusDiv.textContent = 'Payment cancelled by user.';
            paymentStatusDiv.className = 'status-message info-message';
            // Optionally redirect
            // window.location.href = 'success.php?reason=user_cancelled';
        });

    </script>
</body>
</html>

