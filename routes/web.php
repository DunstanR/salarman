<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\LeaveController;
use App\Models\User;
use App\Models\Role;

// Authentication Routes
Route::middleware('guest')->group(function () {
    // Login Routes
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'sendVerificationCode'])->name('login.send-code');
    Route::get('/verify', [LoginController::class, 'showVerificationForm'])->name('verification.notice');
    Route::post('/verify', [LoginController::class, 'verify'])->name('verification.verify');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');

    // Leave routes
    Route::get('/leave/balance', [LeaveController::class, 'balance'])->name('leave.balance');
    Route::get('/leave/apply', [LeaveController::class, 'create'])->name('leave.apply');
    Route::post('/leave/apply', [LeaveController::class, 'store'])->name('leave.store');
    Route::get('/leave/history', [LeaveController::class, 'history'])->name('leave.history');

    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
});

// Root Route
Route::get('/', function () {
    return Auth::check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Test route for MongoDB connection
Route::get('/test-mongodb', function () {
    try {
        $user = Auth::user();
        $department = $user->department();
        return [
            'user_id' => $user->_id,
            'department_id' => $user->attributes['department'] ?? null,
            'department' => $department ? $department->toArray() : null,
            'department_name' => $department ? $department->depName : null,
            'department_name_attribute' => $user->department_name
        ];
    } catch (\Exception $e) {
        return [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ];
    }
});

// Temporary route to check user role
Route::get('/check-role', function () {
    $user = User::where('email', 'dunstanrathnayake@gmail.com')->first();
    
    if (!$user) {
        return response()->json(['error' => 'User not found']);
    }
    
    // Get the raw role from the user document
    $rawRole = $user->getRawOriginal('role');
    
    // Get all roles for reference
    $allRoles = Role::all()->pluck('name', '_id');
    
    return response()->json([
        'user' => [
            'id' => $user->_id,
            'email' => $user->email,
            'firstname' => $user->firstname,
            'lastname' => $user->lastname,
            'raw_role' => $rawRole,
        ],
        'role_found' => $user->role ? [
            'id' => $user->role->_id,
            'name' => $user->role->name,
            'description' => $user->role->description
        ] : null,
        'all_roles' => $allRoles,
        'is_teacher' => $user->isTeacher()
    ]);
});

// Temporary route to check all roles
Route::get('/check-roles', function () {
    $roles = Role::all();
    return response()->json([
        'roles' => $roles->map(function($role) {
            return [
                'id' => $role->_id,
                'name' => $role->name,
                'description' => $role->description
            ];
        })
    ]);
});

// Temporary route to update user role to TEACHER
Route::get('/update-role', function () {
    $user = User::where('email', 'dunstanrathnayake@gmail.com')->first();
    
    if (!$user) {
        return response()->json(['error' => 'User not found']);
    }
    
    // Get the TEACHER role
    $teacherRole = Role::where('name', 'TEACHER')->first();
    
    if (!$teacherRole) {
        return response()->json(['error' => 'Teacher role not found']);
    }
    
    // Update the user's role to just the ID
    $user->role = $teacherRole->_id;
    $user->save();
    
    return response()->json([
        'message' => 'Role updated successfully',
        'user' => [
            'id' => $user->_id,
            'email' => $user->email,
            'role' => $user->role,
        ],
        'is_teacher' => $user->isTeacher()
    ]);
});
