<?php

namespace App\Core;

use App\Core\Hooks\Action;
use App\Models\Setting;
use Plugins\Invoices\src\Models\Invoice;
use Plugins\Domains\src\Models\Domain;

class RegisterCoreTasks
{
    public static function register(): void
    {
        // 1. Check overdue invoices — every 5 minutes
        CronManager::register('invoices.check_overdue', 'everyFiveMinutes', function () {
            $count = 0;
            $overdueInvoices = Invoice::where('status', 'sent')
                ->where('due_date', '<', now())
                ->get();

            foreach ($overdueInvoices as $invoice) {
                $invoice->update(['status' => 'overdue']);
                $count++;
                
                ActivityLogger::log('cron', 'Invoice', $invoice->id,
                    "Invoice #{$invoice->invoice_number} marked as overdue");
            }

            return "{$count} invoices marked as overdue";
        }, ['description' => 'Mark overdue invoices']);

        // 2. Domain expiry checks — daily
        CronManager::register('domains.check_expiry', 'daily', function () {
            $notified = 0;
            
            // Domains expiring in 60, 30, 14, 7, 3, 1 days
            $checkDays = [60, 30, 14, 7, 3, 1];
            
            foreach ($checkDays as $days) {
                $expiringDomains = Domain::where('status', 'active')
                    ->whereDate('expiry_date', now()->addDays($days))
                    ->get();

                foreach ($expiringDomains as $domain) {
                    // Fire hook for notification plugins
                    Action::do('domain.expiring', $domain, $days);
                    $notified++;
                }
            }

            return "{$notified} domain expiry notifications sent";
        }, ['description' => 'Send domain expiry reminders']);

        // 3. Auto-generate renewal invoices — daily
        CronManager::register('invoices.generate_renewals', 'daily', function () {
            $generated = 0;
            $taxRate = jv_tax_rate();
            $invoicePrefix = trim(Setting::get('invoice_prefix', 'INV')) ?: 'INV';
            $invoiceDueDays = (int) Setting::get('invoice_due_days', '14');
            
            $services = \Plugins\Services\src\Models\ClientService::where('status', 'active')
                ->where('billing_cycle', '!=', 'one-time')
                ->whereDate('next_due_date', '<=', now()->addDays(14))
                ->whereDate('next_due_date', '>=', now())
                ->get();

            foreach ($services as $service) {
                // Check if renewal invoice already exists
                $existingInvoice = Invoice::where('client_id', $service->client_id)
                    ->whereHas('items', function ($q) use ($service) {
                        $q->where('service_id', $service->service_id);
                    })
                    ->where('created_at', '>=', now()->subDays(30))
                    ->exists();

                if ($existingInvoice) continue;

                // Generate renewal invoice
                $taxAmount = $service->price * ($taxRate / 100);
                $invoiceNumber = $invoicePrefix . '-' . date('Y') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);
                
                $invoice = Invoice::create([
                    'client_id' => $service->client_id,
                    'invoice_number' => $invoiceNumber,
                    'subtotal' => $service->price,
                    'tax_amount' => $taxAmount,
                    'total' => $service->price + $taxAmount,
                    'status' => 'sent',
                    'due_date' => now()->addDays($invoiceDueDays),
                    'notes' => "Auto-generated renewal invoice for {$service->service->name}",
                ]);

                $invoice->items()->create([
                    'service_id' => $service->service_id,
                    'description' => $service->service->name . ' — Renewal',
                    'quantity' => 1,
                    'unit_price' => $service->price,
                    'tax_rate' => $taxRate,
                    'total' => $service->price + $taxAmount,
                ]);

                Action::do('invoice.created', $invoice);
                $generated++;
            }

            return "{$generated} renewal invoices generated";
        }, ['description' => 'Auto-generate renewal invoices for upcoming services']);

        // 4. Clean old activity logs — weekly
        CronManager::register('system.clean_logs', 'weekly', function () {
            $deleted = \DB::table('activity_logs')
                ->where('created_at', '<', now()->subDays(90))
                ->delete();

            return "{$deleted} old activity logs cleaned";
        }, ['description' => 'Clean old activity logs (90+ days)']);
    }
}
