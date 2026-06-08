<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicTracerController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\OfficeController;

// 1. PUBLIC ROUTES
Route::get('/trace', [PublicTracerController::class, 'index'])->name('public.search');

// 2. AUTHENTICATION (No Public Register)
Auth::routes(['register' => false]);

// 3. PROTECTED ROUTES (Must be logged in)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // DASHBOARD
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // API SEARCH
    Route::get('/api/users/search', function (Illuminate\Http\Request $request) {
        $search = $request->get('q');
        return App\Models\User::where('username', 'LIKE', "%$search%")
            ->select('id', 'username', 'office_id')
            ->with('office:id,office_name')
            ->limit(10)->get();
    })->name('api.users.search');

    // DOCUMENT MANAGEMENT
    Route::prefix('document')->group(function () {
        
        // --- IMPORTANT: ORDER MATTERS HERE ---
        
        // 1. LIVE PREVIEW (Dapat laging una at dapat Number lang ang tinatanggap)
        Route::get('/live-preview/{id}', [DocumentController::class, 'previewWithSigs'])
            ->name('documents.preview-sigs')
            ->where('id', '[0-9]+'); 

        // 2. VIEW HUB (Pangalawa ito, ito yung catch-all para sa Tracking ID na may slashes)
        Route::get('/view/{id}', [DocumentController::class, 'show'])
            ->name('documents.view')
            ->where('id', '.*'); 

        Route::get('/stream-file/{id}', [DocumentController::class, 'streamFile'])
        ->name('documents.stream');

        // 3. STORE & CREATE
        Route::get('/new', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('/store', [DocumentController::class, 'store'])->name('documents.store');
        Route::delete('/delete/{id}', [DocumentController::class, 'destroy'])->name('documents.delete');

        // 4. ACTIONS
        Route::post('/sign/{id}', [DocumentController::class, 'sign'])->name('documents.sign');
        Route::post('/return/{id}', [DocumentController::class, 'return'])->name('documents.return');
        Route::post('/resubmit/{id}', [DocumentController::class, 'resubmit'])
            ->name('documents.resubmit')
            ->where('id', '.*'); // <--- ADD THIS
        Route::post('/disseminate/{id}', [DocumentController::class, 'disseminate'])->name('documents.disseminate');
        Route::get('/download/{id}', [DocumentController::class, 'downloadFinal'])->name('documents.download');
        
        // 5. MAPPING
        Route::get('/map/{id}', [DocumentController::class, 'map'])->name('documents.map');
        Route::post('/save-tag', [DocumentController::class, 'saveTag'])->name('documents.saveTag');
        Route::post('/delete-tag', [DocumentController::class, 'deleteTag'])->name('documents.deleteTag');
        Route::post('/move-tag', [DocumentController::class, 'moveTag'])->name('tags.move');
    });

    // 4. ADMIN ROUTES
    Route::prefix('admin')->name('admin.')->middleware(['superadmin'])->group(function () {
        Route::get('/personnel', [StaffController::class, 'index'])->name('personnel');
        Route::post('/staff/reset-password/{id}', [StaffController::class, 'resetPassword'])->name('staff.resetPassword');
        Route::resource('staff', StaffController::class)->only(['store', 'destroy']);
        Route::resource('offices', OfficeController::class);
    });
    Route::get('/test-mail', function() {
        $doc = \App\Models\Document::first();
        Mail::to('test@example.com')->send(new \App\Mail\UrgentDocumentAlert($doc, false));
        return "Check Mailtrap!";
    });
    Route::post('/profile/change-password', [App\Http\Controllers\Admin\StaffController::class, 'changeOwnPassword'])->name('profile.password.update');

});

require __DIR__.'/auth.php';