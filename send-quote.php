<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$name     = trim($_POST['name'] ?? '');
$email    = trim($_POST['email'] ?? '');
$qty      = trim($_POST['quantity'] ?? '');
$delivery = trim($_POST['delivery'] ?? '');
$notes    = trim($_POST['notes'] ?? '');
$product  = trim($_POST['product'] ?? '');

if ($name === '' || $email === '' || $qty === '' || $product === '') {
    http_response_code(400);
    exit('Please fill in all required fields.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('Invalid email address.');
}

$to = 'abdulmalikabubakar273@gmail.com';
$subject = 'New Quote Request - ' . $product;

$message = "NEW QUOTE REQUEST\n";
$message .= "=================\n\n";
$message .= "Product: $product\n";
$message .= "Customer Name: $name\n";
$message .= "Customer Email: $email\n";
$message .= "Quantity: $qty MT\n";
$message .= "Delivery Terms: $delivery\n\n";
$message .= "Notes:\n$notes\n";

$headers  = "From: Aliska Website <no-reply@aliskaconsult.com>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo 'success';
} else {
    http_response_code(500);
    echo 'Failed to send email.';
}
