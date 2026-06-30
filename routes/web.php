<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicTracerController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\OfficeController;

/*
|--------------------------------------------------------------------------
| 1. PUBLIC ROUTES
|--------------------------------------------------------------------------
*/
Route::get('/trace', [PublicTracerController::class, 'index'])->name('public.search');

// Authentication (Registration Disabled)
Auth::routes(['register' => false]);

/*
|--------------------------------------------------------------------------
| 2. PROTECTED ROUTES (Requires Login & Verified Email)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    
    // --- DASHBOARD ---
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // --- API SEARCH ---
    Route::get('/api/users/search', function (Illuminate\Http\Request $request) {
        $search = $request->get('q');
        return App\Models\User::where('username', 'LIKE', "%$search%")
            ->select('id', 'username', 'office_id', 'role_title')
            ->with('office:id,office_name')
            ->limit(10)
            ->get();
    })->name('api.users.search');

    /*
    |--------------------------------------------------------------------------
    | 3. DOCUMENT MANAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::prefix('document')->group(function () {
        
        // Viewer & Preview
        Route::get('/live-preview/{id}', [DocumentController::class, 'previewWithSigs'])
            ->name('documents.preview-sigs')
            ->where('id', '[0-9]+'); 

        Route::get('/view/{id}', [DocumentController::class, 'show'])
            ->name('documents.view')
            ->where('id', '.*'); 

        Route::get('/stream-file/{id}', [DocumentController::class, 'streamFile'])
            ->name('documents.stream');

        Route::get('/download/{id}', [DocumentController::class, 'downloadFinal'])
            ->name('documents.download')
            ->where('id', '.*');

        // Registration & Deletion
        Route::get('/new', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('/store', [DocumentController::class, 'store'])->name('documents.store');
        Route::delete('/delete/{id}', [DocumentController::class, 'destroy'])->name('documents.delete');

        // Processing Actions
        Route::post('/sign/{id}', [DocumentController::class, 'sign'])->name('documents.sign')->where('id', '.*');
        Route::post('/return/{id}', [DocumentController::class, 'return'])->name('documents.return');
        Route::post('/resubmit/{id}', [DocumentController::class, 'resubmit'])->name('documents.resubmit')->where('id', '.*');
        Route::post('/revalidate/{id}', [DocumentController::class, 'revalidate'])->name('documents.revalidate')->where('id', '.*');
        Route::post('/disseminate/{id}', [DocumentController::class, 'disseminate'])->name('documents.disseminate');
        
        // QR SCAN RECEIVE (CRITICAL FIX: Changed to GET and linked to publicReceive)
        Route::get('/receive/{tracking_id}', [DocumentController::class, 'publicReceive'])->name('documents.publicReceive');        

        // Mapping & QR Code
        Route::get('/map/{id}', [DocumentController::class, 'map'])->name('documents.map');
        Route::post('/save-tag', [DocumentController::class, 'saveTag'])->name('documents.saveTag');
        Route::post('/delete-tag', [DocumentController::class, 'deleteTag'])->name('documents.deleteTag');
        Route::post('/save-qr-tag', [DocumentController::class, 'saveQrTag'])->name('documents.saveQrTag');
        Route::post('/delete-qr-tag', [DocumentController::class, 'deleteQrTag'])->name('documents.deleteQrTag');
        Route::post('/move-tag', [DocumentController::class, 'moveTag'])->name('tags.move');
        
        // Download QR Code
        Route::get('/download-qr/{id}', [DocumentController::class, 'downloadQr'])->name('documents.downloadQr')->where('id', '.*');

        // Lifecycle Flow
        Route::get('/discard/{id}', [DocumentController::class, 'discard'])->name('documents.discard');
        Route::get('/finalize/{id}', [DocumentController::class, 'finalizeMapping'])->name('documents.finalize');
        Route::post('/set-priority/{id}', [DocumentController::class, 'setPriority'])->name('documents.setPriority');
    });

    /*
    |--------------------------------------------------------------------------
    | 4. ADMIN & PERSONNEL MANAGEMENT
    |--------------------------------------------------------------------------
    */
    Route::prefix('admin')->name('admin.')->middleware(['superadmin'])->group(function () {
        Route::get('/personnel', [StaffController::class, 'index'])->name('personnel');
        Route::post('/staff/reset-password/{id}', [StaffController::class, 'resetPassword'])->name('staff.resetPassword');
        Route::resource('staff', StaffController::class)->only(['store', 'destroy']);
        Route::resource('offices', OfficeController::class);
    });

    /*
    |--------------------------------------------------------------------------
    | 5. USER PROFILE & UTILITIES
    |--------------------------------------------------------------------------
    */
    Route::prefix('profile')->group(function () {
        Route::post('/update', [DashboardController::class, 'updateProfile'])->name('profile.update');
        Route::post('/change-password', [DocumentController::class, 'changeOwnPassword'])->name('profile.password.update');
    });

});

require __DIR__.'/auth.php';