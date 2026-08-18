<?php

declare(strict_types=1);

namespace Core;

use RuntimeException;

final class EmailService
{
    public function __construct(private readonly ?EmailSettings $settingsStore = null)
    {
    }

    public function send(string $recipient, string $subject, string $message): void
    {
        if (!filter_var($recipient, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('The recipient email address is invalid.');
        }

        $settings = ($this->settingsStore ?? new EmailSettings())->get();
        $transportName = (string) ($settings['active_transport'] ?? '');
        $transport = $settings[$transportName] ?? null;

        if (!in_array($transportName, ['gmail', 'smtp'], true) || !is_array($transport)) {
            throw new RuntimeException('Select a valid active email service.');
        }

        if (empty($transport['enabled'])) {
            throw new RuntimeException(ucfirst($transportName) . ' email service is disabled.');
        }

        $config = $transportName === 'gmail'
            ? [
                'host' => 'smtp.gmail.com',
                'port' => 465,
                'encryption' => 'ssl',
                'username' => (string) ($transport['email'] ?? ''),
                'password' => (string) ($transport['app_password'] ?? ''),
                'from_email' => (string) ($transport['email'] ?? ''),
                'from_name' => (string) ($transport['from_name'] ?? ''),
            ]
            : $transport;

        $this->sendViaSmtp($config, $recipient, trim($subject), $message);
    }

    private function sendViaSmtp(array $config, string $recipient, string $subject, string $message): void
    {
        $host = trim((string) ($config['host'] ?? ''));
        $port = (int) ($config['port'] ?? 0);
        $encryption = (string) ($config['encryption'] ?? 'tls');
        $username = (string) ($config['username'] ?? '');
        $password = (string) ($config['password'] ?? '');
        $fromEmail = trim((string) ($config['from_email'] ?? ''));
        $fromName = trim((string) ($config['from_name'] ?? ''));

        if ($host === '' || $port < 1 || $port > 65535 || !in_array($encryption, ['none', 'tls', 'ssl'], true)) {
            throw new RuntimeException('The active SMTP connection settings are incomplete.');
        }
        if (!filter_var($fromEmail, FILTER_VALIDATE_EMAIL) || $username === '' || $password === '') {
            throw new RuntimeException('The active SMTP sender and login settings are incomplete.');
        }
        if ($subject === '' || strlen($subject) > 200 || trim($message) === '') {
            throw new RuntimeException('A subject and message are required.');
        }

        $remote = ($encryption === 'ssl' ? 'ssl://' : 'tcp://') . $host . ':' . $port;
        $socket = @stream_socket_client($remote, $errorNumber, $errorMessage, 15, STREAM_CLIENT_CONNECT);
        if (!is_resource($socket)) {
            throw new RuntimeException('Could not connect to the email server: ' . $errorMessage);
        }

        stream_set_timeout($socket, 15);

        try {
            $this->expect($socket, [220]);
            $hostname = gethostname() ?: 'localhost';
            $this->command($socket, 'EHLO ' . $hostname, [250]);

            if ($encryption === 'tls') {
                $this->command($socket, 'STARTTLS', [220]);
                if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
                    throw new RuntimeException('Could not establish a secure SMTP connection.');
                }
                $this->command($socket, 'EHLO ' . $hostname, [250]);
            }

            $this->command($socket, 'AUTH LOGIN', [334]);
            $this->command($socket, base64_encode($username), [334]);
            $this->command($socket, base64_encode($password), [235]);
            $this->command($socket, 'MAIL FROM:<' . $fromEmail . '>', [250]);
            $this->command($socket, 'RCPT TO:<' . $recipient . '>', [250, 251]);
            $this->command($socket, 'DATA', [354]);

            $safeSubject = str_replace(["\r", "\n"], '', $subject);
            $safeName = str_replace(["\r", "\n", '"'], '', $fromName);
            $fromHeader = $safeName === '' ? $fromEmail : '"' . $safeName . '" <' . $fromEmail . '>';
            $body = preg_replace('/(?m)^\./', '..', str_replace(["\r\n", "\r"], "\n", $message));
            $headers = [
                'Date: ' . date(DATE_RFC2822),
                'From: ' . $fromHeader,
                'To: ' . $recipient,
                'Subject: =?UTF-8?B?' . base64_encode($safeSubject) . '?=',
                'MIME-Version: 1.0',
                'Content-Type: text/plain; charset=UTF-8',
                'Content-Transfer-Encoding: 8bit',
            ];
            fwrite($socket, implode("\r\n", $headers) . "\r\n\r\n" . str_replace("\n", "\r\n", (string) $body) . "\r\n.\r\n");
            $this->expect($socket, [250]);
            $this->command($socket, 'QUIT', [221]);
        } finally {
            fclose($socket);
        }
    }

    private function command($socket, string $command, array $expectedCodes): void
    {
        if (fwrite($socket, $command . "\r\n") === false) {
            throw new RuntimeException('Could not write to the email server.');
        }
        $this->expect($socket, $expectedCodes);
    }

    private function expect($socket, array $expectedCodes): void
    {
        $response = '';
        $code = 0;

        while (($line = fgets($socket, 2048)) !== false) {
            $response .= $line;
            $code = (int) substr($line, 0, 3);
            if (strlen($line) < 4 || $line[3] !== '-') {
                break;
            }
        }

        if (!in_array($code, $expectedCodes, true)) {
            $detail = trim(preg_replace('/^\d{3}[ -]?/m', '', $response) ?? '');
            throw new RuntimeException('Email server rejected the request' . ($detail === '' ? '.' : ': ' . $detail));
        }
    }
}
