                            @if (Auth::user()->is_admin)
                            <a href="{{ route('admin.users.index') }}"
                               class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm font-medium">
                                管理使用者
                            </a>
                            <a href="{{ route('admin.user-locks.index') }}"
                               class="text-gray-500 hover:text-gray-700 px-3 py-2 text-sm font-medium">
                                登入鎖定管理
                            </a>
                            @endif 