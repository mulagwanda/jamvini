<?php

use Illuminate\Support\Facades\Route;
use Plugins\AiAssistant\src\Controllers\AdminAiAssistantController;
use Plugins\AiAssistant\src\Controllers\WidgetController;

Route::middleware(['auth:admin', 'admin.permission:ai-assistant,auto'])->prefix('admin/ai-assistant')->name('admin.ai-assistant.')->group(function () {
    Route::get('/', [AdminAiAssistantController::class, 'index'])->name('index');
    Route::get('/settings', [AdminAiAssistantController::class, 'settings'])->name('settings');
    Route::post('/settings', [AdminAiAssistantController::class, 'saveSettings'])->name('settings.save');

    Route::get('/conversations', [AdminAiAssistantController::class, 'conversations'])->name('conversations.index');
    Route::get('/conversations/{conversation}', [AdminAiAssistantController::class, 'showConversation'])->name('conversations.show');
    Route::post('/conversations/{conversation}/reply', [AdminAiAssistantController::class, 'replyConversation'])->name('conversations.reply');
    Route::patch('/conversations/{conversation}', [AdminAiAssistantController::class, 'updateConversation'])->name('conversations.update');

    Route::get('/sources', [AdminAiAssistantController::class, 'sources'])->name('sources.index');
    Route::post('/sources', [AdminAiAssistantController::class, 'storeSource'])->name('sources.store');
    Route::post('/sources/{source}/reindex', [AdminAiAssistantController::class, 'reindexSource'])->name('sources.reindex');
    Route::delete('/sources/{source}', [AdminAiAssistantController::class, 'destroySource'])->name('sources.destroy');
});

Route::prefix('ai-assistant')->name('ai-assistant.')->group(function () {
    Route::get('/config', [WidgetController::class, 'config'])->name('config');
    Route::get('/knowledge-base', [WidgetController::class, 'knowledgeBase'])->name('knowledge-base');
    Route::get('/conversation/{conversation}', [WidgetController::class, 'conversation'])->name('conversation');
    Route::post('/message', [WidgetController::class, 'message'])->name('message');
    Route::post('/escalate', [WidgetController::class, 'escalate'])->name('escalate');
});
