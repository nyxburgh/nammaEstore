<?php
namespace App\Core\Providers;

use App\Core\Interfaces\EmailInterface;

class SmtpEmailProvider implements EmailInterface
{
    private string $host;
    private int $port;
    private string $username;
    private string $password;
    private string $fromEmail;
    private string $fromName;

    public function __construct(string $host, int $port, string $username, string $password, string $fromEmail, string $fromName)
    {
        $this->host      = $host;
        $this->port      = $port;
        $this->username  = $username;
        $this->password  = $password;
        $this->fromEmail = $fromEmail;
        $this->fromName  = $fromName;
    }

    public function send(string $to, string $subject, string $htmlBody, array $attachments = []): bool
    {
        // Uses PHPMailer if installed via composer; falls back to mail() otherwise.
        if (class_exists('\PHPMailer\PHPMailer\PHPMailer')) {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                $mail->isSMTP();
                $mail->Host       = $this->host;
                $mail->Port       = $this->port;
                $mail->SMTPAuth   = true;
                $mail->Username   = $this->username;
                $mail->Password   = $this->password;
                $mail->SMTPSecure = $this->port === 465 ? 'ssl' : 'tls';
                $mail->setFrom($this->fromEmail, $this->fromName);
                $mail->addAddress($to);
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $htmlBody;
                foreach ($attachments as $a) {
                    $mail->addAttachment($a['path'], $a['name'] ?? '');
                }
                return $mail->send();
            } catch (\Exception $e) {
                error_log('SMTP send failed: ' . $e->getMessage());
                return false;
            }
        }

        // Fallback (dev environments without composer packages installed)
        $headers = "MIME-Version: 1.0\r\n" .
            "Content-type:text/html;charset=UTF-8\r\n" .
            "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        return mail($to, $subject, $htmlBody, $headers);
    }

    public function sendTemplate(string $to, string $templateId, array $vars = []): bool
    {
        // Plain SMTP has no server-side templates — render locally and send.
        $templateFile = BASE_PATH . '/app/core/mail-templates/' . $templateId . '.php';
        if (!file_exists($templateFile)) {
            error_log("Email template not found: {$templateId}");
            return false;
        }
        extract($vars);
        ob_start();
        include $templateFile;
        $html = ob_get_clean();
        return $this->send($to, $vars['subject'] ?? 'Namma E Store', $html);
    }
}
