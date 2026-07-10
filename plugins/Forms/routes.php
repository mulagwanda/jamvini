<?php

use Illuminate\Support\Facades\Route;
use Plugins\Forms\src\Controllers\FormController;

Route::middleware(['auth:admin', 'admin.permission:forms,auto'])->prefix('admin')->name('admin.')->group(function () {
    Route::resource('forms', FormController::class);
    Route::get('/forms/{form}/submissions', [FormController::class, 'submissions'])->name('forms.submissions');
    Route::get('/forms/{form}/submissions/{submission}', [FormController::class, 'showSubmission'])->name('forms.submissions.show');
    Route::delete('/forms/{form}/submissions/{submission}', [FormController::class, 'deleteSubmission'])->name('forms.submissions.delete');
});

Route::get('/form/{slug}', [FormController::class, 'render'])->name('form.render');
Route::post('/form/{slug}', [FormController::class, 'submit'])->name('form.submit');
