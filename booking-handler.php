<?php
/**
 * Aliska Booking Form Handler
 * ----------------------------
 * Receives POST data from the booking form, validates it,
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
$first_name = trim(filter_input(INPUT_POST, "first_name", FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? "");
$last_name  = trim(filter_input(INPUT_POST, "last_name",  FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? "");
$email      = trim(filter_input(INPUT_POST, "email",      FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? "");
$interest   = trim(filter_input(INPUT_POST, "interest",   FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? "");
$location   = trim(filter_input(INPUT_POST, "location",   FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? "");
$notes      = trim(filter_input(INPUT_POST, "notes",      FILTER_SANITIZE_FULL_SPECIAL_CHARS) ?? "");

// Honeypot field (optional spam trap — add a hidden input named "website" to your form)
$honeypot = trim($_POST["website"] ?? "");
if (!empty($honeypot)) {
    // Silently pretend success to bots
    echo json_encode(["success" => true, "message" => "Session booked."]);
    exit;
}

// ── Validation ──
$errors = [];

if (empty($first_name) || strlen($first_name) < 2) {
    $errors["first_name"] = "Please enter your first name.";
}

if (empty($last_name) || strlen($last_name) < 2) {
    $errors["last_name"] = "Please enter your last name.";
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors["email"] = "Please enter a valid email address.";
}

$allowed_interests = [
    "Financial Advisory",
    "Business Management",
    "Human Resource Advisory",
    "IT Advisory",
    "Corporate Strategy Consulting",
];
if (empty($interest) || !in_array($interest, $allowed_interests, true)) {
    $errors["interest"] = "Please select an area of interest.";
}

if (empty($location) || !in_array($location, ["Onsite", "Remote"], true)) {
    $errors["location"] = "Please select onsite or remote.";
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

$full_name = $first_name . " " . $last_name;

// ── Build email ──
$email_subject = "[$site_name Booking] New session request — $interest";

$email_body = "You have a new booking request from your website.\n\n";
$email_body .= "Name:            $full_name\n";
$email_body .= "Email:           $email\n";
$email_body .= "Area of interest: $interest\n";
$email_body .= "Onsite/Remote:   $location\n";
$email_body .= "Additional info: " . (!empty($notes) ? $notes : "—") . "\n";
$email_body .= "\n---\nSent: " . date("Y-m-d H:i:s") . "\n";
$email_body .= "IP: " . ($_SERVER["REMOTE_ADDR"] ?? "unknown") . "\n";

// Headers
$headers   = [];
$headers[] = "MIME-Version: 1.0";
$headers[] = "Content-type: text/plain; charset=UTF-8";
$headers[] = "From: $site_name Website <noreply@" . ($_SERVER["HTTP_HOST"] ?? "aliskaconsult.com") . ">";
$headers[] = "Reply-To: $full_name <$email>";
$headers[] = "X-Mailer: PHP/" . phpversion();

// ── Send ──
$sent = mail($recipient_email, $email_subject, $email_body, implode("\r\n", $headers));

if ($sent) {
    echo json_encode([
        "success" => true,
        "message" => "Thank you, $first_name! Your session request has been sent. We'll get back to you soon."
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Sorry, something went wrong on our end. Please try again or email us directly."
    ]);
}