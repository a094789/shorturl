<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    public function index(Request $request)
    {
        // 獲取篩選參數
        $keyword = $request->input('keyword');
        $role = $request->input('role');
        $date_from = $request->input('date_from');
        $date_to = $request->input('date_to');
        
        // 獲取每頁顯示筆數，預設為 10 筆
        $perPage = (int)$request->input('per_page', 10);
        
        // 設定合法的每頁筆數選項
        $validPerPageOptions = [5, 10, 20, 30, 9999];
        
        // 如果不在合法選項中，預設為 10 筆
        if (!in_array($perPage, $validPerPageOptions)) {
            $perPage = 10;
        }
        
        $users = User::orderBy('created_at', 'desc');
        
        // 關鍵字搜尋，搜尋名稱或Email
        if ($keyword) {
            $users->where(function($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%")
                    ->orWhere('email', 'like', "%{$keyword}%");
            });
        }
        
        // 權限篩選
        if ($role === 'admin') {
            $users->where('is_admin', true);
        } elseif ($role === 'user') {
            $users->where('is_admin', false);
        }
        
        // 註冊日期範圍篩選
        if ($date_from) {
            $users->whereDate('created_at', '>=', $date_from);
        }
        
        if ($date_to) {
            $users->whereDate('created_at', '<=', $date_to);
        }
        
        $users = $users->paginate($perPage)
            ->appends(request()->except('page'));
            
        return view('admin.users.index', compact(
            'users', 
            'perPage', 
            'keyword', 
            'role', 
            'date_from', 
            'date_to'
        ));
    }

    public function edit(User $user)
    {
        // 由於前端使用AJAX和彈窗進行編輯，不再需要獨立視圖
        // 返回JSON格式的用戶數據
        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'is_admin' => $user->is_admin,
                'created_at' => $user->created_at->format('Y-m-d H:i:s')
            ]
        ]);
    }

    public function update(Request $request, User $user)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'is_admin' => 'required|boolean',
            ]);

            $authUser = Auth::user();
            
            // 保護開發者帳號
            if ($user->email === 'a094789@gmail.com') {
                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => '無法修改系統開發者的權限！'], 403)
                    : back()->with('error', '無法修改系統開發者的權限！');
            }
            
            // 防止自己移除自己的管理員權限
            if ($user->id == $authUser->id && !$validated['is_admin']) {
                return $request->wantsJson()
                    ? response()->json(['success' => false, 'message' => '無法移除自己的管理員權限'], 403)
                    : back()->with('error', '無法移除自己的管理員權限');
            }

            $user->name = $validated['name'];
            $user->is_admin = $validated['is_admin'];
            $user->save();

            if ($request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => '已更新使用者資料',
                    'user' => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'is_admin' => $user->is_admin,
                        'created_at' => $user->created_at->format('Y-m-d H:i:s')
                    ]
                ]);
            }

            return redirect()->route('admin.users.index')->with('success', '已更新使用者資料');
        } catch (\Exception $e) {
            // 記錄錯誤
            Log::error('更新用戶時發生錯誤：' . $e->getMessage());
            
            // 回應適當的錯誤訊息
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => '編輯使用者資料時發生錯誤，請重試'
                ], 500);
            }
            
            return back()->with('error', '編輯使用者資料時發生錯誤，請重試');
        }
    }

    public function toggleAdmin(User $user)
    {
        $authUser = Auth::user();
        
        // 保護開發者帳號
        if ($user->email === 'a094789@gmail.com') {
            return back()->with('error', '無法修改系統開發者的權限！');
        }
        
        if ($user == $authUser) {
            return back()->with('error', '無法更改自己的管理員權限');
        }

        $user->is_admin = !$user->is_admin;
        $user->save();

        return back()->with('success', '已更新使用者權限');
    }

    public function destroy(User $user)
    {
        $authUser = Auth::user();
        
        // 保護開發者帳號
        if ($user->email === 'a094789@gmail.com') {
            return back()->with('error', '無法刪除系統開發者帳號！');
        }
        
        if ($user == $authUser) {
            return back()->with('error', '無法刪除自己的帳號');
        }

        $user->delete();
        return back()->with('success', '已刪除使用者');
    }
} 