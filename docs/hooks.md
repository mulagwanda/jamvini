# JamVini Hook Reference

Hooks use `Action::add($hook, $callback, $priority)` for observers and `Filter::add($hook, $callback, $priority)` for value modification.

Priorities run low to high. Returning `false` from an `Action::until()` hook stops the operation.

## Core Hook Families

- `client.created($client)`
- `client.updated($client)`
- `client.deleting($client)`
- `client.deleted($client)`
- `invoice.created($invoice)`
- `invoice.updated($invoice)`
- `invoice.paid($invoice)`
- `invoice.sent($invoice)`
- `invoice.voided($invoice)`
- `order.created($order)`
- `order.accepted($order)`
- `order.rejected($order)`
- `order.completed($order)`
- `domain.created($domain)`
- `domain.updated($domain)`
- `domain.deleted($domain)`
- `domain.expiring($domain, $days)`
- `support.ticket_created($ticket)`
- `support.ticket_replied($ticket)`
- `support.announcement_published($announcement)`
- `payment.initiated($payload)`
- `payment.received($transaction)`
- `payment.refunded($transaction, $amount)`
- `hosting.provisioned($service, $result)`
- `hosting.suspended($service, $result)`
- `dashboard.widgets`

## Filters

- `shortcode.form($output, $attrs)`
- `seo.head_tags($html)`
- `api.response($payload, $request)`
- `provisioning.before($payload)`
- `invoice.before_create($payload)`

## Example

```php
use App\Core\Hooks\Action;

Action::add('invoice.paid', function ($invoice) {
    // provision service, notify staff, or sync external CRM
}, priority: 20);
```
