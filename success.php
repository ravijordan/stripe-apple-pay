<?php
require_once 'vendor/autoload.php';
require_once 'config.php';

\Stripe\Stripe::setApiKey(STRIPE_SECRET_KEY);

$payment_intent_id = $_GET['payment_intent'] ?? $_GET['payment_intent_id'];

try {
    $paymentIntent = \Stripe\PaymentIntent::retrieve($payment_intent_id);
    
        // Retrieve Customer details
    $customer = \Stripe\Customer::retrieve($paymentIntent->customer);
    
    // Prepare customer data
    $customerName = $customer->name ?? 'Unknown';
    $customerEmail = $customer->email ?? 'No email';
    
    
    // print_r($paymentIntent);
    
    // Save to database
    $stmt = $mysqli->prepare("INSERT INTO payments (
        payment_intent_id, 
        amount, 
        currency, 
        status, 
        customer_name, 
        customer_email
    ) VALUES (?, ?, ?, ?, ?, ?)");
    
    $stmt->bind_param(
        'sissss',
        $paymentIntent->id,
        $paymentIntent->amount,
        $paymentIntent->currency,
        $paymentIntent->status,
        $customerName,
        $customerEmail
    );
    
    $stmt->execute();
    $stmt->close();

    if ($paymentIntent->status === 'succeeded') {
        echo "Payment succeeded!";
        // Additional success logic here
    } else {
        echo "Payment failed!";
    }
} catch (\Stripe\Exception\ApiErrorException $e) {
    // Handle error
    echo "API Error: " . $e->getMessage();
} catch (Exception $e) {
    // Handle general error
    echo "Error: " . $e->getMessage();
}