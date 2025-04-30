<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index()
    {
        $users = User::paginate(10);
        return view('admin.users.index', compact('users'));
    }

    public function toggleAdmin(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', '無法更改自己的管理員權限！');
        }

        $user->update(['is_admin' => !$user->is_admin]);

        return back()->with('success', '使用者權限已更新！');
    }
} 