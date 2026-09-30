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

$to      = 'abdulmalikabubakar273@gmail.com';
$subject = 'New Quote Request - ' . $product;

// Plain-text fallback (for clients that don't render HTML)
$text_body  = "NEW QUOTE REQUEST\n\n";
$text_body .= "Product: $product\n";
$text_body .= "Customer Name: $name\n";
$text_body .= "Customer Email: $email\n";
$text_body .= "Quantity: $qty MT\n";
$text_body .= "Delivery Terms: $delivery\n\n";
$text_body .= "Notes:\n$notes\n";
$text_body .= "\nSent: " . date('Y-m-d H:i:s') . "\n";

// HTML version — inline CSS is required for email clients
$e = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
$nl2br = function ($s) { return nl2br(htmlspecialchars($s, ENT_QUOTES, 'UTF-8')); };

// Pre-compute interpolated values (PHP cannot call functions inside heredocs)
$notes_html = $notes !== '' ? $nl2br($notes) : '—';
$sent_at = $e(date('Y-m-d H:i:s'));
$product_h = $e($product);
$name_h = $e($name);
$email_h = $e($email);
$qty_h = $e($qty);

$delivery_row = $delivery !== ''
    ? '<tr>
                  <td style="padding:8px 0;color:#888888;vertical-align:top;">Delivery Terms</td>
                  <td style="padding:8px 0;">' . $e($delivery) . '</td>
                </tr>'
    : '';

$html_body = <<<HTML
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="margin:0;padding:0;background-color:#f4f4f4;font-family:Arial,Helvetica,sans-serif;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4;padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;">
          <tr>
            <td style="background:#0f2a4a;color:#ffffff;padding:20px 28px;font-size:18px;font-weight:bold;">
              Aliska Business Advisory and Research
            </td>
          </tr>
          <tr>
            <td style="padding:28px;color:#333333;font-size:14px;line-height:1.6;">
              <p style="margin:0 0 16px;font-size:16px;font-weight:bold;color:#0f2a4a;">New Quote Request</p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                <tr>
                  <td style="padding:8px 0;color:#888888;width:140px;vertical-align:top;">Product</td>
                  <td style="padding:8px 0;">
                    <span style="display:inline-block;background:#0f2a4a;color:#ffffff;padding:4px 12px;border-radius:12px;font-size:12px;font-weight:bold;">{$product_h}</span>
                  </td>
                </tr>
                <tr>
                  <td style="padding:8px 0;color:#888888;vertical-align:top;">Name</td>
                  <td style="padding:8px 0;font-weight:bold;">{$name_h}</td>
                </tr>
                <tr>
                  <td style="padding:8px 0;color:#888888;vertical-align:top;">Email</td>
                  <td style="padding:8px 0;"><a href="mailto:{$email_h}" style="color:#0f2a4a;">{$email_h}</a></td>
                </tr>
                <tr>
                  <td style="padding:8px 0;color:#888888;vertical-align:top;">Quantity</td>
                  <td style="padding:8px 0;">
                    <span style="display:inline-block;background:#fff4e0;color:#b26a00;padding:4px 12px;border-radius:12px;font-size:13px;font-weight:bold;">{$qty_h} MT</span>
                  </td>
                </tr>
                {$delivery_row}
              </table>
              <div style="margin-top:20px;padding:16px;background:#f7f9fc;border-left:4px solid #0f2a4a;border-radius:4px;">
                <p style="margin:0 0 6px;font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#888888;">Notes</p>
                <p style="margin:0;color:#333333;">{$notes_html}</p>
              </div>
              <p style="margin:24px 0 0;font-size:12px;color:#aaaaaa;">Sent: {$sent_at}</p>
            </td>
          </tr>
          <tr>
            <td style="background:#0f2a4a;color:#ffffff;padding:14px 28px;font-size:12px;">
              This is an automated notification from your website quote form.
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
HTML;

$boundary = md5(uniqid(time(), true));

$headers  = "From: Aliska Website <no-reply@aliskaconsult.com>\r\n";
$headers .= "Reply-To: $email\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: multipart/alternative; boundary=\"$boundary\"\r\n";

$body  = "--$boundary\r\n";
$body .= "Content-Type: text/plain; charset=UTF-8\r\n\r\n";
$body .= $text_body . "\r\n";
$body .= "--$boundary\r\n";
$body .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";
$body .= $html_body . "\r\n";
$body .= "--$boundary--";

if (mail($to, $subject, $body, $headers)) {
    echo 'success';
} else {
    http_response_code(500);
    echo 'Failed to send email.';
}