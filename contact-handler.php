<?php

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$subject = trim($_POST['subject'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $message === '') {
    http_response_code(400);
    exit('Please fill in all required fields.');
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    exit('Invalid email address.');
}

$to      = 'abdulmalikabubakar273@gmail.com';
$subject_line = 'New Contact Message' . ($subject !== '' ? " - $subject" : '');

// Plain-text fallback (for clients that don't render HTML)
$text_body  = "NEW CONTACT MESSAGE\n\n";
$text_body .= "Customer Name:  $name\n";
$text_body .= "Customer Email: $email\n";
if ($phone !== '') {
    $text_body .= "Customer Phone: $phone\n";
}
if ($subject !== '') {
    $text_body .= "Subject: $subject\n";
}
$text_body .= "\nMessage:\n$message\n";
$text_body .= "\nSent: " . date('Y-m-d H:i:s') . "\n";

// HTML version — inline CSS is required for email clients
$e = function ($s) { return htmlspecialchars($s, ENT_QUOTES, 'UTF-8'); };
$nl2br = function ($s) { return nl2br(htmlspecialchars($s, ENT_QUOTES, 'UTF-8')); };

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
              <p style="margin:0 0 16px;font-size:16px;font-weight:bold;color:#0f2a4a;">New Contact Message</p>
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                <tr>
                  <td style="padding:8px 0;color:#888888;width:130px;vertical-align:top;">Name</td>
                  <td style="padding:8px 0;font-weight:bold;">{$e($name)}</td>
                </tr>
                <tr>
                  <td style="padding:8px 0;color:#888888;vertical-align:top;">Email</td>
                  <td style="padding:8px 0;"><a href="mailto:{$e($email)}" style="color:#0f2a4a;">{$e($email)}</a></td>
                </tr>
HTML;

if ($phone !== '') {
    $html_body .= <<<HTML
                <tr>
                  <td style="padding:8px 0;color:#888888;vertical-align:top;">Phone</td>
                  <td style="padding:8px 0;">{$e($phone)}</td>
                </tr>
HTML;
}
if ($subject !== '') {
    $html_body .= <<<HTML
                <tr>
                  <td style="padding:8px 0;color:#888888;vertical-align:top;">Subject</td>
                  <td style="padding:8px 0;">{$e($subject)}</td>
                </tr>
HTML;
}

$html_body .= <<<HTML
              </table>
              <div style="margin-top:20px;padding:16px;background:#f7f9fc;border-left:4px solid #0f2a4a;border-radius:4px;">
                <p style="margin:0 0 6px;font-size:12px;text-transform:uppercase;letter-spacing:1px;color:#888888;">Message</p>
                <p style="margin:0;color:#333333;">{$nl2br($message)}</p>
              </div>
              <p style="margin:24px 0 0;font-size:12px;color:#aaaaaa;">Sent: {$e(date('Y-m-d H:i:s'))}</p>
            </td>
          </tr>
          <tr>
            <td style="background:#0f2a4a;color:#ffffff;padding:14px 28px;font-size:12px;">
              <span>&copy; {$e(date('Y'))} All rights reserved.</span>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <script>document.getElementById('year').textContent = new Date().getFullYear();</script>
</body>
</html>
HTML;

$boundary = md5(uniqid(time(), true));

$headers  = "From: Aliska Consult <no-reply@aliskaconsult.com>\r\n";
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

if (mail($to, $subject_line, $body, $headers)) {
    echo 'success';
} else {
    http_response_code(500);
    echo 'Failed to send email.';
}