<?php

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/theme/pulse/settings', fn () => view('themes.pulse::admin.settings'))->name('theme.pulse.settings');
    Route::post('/theme/pulse/settings', function (Request $request) {
        $validated = $request->validate([
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'accent_color' => 'required|string|max:20',
            'layout' => 'required|in:boxed,fullwidth',
            'dark_mode' => 'nullable|boolean',
        ]);

        foreach ($validated as $key => $value) {
            Setting::set('pulse.' . $key, is_bool($value) ? (int) $value : $value, 'themes', 'Pulse ' . str_replace('_', ' ', $key));
        }

        Setting::set('pulse.dark_mode', $request->boolean('dark_mode') ? '1' : '0', 'themes', 'Pulse dark mode');

        return back()->with('success', 'Pulse theme settings saved.');
    })->name('theme.pulse.update');
});
