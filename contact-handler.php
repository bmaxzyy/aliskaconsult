<?php
/**
 * Aliska Contact Form Handler
 * ---------------------------
 * Receives POST data from the contact form, validates it,
 * sends an email, and returns a JSON response.
 */

// ── Config ──
$recipient_email = "abdulmalikabubakar273@gmail.com";
$site_name       = "Aliska Business Advisory and Research";

// Only allow POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
    exit;
}

header("Content-Type: application/json");

// ── Collect & sanitize input ──
$name    = trim(filter_input(INPUT_POST, "name",    FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? "");
$email   = trim(filter_input(INPUT_POST, "email",   FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? "");
$phone   = trim(filter_input(INPUT_POST, "phone",   FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? "");
$subject = trim(filter_input(INPUT_POST, "subject", FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? "");
$message = trim(filter_input(INPUT_POST, "message", FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? "");

// Honeypot field (optional spam trap — add a hidden input named "website" to your form)
$honeypot = trim($_POST["website"] ?? "");
if (!empty($honeypot)) {
    // Silently pretend success to bots
    echo json_encode(["success" => true, "message" => "Message sent."]);
    exit;
}

// ── Validation ──
$errors = [];

if (empty($name) || strlen($name) < 2) {
    $errors["name"] = "Please enter your name.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors["email"] = "Please enter a valid email address.";
}

if (empty($message) || strlen($message) < 10) {
    $errors["message"] = "Your message should be at least 10 characters.";
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode([
        "success" => false,
        "message" => "Please fix the errors below.",
        "errors"  => $errors
    ]);
    exit;
}

// ── Build email ──
// Fall back to a generic subject if none was provided (subject is optional)
$email_subject = "[$site_name Contact] " . (!empty($subject) ? $subject : "General inquiry");

$email_body = "You have a new message from your website contact form.\n\n";
$email_body .= "Name:    $name\n";
$email_body .= "Email:   $email\n";
if (!empty($phone)) {
    $email_body .= "Phone:   $phone\n";
}
if (!empty($subject)) {
    $email_body .= "Subject: $subject\n";
}
$email_body .= "\nMessage:\n$message\n";
$email_body .= "\n---\nSent: " . date("Y-m-d H:i:s") . "\n";
$email_body .= "IP: " . ($_SERVER["REMOTE_ADDR"] ?? "unknown") . "\n";

// Headers
$headers   = [];
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-type: text/plain; charset=UTF-8";
$headers[] = "From: $site_name Website <noreply@" . ($_SERVER["HTTP_HOST"] ?? "aliskaconsult.com") . ">";
$headers[] = "Reply-To: $name <$email>";
$headers[] = "X-Mailer: PHP/" . phpversion();

// ── Send ──
$sent = mail($recipient_email, $email_subject, $email_body, implode("\r\n", $headers));

if ($sent) {
    echo json_encode([
        "success" => true,
        "message" => "Thank you, $name! Your message has been sent. We'll get back to you soon."
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Sorry, something went wrong on our end. Please try again or email us directly."
    ]);
}