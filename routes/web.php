<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ShortUrlController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserLockController;
use App\Http\Middleware\AdminMiddleware;

// 短網址重定向（公開訪問）- 放在最上方優先處理
Route::get('/s/{shortCode}', [ShortUrlController::class, 'redirect'])->name('short-url.redirect');
// 當訪問/s或/s/時，導向登入頁面
Route::get('/s', function () {
    return redirect()->route('login');
});

// 首頁直接導向登入頁面，不檢查用戶登入狀態
Route::redirect('/', '/login');

// 訪客相關路由
Route::middleware('guest')->group(function () {
    Route::controller(RegisteredUserController::class)->group(function () {
        Route::get('register', 'create')->name('register');
        Route::post('register', 'store');
    });

    Route::controller(AuthenticatedSessionController::class)->group(function () {
        Route::get('login', 'create')->name('login');
        Route::post('login', 'store');
    });
});

// 登出功能
Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// 需要登入的功能
Route::middleware('auth')->group(function () {
    // 儀表板
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    // 個人資料管理
    Route::controller(ProfileController::class)->prefix('profile')->group(function () {
        Route::get('/', 'edit')->name('profile.edit');
        Route::patch('/', 'update')->name('profile.update');
        Route::delete('/', 'destroy')->name('profile.destroy');
        Route::put('/password', 'updatePassword')->name('password.update');
    });

    // 短網址管理
    Route::resource('short-urls', ShortUrlController::class)->only([
        'index',
        'create',
        'store',
        'destroy'
    ])->parameters([
        'short_urls' => 'shortUrl'
    ]);

    // 短網址額外功能
    Route::controller(ShortUrlController::class)->prefix('short-urls')->name('short-urls.')->group(function () {
        Route::get('{shortUrl}/clicks', 'showClicks')->name('clicks');

        // QR 碼相關
        Route::match(['get', 'post'], '{shortUrl}/qrcode', 'qrcode')->name('qrcode');
        Route::match(['get', 'post'], '{shortUrl}/qrcode/download', 'downloadQrCode')->name('qrcode.download');
    });

    // 管理員專用功能
    Route::middleware(AdminMiddleware::class)
        ->prefix('admin')
        ->name('admin.')
        ->group(function () {
            // 用戶管理
            Route::resource('users', UserController::class)->only([
                'index',
                'edit',
                'update',
                'destroy'
            ]);
            Route::patch('users/{user}/toggle-admin', [UserController::class, 'toggleAdmin'])->name('users.toggle-admin');

            // 用戶鎖定管理
            Route::controller(UserLockController::class)->prefix('user-locks')->name('user-locks.')->group(function () {
                Route::get('/', 'index')->name('index');
                Route::post('/unlock', 'unlock')->name('unlock');
            });
        });
});
