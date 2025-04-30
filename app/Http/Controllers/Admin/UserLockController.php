<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use App\Models\User;

class UserLockController extends Controller
{
    /**
     * 最大嘗試次數
     */
    private const MAX_ATTEMPTS = 5;

    /**
     * 顯示被鎖定的用戶列表
     */
    public function index(Request $request)
    {
        // 獲取每頁顯示筆數，預設為 10 筆
        $perPage = (int)$request->input('per_page', 10);
        
        // 設定合法的每頁筆數選項
        $validPerPageOptions = [5, 10, 20, 30, 9999];
        
        // 如果不在合法選項中，預設為 10 筆
        if (!in_array($perPage, $validPerPageOptions)) {
            $perPage = 10;
        }
        
        // 獲取篩選條件
        $emailFilter = $request->input('email', '');
        $ipFilter = $request->input('ip', '');
        
        // 直接獲取所有鎖定用戶的完整資訊
        $allLockedUsers = $this->getLockedUsers();
        
        // 應用篩選條件
        if (!empty($emailFilter) || !empty($ipFilter)) {
            $allLockedUsers = array_filter($allLockedUsers, function($user) use ($emailFilter, $ipFilter) {
                $emailMatch = empty($emailFilter) || stripos($user['email'], $emailFilter) !== false;
                $ipMatch = empty($ipFilter) || stripos($user['ip'], $ipFilter) !== false;
                return $emailMatch && $ipMatch;
            });
        }
        
        // 排序功能
        $sortBy = $request->input('sort_by', 'available_in'); // 預設按剩餘鎖定時間排序
        $sortOrder = $request->input('sort_order', 'desc'); // 預設降序排列
        
        // 確保排序欄位是允許的
        $allowedSortFields = ['email', 'ip', 'attempts', 'available_in'];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'available_in';
        }
        
        // 確保排序方向是允許的
        $allowedSortOrders = ['asc', 'desc'];
        if (!in_array($sortOrder, $allowedSortOrders)) {
            $sortOrder = 'desc';
        }
        
        // 應用排序
        usort($allLockedUsers, function($a, $b) use ($sortBy, $sortOrder) {
            if ($sortOrder === 'asc') {
                return $a[$sortBy] <=> $b[$sortBy];
            } else {
                return $b[$sortBy] <=> $a[$sortBy];
            }
        });
        
        // 手動分頁處理
        $page = $request->input('page', 1);
        $offset = ($page - 1) * $perPage;
        
        $lockedUsers = array_slice($allLockedUsers, $offset, $perPage);
        $totalUsers = count($allLockedUsers);
        
        // 創建分頁實例
        $lockedUsersPaginator = new \Illuminate\Pagination\LengthAwarePaginator(
            $lockedUsers,
            $totalUsers,
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
        
        // 將資料傳遞給視圖
        return view('admin.users.locks', [
            'lockedUsers' => $lockedUsersPaginator,
            'perPage' => $perPage,
            'emailFilter' => $emailFilter,
            'ipFilter' => $ipFilter,
            'sortBy' => $sortBy,
            'sortOrder' => $sortOrder
        ]);
    }

    /**
     * 解鎖特定用戶
     */
    public function unlock(Request $request)
    {
        $key = $request->input('key');

        // 檢查鍵是否有效
        if (!$key) {
            return back()->with('error', '無效的鎖定鍵');
        }

        // 清除該用戶的RateLimit
        RateLimiter::clear($key);

        return back()->with('success', '用戶已成功解鎖');
    }

    /**
     * 獲取所有被鎖定的用戶
     * 
     * @return array
     */
    private function getLockedUsers()
    {
        $lockedUsers = [];
        
        // 獲取所有用戶
        $users = User::all();
        
        // 檢查每個用戶在各種可能的IP下是否被鎖定
        foreach ($users as $user) {
            $email = $user->email;
            $name = $user->name;
            
            // 檢查用戶在當前IP下是否被鎖定
            $currentIp = request()->ip();
            $this->checkUserLock($lockedUsers, $email, $currentIp, $name);
            
            // 檢查常見的本地IP地址
            $this->checkUserLock($lockedUsers, $email, '127.0.0.1', $name);
            $this->checkUserLock($lockedUsers, $email, '::1', $name);
        }
        
        // 如果沒有找到真實的鎖定用戶，添加一個演示數據以便展示UI
        if (empty($lockedUsers) && app()->environment('local', 'development')) {
            $lockedUsers[] = [
                'key' => 'demo@example.com|127.0.0.1',
                'email' => 'demo@example.com',
                'name' => '示範用戶',
                'ip' => '127.0.0.1',
                'attempts' => 5,
                'available_in' => 3600, // 1小時
            ];
        }
        
        return $lockedUsers;
    }
    
    /**
     * 檢查特定用戶在特定IP下是否被鎖定
     * 
     * @param array &$lockedUsers 要填充的鎖定用戶數組
     * @param string $email 用戶電子郵件
     * @param string $ip IP地址
     * @param string $name 用戶名稱
     */
    private function checkUserLock(&$lockedUsers, $email, $ip, $name = '')
    {
        // 生成與LoginRequest中相同格式的鍵
        $key = $this->generateThrottleKey($email, $ip);
        
        // 檢查是否超過嘗試限制
        if (RateLimiter::tooManyAttempts($key, self::MAX_ATTEMPTS)) {
            $attempts = RateLimiter::attempts($key);
            $availableIn = RateLimiter::availableIn($key);
            
            $lockedUsers[] = [
                'key' => $key,
                'email' => $email,
                'name' => $name,
                'ip' => $ip,
                'attempts' => $attempts,
                'available_in' => $availableIn,
            ];
        }
    }
    
    /**
     * 生成與LoginRequest中相同格式的節流鍵
     * 
     * @param string $email
     * @param string $ip
     * @return string
     */
    private function generateThrottleKey($email, $ip)
    {
        return Str::transliterate(Str::lower($email).'|'.$ip);
    }
} 