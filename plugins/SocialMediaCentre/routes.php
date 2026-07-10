<?php

use Illuminate\Support\Facades\Route;
use Plugins\SocialMediaCentre\src\Controllers\SocialAccountController;
use Plugins\SocialMediaCentre\src\Controllers\SocialCampaignController;
use Plugins\SocialMediaCentre\src\Controllers\SocialPostController;
use Plugins\SocialMediaCentre\src\Controllers\SocialPostTemplateController;
use Plugins\SocialMediaCentre\src\Controllers\SocialSettingsController;

Route::middleware(['auth:admin', 'admin.permission:social-media-centre,auto'])->prefix('admin/social')->name('admin.social.')->group(function () {
    Route::get('/', [SocialPostController::class, 'dashboard'])->name('index');
    Route::get('/calendar', [SocialPostController::class, 'calendar'])->name('calendar');
    Route::get('/settings', [SocialSettingsController::class, 'edit'])->name('settings');
    Route::post('/settings', [SocialSettingsController::class, 'update'])->name('settings.update');
    Route::post('/ai/suggest', [SocialPostController::class, 'aiSuggest'])->name('ai.suggest');
    Route::post('/publishing/run-due', [SocialPostController::class, 'runPublishingQueue'])->name('publishing.run-due');

    Route::resource('posts', SocialPostController::class);
    Route::post('/posts/{post}/mark-published', [SocialPostController::class, 'markPublished'])->name('posts.mark-published');
    Route::post('/posts/{post}/duplicate', [SocialPostController::class, 'duplicate'])->name('posts.duplicate');
    Route::post('/posts/{post}/sync-publications', [SocialPostController::class, 'syncPublications'])->name('posts.sync-publications');
    Route::post('/publications/{publication}/mark-published', [SocialPostController::class, 'markPublicationPublished'])->name('publications.mark-published');
    Route::post('/publications/{publication}/mark-failed', [SocialPostController::class, 'markPublicationFailed'])->name('publications.mark-failed');

    Route::get('/templates/{template}/use', [SocialPostTemplateController::class, 'use'])->name('templates.use');
    Route::post('/templates/{template}/compose', [SocialPostTemplateController::class, 'compose'])->name('templates.compose');
    Route::resource('templates', SocialPostTemplateController::class)->except(['show']);
    Route::resource('campaigns', SocialCampaignController::class);
    Route::resource('accounts', SocialAccountController::class)->except(['show']);
});
