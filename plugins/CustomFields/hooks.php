<?php

use App\Core\Hooks\Action;
use App\Core\Hooks\Filter;
use Plugins\CustomFields\src\Services\CustomFieldService;

Filter::add('client.registration.validation_rules', function (array $rules) {
    return $rules + app(CustomFieldService::class)->validationRules('client', ['registration' => true]);
}, 10, 1);

Filter::add('auth.register.extra_fields', function (string $html) {
    $fields = app(CustomFieldService::class)->fields('client', ['registration' => true]);

    if ($fields->isEmpty()) {
        return $html;
    }

    return $html . view('plugins.CustomFields::partials.fields', [
        'fields' => $fields,
        'values' => collect(),
    ])->render();
}, 10, 1);

Action::add('client.registration.created', function ($client, array $data) {
    app(CustomFieldService::class)->sync($client, 'client', $data['custom_fields'] ?? [], ['registration' => true]);
}, 10, 2);
