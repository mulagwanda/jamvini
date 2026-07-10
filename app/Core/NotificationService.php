<?php

namespace App\Core;

use App\Models\Setting;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\DB;

class NotificationService
{
    protected string $companyName;
    protected string $companyEmail;
    protected string $clientPortalUrl;

    public function __construct()
    {
        $this->companyName = Setting::get('company_name', 'JamVini Hosting');
        $this->companyEmail = Setting::get('company_email', 'info@jamvini.local');
        $this->clientPortalUrl = url('/client/dashboard');
    }

    /**
     * Send a notification by template slug.
     */
    public function send(string $slug, string $recipient, array $data = [], string $channel = 'email'): bool
    {
        $template = DB::table('notification_templates')
            ->where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            ActivityLogger::log('notification', 'Notification', null, "Template '{$slug}' not found");
            return false;
        }

        // Merge default variables
        $data = array_merge([
            'company_name' => $this->companyName,
            'company_email' => $this->companyEmail,
            'client_portal_url' => $this->clientPortalUrl,
        ], $data);

        $subject = $this->replaceVariables($template->subject, $data);
        $body = $this->replaceVariables($template->body, $data);

        try {
            if ($channel === 'email') {
                $this->sendEmail($recipient, $subject, $body);
            }

            // Log the notification
            DB::table('notification_logs')->insert([
                'template_slug' => $slug,
                'type' => $channel,
                'recipient' => $recipient,
                'subject' => $subject,
                'body' => $body,
                'status' => 'sent',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            ActivityLogger::log('notification', 'Notification', null, "Sent '{$slug}' to {$recipient}");
            return true;
        } catch (\Exception $e) {
            DB::table('notification_logs')->insert([
                'template_slug' => $slug,
                'type' => $channel,
                'recipient' => $recipient,
                'subject' => $subject,
                'body' => $body,
                'status' => 'failed',
                'error' => $e->getMessage(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            ActivityLogger::log('notification', 'Notification', null, "Failed '{$slug}' to {$recipient}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email using Laravel Mail.
     */
    protected function sendEmail(string $recipient, string $subject, string $body): void
    {
        Mail::raw($body, function ($message) use ($recipient, $subject) {
            $message->to($recipient)
                ->subject($subject)
                ->from($this->companyEmail, $this->companyName);
        });
    }

    /**
     * Replace variables in template.
     */
    protected function replaceVariables(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            $text = str_replace('{' . $key . '}', $value ?? '', $text);
        }
        return $text;
    }

    /**
     * Get recent notification logs.
     */
    public static function logs(int $limit = 20): array
    {
        return DB::table('notification_logs')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}