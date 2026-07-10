<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('type')->default('email'); // email, sms, whatsapp
            $table->string('subject')->nullable();
            $table->text('body');
            $table->json('variables')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('notification_logs', function (Blueprint $table) {
            $table->id();
            $table->string('template_slug')->nullable();
            $table->string('type'); // email, sms
            $table->string('recipient');
            $table->string('subject')->nullable();
            $table->text('body')->nullable();
            $table->string('status')->default('sent'); // sent, failed, pending
            $table->text('error')->nullable();
            $table->timestamps();
        });

        // Seed default templates
        $templates = [
            [
                'slug' => 'invoice.created',
                'name' => 'Invoice Created',
                'type' => 'email',
                'subject' => 'New Invoice #{invoice_number}',
                'body' => "Dear {client_name},\n\nA new invoice has been generated for your account.\n\nInvoice: #{invoice_number}\nAmount: {total}\nDue Date: {due_date}\n\nYou can view and pay this invoice by logging into your client portal.\n\nThank you,\n{company_name}",
            ],
            [
                'slug' => 'invoice.paid',
                'name' => 'Invoice Paid',
                'type' => 'email',
                'subject' => 'Payment Received — Invoice #{invoice_number}',
                'body' => "Dear {client_name},\n\nThank you for your payment!\n\nInvoice #{invoice_number} for {total} has been marked as paid.\n\nPayment Date: {paid_date}\n\nThank you for your business.\n{company_name}",
            ],
            [
                'slug' => 'invoice.overdue',
                'name' => 'Invoice Overdue',
                'type' => 'email',
                'subject' => 'Overdue Invoice #{invoice_number} — Please Pay',
                'body' => "Dear {client_name},\n\nYour invoice #{invoice_number} for {total} is now overdue.\n\nDue Date was: {due_date}\n\nPlease pay at your earliest convenience to avoid service interruption.\n\n{company_name}",
            ],
            [
                'slug' => 'domain.expiring',
                'name' => 'Domain Expiring Soon',
                'type' => 'email',
                'subject' => 'Domain {domain} Expires in {days} Days',
                'body' => "Dear {client_name},\n\nYour domain {domain} will expire in {days} days on {expiry_date}.\n\nRenew now to avoid downtime:\n{client_portal_url}/domains\n\n{company_name}",
            ],
            [
                'slug' => 'domain.expired',
                'name' => 'Domain Expired',
                'type' => 'email',
                'subject' => 'Domain {domain} Has Expired',
                'body' => "Dear {client_name},\n\nYour domain {domain} expired on {expiry_date}.\n\nYour website may be offline. Renew immediately to restore service.\n\n{company_name}",
            ],
            [
                'slug' => 'service.suspended',
                'name' => 'Service Suspended',
                'type' => 'email',
                'subject' => 'Service Suspended — {service_name}',
                'body' => "Dear {client_name},\n\nYour service '{service_name}' has been suspended due to non-payment.\n\nPlease pay outstanding invoice #{invoice_number} to reactivate your service.\n\n{company_name}",
            ],
            [
                'slug' => 'welcome.client',
                'name' => 'Welcome New Client',
                'type' => 'email',
                'subject' => 'Welcome to {company_name}!',
                'body' => "Dear {client_name},\n\nWelcome to {company_name}! Your account has been created.\n\nYou can access your client portal at:\n{client_portal_url}\n\nEmail: {email}\n\nIf you have any questions, please contact us.\n\n{company_name}",
            ],
            [
                'slug' => 'order.confirmed',
                'name' => 'Order Confirmed',
                'type' => 'email',
                'subject' => 'Order #{order_number} Confirmed',
                'body' => "Dear {client_name},\n\nYour order #{order_number} has been confirmed.\n\nTotal: {total}\n\nYou will receive invoice details shortly.\n\n{company_name}",
            ],
        ];

        foreach ($templates as $template) {
            DB::table('notification_templates')->insert(array_merge($template, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('notification_logs');
        Schema::dropIfExists('notification_templates');
    }
};
