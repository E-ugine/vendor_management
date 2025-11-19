<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Role switcher
    Route::post('/role/switch', function (Illuminate\Http\Request $request) {
        $request->validate([
            'role' => ['required', 'string', 'in:' . implode(',', array_column(\App\Enums\UserRole::cases(), 'value'))]
        ]);
        
        $request->user()->update(['current_role' => $request->role]);
        
        return back()->with('status', 'Role switched successfully!');
    })->name('role.switch');
});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Role switcher
    Route::post('/role/switch', function (Illuminate\Http\Request $request) {
        $request->validate([
            'role' => ['required', 'string', 'in:' . implode(',', array_column(\App\Enums\UserRole::cases(), 'value'))]
        ]);
        
        $request->user()->update(['current_role' => $request->role]);
        
        return back()->with('status', 'Role switched successfully!');
    })->name('role.switch');
    
    // Vendor management
    Route::resource('vendors', \App\Http\Controllers\VendorController::class);
});

require __DIR__.'/auth.php';
