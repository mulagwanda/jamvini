<?php

use App\Core\Hooks\Filter;

Filter::add('shortcode.form', function($output, $attrs) {
    $id = $attrs['id'] ?? null;
    $slug = $attrs['slug'] ?? null;
    
    if ($id) {
        $form = \Plugins\Forms\src\Models\Form::find($id);
    } elseif ($slug) {
        $form = \Plugins\Forms\src\Models\Form::where('slug', $slug)->first();
    } else {
        return '';
    }
    
    if (!$form || !$form->is_active) return '';
    
    return view('plugins.Forms::render', compact('form'))->render();
}, 10, 2);