<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PublicTracerController;
use App\Http\Controllers\Admin\StaffController;
use App\Http\Controllers\Admin\OfficeController;

// 1. PUBLIC ROUTES
Route::get('/trace', [PublicTracerController::class, 'index'])->name('public.search');

// 2. AUTHENTICATION (Registration Disabled)
Auth::routes(['register' => false]);

// 3. PROTECTED ROUTES (Requires Login & Verified Email)
Route::middleware(['auth', 'verified'])->group(function () {
    
    // DASHBOARD
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // API SEARCH
    Route::get('/api/users/search', function (Illuminate\Http\Request $request) {
        $search = $request->get('q');
        return App\Models\User::where('username', 'LIKE', "%$search%")
            ->select('id', 'username', 'office_id', 'role_title')
            ->with('office:id,office_name')
            ->limit(10)
            ->get();
    })->name('api.users.search');

    // DOCUMENT MANAGEMENT GROUP
    Route::prefix('document')->group(function () {
        
        // --- 1. VIEWER & PREVIEW ---
        Route::get('/live-preview/{id}', [DocumentController::class, 'previewWithSigs'])
            ->name('documents.preview-sigs')
            ->where('id', '[0-9]+'); 

        Route::get('/view/{id}', [DocumentController::class, 'show'])
            ->name('documents.view')
            ->where('id', '.*'); 

        Route::get('/stream-file/{id}', [DocumentController::class, 'streamFile'])
            ->name('documents.stream');

        // --- 2. REGISTRATION & DELETION ---
        Route::get('/new', [DocumentController::class, 'create'])->name('documents.create');
        Route::post('/store', [DocumentController::class, 'store'])->name('documents.store');
        Route::delete('/delete/{id}', [DocumentController::class, 'destroy'])->name('documents.delete');

        // --- 3. DOCUMENT ACTIONS ---
        Route::post('/sign/{id}', [DocumentController::class, 'sign'])
            ->name('documents.sign')
            ->where('id', '.*');

        Route::post('/return/{id}', [DocumentController::class, 'return'])
            ->name('documents.return')
            ->where('id', '.*');

        Route::post('/resubmit/{id}', [DocumentController::class, 'resubmit'])
            ->name('documents.resubmit')
            ->where('id', '.*');

        Route::post('/revalidate/{id}', [DocumentController::class, 'revalidate'])
            ->name('documents.revalidate')
            ->where('id', '.*');

        Route::post('/disseminate/{id}', [DocumentController::class, 'disseminate'])->name('documents.disseminate');
        
        Route::get('/download/{id}', [DocumentController::class, 'downloadFinal'])
            ->name('documents.download')
            ->where('id', '.*');
        
        // --- 4. MAPPING & QR CODE ---
        Route::get('/map/{id}', [DocumentController::class, 'map'])->name('documents.map');
        Route::post('/save-tag', [DocumentController::class, 'saveTag'])->name('documents.saveTag');
        Route::post('/delete-tag', [DocumentController::class, 'deleteTag'])->name('documents.deleteTag');
        Route::post('/move-tag', [DocumentController::class, 'moveTag'])->name('tags.move');
        Route::get('/download-qr/{id}', [DocumentController::class, 'downloadQr'])
            ->name('documents.downloadQr')
            ->where('id', '.*');

        // --- 5. LIFECYCLE FLOW ---
        Route::get('/discard/{id}', [DocumentController::class, 'discard'])->name('documents.discard');
        Route::get('/finalize/{id}', [DocumentController::class, 'finalizeMapping'])->name('documents.finalize');
        Route::post('/set-priority/{id}', [DocumentController::class, 'setPriority'])->name('documents.setPriority');
    });

    // 4. ADMIN & PERSONNEL MANAGEMENT
    Route::prefix('admin')->name('admin.')->middleware(['superadmin'])->group(function () {
        Route::get('/personnel', [StaffController::class, 'index'])->name('personnel');
        Route::post('/staff/reset-password/{id}', [StaffController::class, 'resetPassword'])->name('staff.resetPassword');
        Route::resource('staff', StaffController::class)->only(['store', 'destroy']);
        Route::resource('offices', OfficeController::class);
    });

    // 5. USER PROFILE & UTILITIES
    Route::post('/profile/change-password', [StaffController::class, 'changeOwnPassword'])->name('profile.password.update');
    Route::post('/profile/update', [DashboardController::class, 'updateProfile'])->name('profile.update');

    Route::get('/test-mail', function() {
        $doc = \App\Models\Document::first();
        if($doc) {
            Mail::to('test@example.com')->send(new \App\Mail\UrgentDocumentAlert($doc, false));
            return "Check Mailtrap!";
        }
        return "No documents found to test.";
    });

}); // <--- THIS WAS MISSING: Closes the group started on line 19

require __DIR__.'/auth.php';