<?php

declare(strict_types=1);

namespace App\Controllers;

use Core\ActivityLogger;
use Core\Controller;
use Core\EmailService;
use Core\EmailSettings;
use Core\WhatsAppService;
use RuntimeException;
use Throwable;

final class CommunicationController extends Controller
{
    public function settings(): void
    {
        $this->view('communication/settings', [
            'title' => 'Email Settings',
            'settings' => (new EmailSettings())->get(),
        ]);
    }

    public function updateSettings(): void
    {
        $activeTransport = strtolower(trim((string) ($_POST['active_transport'] ?? '')));
        $gmailEmail = strtolower(trim((string) ($_POST['gmail_email'] ?? '')));
        $gmailFromName = trim((string) ($_POST['gmail_from_name'] ?? ''));
        $gmailPassword = preg_replace('/\s+/', '', (string) ($_POST['gmail_app_password'] ?? '')) ?? '';
        $smtpHost = trim((string) ($_POST['smtp_host'] ?? ''));
        $smtpPort = (int) ($_POST['smtp_port'] ?? 0);
        $smtpEncryption = strtolower(trim((string) ($_POST['smtp_encryption'] ?? '')));
        $smtpUsername = trim((string) ($_POST['smtp_username'] ?? ''));
        $smtpFromEmail = strtolower(trim((string) ($_POST['smtp_from_email'] ?? '')));
        $smtpFromName = trim((string) ($_POST['smtp_from_name'] ?? ''));

        if (!in_array($activeTransport, ['gmail', 'smtp'], true)) {
            flash('error', 'Select Gmail or SMTP as the active email service.');
            $this->redirect('email-settings');
        }
        if (!in_array($smtpEncryption, ['none', 'tls', 'ssl'], true)) {
            flash('error', 'Select a valid SMTP encryption option.');
            $this->redirect('email-settings');
        }
        if ($smtpPort < 1 || $smtpPort > 65535) {
            flash('error', 'SMTP port must be between 1 and 65535.');
            $this->redirect('email-settings');
        }
        if ($gmailEmail !== '' && !filter_var($gmailEmail, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Enter a valid Gmail address.');
            $this->redirect('email-settings');
        }
        if ($smtpFromEmail !== '' && !filter_var($smtpFromEmail, FILTER_VALIDATE_EMAIL)) {
            flash('error', 'Enter a valid SMTP sender email address.');
            $this->redirect('email-settings');
        }

        $store = new EmailSettings();
        $current = $store->get();
        $gmailEnabled = isset($_POST['gmail_enabled']);
        $smtpEnabled = isset($_POST['smtp_enabled']);

        if (($activeTransport === 'gmail' && $gmailEnabled)
            && ($gmailEmail === '' || (($current['gmail']['app_password'] ?? '') === '' && $gmailPassword === ''))
        ) {
            flash('error', 'An email address and app password are required to enable Gmail.');
            $this->redirect('email-settings');
        }
        if (($activeTransport === 'smtp' && $smtpEnabled)
            && ($smtpHost === '' || $smtpUsername === '' || $smtpFromEmail === ''
                || (($current['smtp']['password'] ?? '') === '' && trim((string) ($_POST['smtp_password'] ?? '')) === ''))
        ) {
            flash('error', 'Host, login, password, and sender email are required to enable SMTP.');
            $this->redirect('email-settings');
        }

        $smtpPassword = trim((string) ($_POST['smtp_password'] ?? ''));

        $store->save([
            'active_transport' => $activeTransport,
            'gmail' => [
                'enabled' => $gmailEnabled,
                'email' => $gmailEmail,
                'app_password' => $gmailPassword !== '' ? $gmailPassword : (string) ($current['gmail']['app_password'] ?? ''),
                'from_name' => substr($gmailFromName, 0, 100),
            ],
            'smtp' => [
                'enabled' => $smtpEnabled,
                'host' => substr($smtpHost, 0, 255),
                'port' => $smtpPort,
                'encryption' => $smtpEncryption,
                'username' => substr($smtpUsername, 0, 255),
                'password' => $smtpPassword !== '' ? $smtpPassword : (string) ($current['smtp']['password'] ?? ''),
                'from_email' => $smtpFromEmail,
                'from_name' => substr($smtpFromName, 0, 100),
            ],
        ]);

        ActivityLogger::log('updated email service settings');
        flash('success', 'Email settings updated successfully.');
        $this->redirect('email-settings');
    }

    public function legacyCommunicationPage(): void
    {
        $this->redirect('send-email');
    }

    public function emailPage(): void
    {
        $settings = (new EmailSettings())->get();
        $active = (string) ($settings['active_transport'] ?? 'gmail');

        $this->view('communication/send-email', [
            'title' => 'Send Email',
            'activeService' => ucfirst($active),
            'emailReady' => !empty($settings[$active]['enabled']),
        ]);
    }

    public function smsPage(): void
    {
        $this->view('communication/send-sms', [
            'title' => 'Send SMS',
        ]);
    }

    public function sendEmail(): void
    {
        $recipient = trim((string) ($_POST['recipient'] ?? ''));
        $subject = trim((string) ($_POST['subject'] ?? ''));
        $message = trim((string) ($_POST['message'] ?? ''));

        try {
            (new EmailService())->send($recipient, $subject, $message);
            ActivityLogger::log('sent an email');
            flash('success', 'Email sent successfully.');
        } catch (Throwable $exception) {
            flash('error', $exception instanceof RuntimeException
                ? $exception->getMessage()
                : 'The email could not be sent. Check the active service settings.');
        }

        $this->redirect('send-email');
    }

    public function openWhatsApp(): void
    {
        try {
            $url = (new WhatsAppService())->messageUrl(
                (string) ($_POST['receiver_number'] ?? ''),
                (string) ($_POST['message'] ?? '')
            );
            ActivityLogger::log('opened a WhatsApp message');
            header('Location: ' . $url);
            exit;
        } catch (RuntimeException $exception) {
            flash('error', $exception->getMessage());
            $this->redirect('send-sms');
        }
    }
}
