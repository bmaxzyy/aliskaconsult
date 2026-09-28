<?php
/**
 * Handles submissions from the Book A Session page (book-a-session.html).
 * Expects POST fields: first_name, last_name, email, interest, location, notes, website (honeypot).
 */

declare(strict_types=1);
require __DIR__ . '/config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(false, 'Invalid request method.', 405);
}

reject_if_spam();

$firstName = clean_input($_POST['first_name'] ?? '');
$lastName  = clean_input($_POST['last_name'] ?? '');
$email     = filter_var(trim($_POST['email'] ?? ''), FILTER_SANITIZE_EMAIL);
$interest  = clean_input($_POST['interest'] ?? '');
$location  = clean_input($_POST['location'] ?? '');
$notes     = clean_input($_POST['notes'] ?? '');

$allowedInterests = [
    'Financial Advisory',
    'Business Management',
    'Human Resource Advisory',
    'IT Advisory',
    'Corporate Strategy Consulting',
];
$allowedLocations = ['Onsite', 'Remote'];

$errors = [];
if ($firstName === '' || $lastName === '') {
    $errors[] = 'First and last name are required.';
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'A valid email address is required.';
}
if (!in_array($interest, $allowedInterests, true)) {
    $errors[] = 'Please select a valid area of interest.';
}
if (!in_array($location, $allowedLocations, true)) {
    $errors[] = 'Please select whether you need onsite or remote assistance.';
}

if ($errors) {
    json_response(false, implode(' ', $errors), 422);
}

$emailBody = "New session booking from the " . SITE_NAME . " website\n\n"
    . "Name: {$firstName} {$lastName}\n"
    . "Email: {$email}\n"
    . "Area of interest: {$interest}\n"
    . "Onsite or remote: {$location}\n\n"
    . "Additional info:\n" . ($notes !== '' ? $notes : 'None provided') . "\n";

$headers = [
    'From: ' . SITE_NAME . ' Website <' . FROM_EMAIL . '>',
    'Reply-To: ' . $email,
    'Content-Type: text/plain; charset=UTF-8',
];

$sent = @mail(RECIPIENT_EMAIL, '[Website] New Session Booking — ' . $interest, $emailBody, implode("\r\n", $headers));

if ($sent) {
    json_response(true, 'Thank you, ' . $firstName . ' — your session request has been received. We will confirm shortly by email.');
}

error_log('Aliska booking form: mail() failed for ' . $email);
json_response(false, 'We could not submit your booking right now. Please call us on (+233) 302 907 260 or try again later.', 500);
