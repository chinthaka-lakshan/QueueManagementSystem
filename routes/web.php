<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\CashierController;
use App\Http\Controllers\DisplayController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\PatientQueueController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Public Patient Portal & Online Token Booking
Route::get('/', [PatientQueueController::class, 'index'])->name('home');
Route::post('/patient/book-token', [PatientQueueController::class, 'bookToken'])->name('patient.book');
Route::get('/patient/track/{token}', [PatientQueueController::class, 'tracker'])->name('patient.tracker');
Route::get('/api/patient/token-status/{token}', [PatientQueueController::class, 'liveStatus'])->name('api.patient.status');

// Public Waiting Room TV Display Screen
Route::get('/display/{session?}', [DisplayController::class, 'index'])->name('display.index');
Route::get('/api/display/live/{session?}', [DisplayController::class, 'liveData'])->name('api.display.live');

// Default Dashboard Route
Route::get('/dashboard', function () {
    $user = auth()->user();
    if ($user->role === 'doctor') {
        return redirect()->route('doctor.dashboard');
    } elseif ($user->role === 'cashier') {
        return redirect()->route('cashier.dashboard');
    } elseif ($user->role === 'admin') {
        return redirect()->route('admin.dashboard');
    }
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Admin Operations (Auth)
Route::middleware(['auth'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::post('/doctors', [AdminController::class, 'storeDoctor'])->name('doctors.store');
    Route::post('/cashiers', [AdminController::class, 'storeCashier'])->name('cashiers.store');
    Route::post('/staff/{user}/toggle', [AdminController::class, 'toggleStaffStatus'])->name('staff.toggle');
    Route::post('/sessions', [AdminController::class, 'storeSession'])->name('sessions.store');
    Route::post('/sessions/{session}/status', [AdminController::class, 'updateSessionStatus'])->name('sessions.status');
});

// Doctor Operations (Auth)
Route::middleware(['auth'])->prefix('doctor')->name('doctor.')->group(function () {
    Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('dashboard');
    Route::post('/call-next', [DoctorController::class, 'callNext'])->name('call-next');
    Route::post('/call/{token}', [DoctorController::class, 'callSpecificToken'])->name('call');
    Route::post('/hold/{token}', [DoctorController::class, 'holdToken'])->name('hold');
    Route::post('/recall/{token}', [DoctorController::class, 'recallToken'])->name('recall');
    Route::post('/complete/{token}', [DoctorController::class, 'completeToken'])->name('complete');
    Route::post('/cancel/{token}', [DoctorController::class, 'cancelToken'])->name('cancel');
});

// Cashier Operations (Auth)
Route::middleware(['auth'])->prefix('cashier')->name('cashier.')->group(function () {
    Route::get('/dashboard', [CashierController::class, 'dashboard'])->name('dashboard');
    Route::post('/issue-token', [CashierController::class, 'issueToken'])->name('issue-token');
    Route::get('/search-patient', [CashierController::class, 'searchPatient'])->name('search-patient');
});

// User Profile
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
