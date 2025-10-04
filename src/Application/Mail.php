<?php

namespace Craft\Application;

/**
 * #### Simple SMTP Mailer
 * 
 * Mail class to send emails using SMTP protocol. 
 * 
 * **Note**: Only supports SMTP with STARTTLS.
 */
class Mail
{
    private $smtp_host = "smtp.gmail.com";
    private $smtp_port = 587;
    private $username;
    private $password;
    private $from;
    private $from_name;
    private $to = [];
    private $subject;
    private $body;

    /**
     * Mail configuration can be provided via environment variables or passed as an associative array.
     * 
     * @param mixed|null $config Configuration array with keys: username, password, fromname
     */
    public function __construct(?array $config = null)
    {
        $this->username   = env('MAIL_USERNAME') ?? $config['username'];
        $this->password   = env('MAIL_PASSWORD') ?? $config['password'];
        $this->from       = env('MAIL_USERNAME') ?? $config['username'];
        $this->from_name  = env('MAIL_FROMNAME') ?? "No-Reply" ?? $config['fromname'];
    }

    /**
     * Add a recipient email address.
     * 
     * @param string $email Recipient email address
     * @return self
     */
    public function to(string $email): self
    {
        $this->to[] = $email;
        return $this;
    }

    /**
     * Set the email subject.
     * 
     * @param string $subject Email subject
     * @return self
     */
    public function subject(string $subject): self
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Set the email body (HTML content).
     * 
     * @param string $body Email body in HTML
     * @return self
     */
    public function body(string $body): self
    {
        $this->body = $body;
        return $this;
    }

    /**
     * Send the email via SMTP.
     * 
     * @return bool True on success
     * @throws \RuntimeException on failure
     */
    public function send(): bool
    {
        // Validate required fields
        $fp = fsockopen($this->smtp_host, $this->smtp_port, $errno, $errstr, 30);
        if (!$fp) {
            throw new \RuntimeException("SMTP connect failed: $errstr ($errno)");
        }

        // Greet server
        $this->getLine($fp);
        $this->sendLine($fp, "EHLO localhost");
        $this->getMultiline($fp);

        // STARTTLS
        $this->sendLine($fp, "STARTTLS");
        $this->getLine($fp);

        if (!stream_socket_enable_crypto($fp, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            throw new \RuntimeException("Failed to enable TLS");
        }

        // Re-EHLO
        $this->sendLine($fp, "EHLO localhost");
        $this->getMultiline($fp);

        // AUTH LOGIN
        $this->sendLine($fp, "AUTH LOGIN");
        $response = $this->getLine($fp);

        $this->sendLine($fp, base64_encode($this->username));
        $response = $this->getLine($fp);

        $this->sendLine($fp, base64_encode($this->password));
        $response = $this->getLine($fp);

        // Check for authentication failure
        if (strpos($response, '535') === 0) {
            throw new \RuntimeException("SMTP Authentication failed: Wrong username or application password.");
        }

        // MAIL FROM
        $this->sendLine($fp, "MAIL FROM:<{$this->from}>");
        $response = $this->getLine($fp);

        if (strpos($response, '550') === 0) {
            throw new \RuntimeException("Sender email address is invalid or does not exist: {$this->from}");
        }

        // RCPT TO
        foreach ($this->to as $rcpt) {
            $this->sendLine($fp, "RCPT TO:<$rcpt>");
            $response = $this->getLine($fp);

            if (strpos($response, '550') === 0) {
                throw new \RuntimeException("Recipient email address is invalid or does not exist: $rcpt");
            }
        }

        // DATA
        $this->sendLine($fp, "DATA");
        $this->getLine($fp);

        // Email headers and body
        $headers  = "From: {$this->from_name} <{$this->from}>\r\n";
        $headers .= "To: " . implode(",", $this->to) . "\r\n";
        $headers .= "Subject: {$this->subject}\r\n";
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n\r\n";

        $message = $headers . $this->body . "\r\n.\r\n";

        fwrite($fp, $message);
        $this->getLine($fp);

        $this->sendLine($fp, "QUIT");
        fclose($fp);

        return true;
    }

    /**
     * Quick send method for one-off emails.
     * 
     * @param string $to Recipient email address
     * @param string $subject Email subject
     * @param string $body Email body in HTML
     * @return bool True on success
     * @throws \InvalidArgumentException if email is invalid
     */
    public static function fast(string $to, string $subject, string $body): bool
    {
        $mail = new self();

        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException("Invalid recipient email: $to");
        }

        return $mail->to($to)
            ->subject($subject)
            ->body($body)
            ->send();
    }

    /**
     * Send a line to the SMTP server.
     * @param mixed $fp
     * @param string $line
     * @return void
     */
    private function sendLine($fp, string $line): void
    {
        fwrite($fp, $line . "\r\n");
    }

    /**
     * Get a line from the SMTP server.
     * @param mixed $fp
     * @throws \RuntimeException
     * @return bool|string
     */
    private function getLine($fp): string
    {
        $line = fgets($fp, 515);
        if ($line === false) {
            throw new \RuntimeException("SMTP read failed");
        }
        return $line;
    }

    /**
     * Read multiline response from the SMTP server.
     * @param mixed $fp
     * @return void
     */
    private function getMultiline($fp): void
    {
        while (($line = fgets($fp, 515)) !== false) {
            if (substr($line, 3, 1) !== '-') break;
        }
    }
}
