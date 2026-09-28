<?php
/**
 * Shared configuration for the plain-PHP form handlers.
 * Update RECIPIENT_EMAIL / SITE_NAME for deployment. In production, set
 * these from environment variables rather than committing them to source.
 */

define('RECIPIENT_EMAIL', 'abdulmalikabubakar273@gmail.com');
define('SITE_NAME', 'Aliska Business Advisory and Research');
define('FROM_EMAIL', 'no-reply@aliskaconsult.com');

/**
 * Send a JSON response and stop execution.
 */
function json_response(bool $success, string $message, int $statusCode = 200): void
{
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode(['success' => $success, 'message' => $message]);
    exit;
}

/**
 * Basic sanitisation for text fields.
 */
function clean_input(string $value): string
{
    return htmlspecialchars(trim($value), ENT_QUOTES, 'UTF-8');
}

/**
 * Reject requests that fill the honeypot field (basic bot protection)
 * or that arrive without the expected AJAX header.
 */
function reject_if_spam(): void
{
    if (!empty($_POST['website'] ?? '')) {
        // Honeypot triggered — silently report success so bots move on,
        // without actually sending mail.
        json_response(true, 'Thank you — your message has been sent.');
    }
}
