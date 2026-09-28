<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$first_name = trim($_POST['first_name'] ?? '');
$last_name  = trim($_POST['last_name'] ?? '');
$email      = trim($_POST['email'] ?? '');
$interest   = trim($_POST['interest'] ?? '');
$location   = trim($_POST['location'] ?? '');
$notes      = trim($_POST['notes'] ?? '');

if ($first_name === '' || $last_name === '' || $email === '' || $interest === '' || $location === '') {
    http_response_code(400);
    exit('Please fill in all required fields.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('Invalid email address.');
}

$allowed_interests = [
    'Financial Advisory',
    'Business Management',
    'Human Resource Advisory',
    'IT Advisory',
    'Corporate Strategy Consulting',
];
if (!in_array($interest, $allowed_interests, true)) {
    http_response_code(400);
    exit('Please select a valid area of interest.');
}

if (!in_array($location, ['Onsite', 'Remote'], true)) {
    http_response_code(400);
    exit('Please select onsite or remote.');
}

$full_name = $first_name . ' ' . $last_name;

$to      = 'abdulmalikabubakar273@gmail.com';
$subject = 'New Session Booking - ' . $interest;

$message  = "NEW SESSION BOOKING\n";
$message .= "====================\n\n";
$message .= "Area of Interest: $interest\n";
$message .= "Onsite/Remote:    $location\n";
$message .= "Customer Name:    $full_name\n";
$message .= "Customer Email:   $email\n\n";
$message .= "Additional Info:\n" . ($notes !== '' ? $notes : '—') . "\n";
$message .= "\nSent: " . date('Y-m-d H:i:s') . "\n";
$message .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . "\n";

$headers  = "From: Aliska Website <no-reply@aliskaconsult.com>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

if (mail($to, $subject, $message, $headers)) {
    echo 'success';
} else {
    http_response_code(500);
    echo 'Failed to send email.';
}