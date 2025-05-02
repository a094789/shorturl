<x-app-layout>
    <x-slot name="title">用戶管理</x-slot>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('用戶管理') }}
        </h2>
    </x-slot>

    <style>
        .select-wrapper {
            position: relative;
        }
        .select-wrapper::after {
            content: '';
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            width: 0; 
            height: 0; 
            border-left: 5px solid transparent;
            border-right: 5px solid transparent;
            border-top: 5px solid #6b7280;
            pointer-events: none;
        }
        .edit-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .edit-modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            width: 90%;
            max-width: 500px;
            max-height: 90vh;
            overflow-y: auto;
        }
    </style>

    <!-- 編輯用戶彈窗 -->
    <div id="editUserModal" class="edit-modal">
        <div class="edit-modal-content">
            <h3 class="text-lg font-medium text-gray-900 mb-6">編輯使用者資料</h3>
            <div id="editError" class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg hidden" role="alert"></div>
            
            <form id="editUserForm">
                @csrf
                @method('PATCH')
                <input type="hidden" id="editUserId" name="user_id">

                <div class="mb-6">
                    <label for="editName" class="block mb-2 text-sm font-medium text-gray-900">用戶名稱</label>
                    <input type="text" id="editName" name="name" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5" required>
                </div>

                <div class="mb-6">
                    <label class="block mb-2 text-sm font-medium text-gray-900">Email</label>
                    <p id="editEmail" class="text-gray-500"></p>
                    <p class="mt-2 text-xs text-gray-500">電子郵件地址無法更改</p>
                </div>

                <div class="mb-6">
                    <label class="block mb-2 text-sm font-medium text-gray-900">註冊時間</label>
                    <p id="editCreatedAt" class="text-gray-500"></p>
                </div>

                <div class="mb-6">
                    <label class="block mb-2 text-sm font-medium text-gray-900">權限設定</label>
                    <div class="flex items-center">
                        <input id="editIsAdmin0" type="radio" name="is_admin" value="0" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500">
                        <label for="editIsAdmin0" class="ml-2 text-sm font-medium text-gray-900">一般用戶</label>
                        
                        <div class="w-32"></div>
                        
                        <input id="editIsAdmin1" type="radio" name="is_admin" value="1" class="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 focus:ring-blue-500 ml-2">
                        <label for="editIsAdmin1" class="ml-2 text-sm font-medium text-gray-900">管理員</label>
                    </div>
                    @error('is_admin')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center space-x-4 mt-8">
                    <button type="button" id="saveUserBtn" class="text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:ring-red-300 font-medium rounded-lg text-sm px-6 py-3 text-center w-32">
                        儲存變更
                    </button>
                    <button type="button" id="cancelEditBtn" class="text-gray-700 bg-gray-200 hover:bg-gray-300 focus:ring-4 focus:ring-gray-200 font-medium rounded-lg text-sm px-6 py-3 text-center w-24">
                        取消
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="py-4 lg:py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <!-- 顯示成功訊息 -->
                @if(session('success'))
                    <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                        {{ session('success') }}
                    </div>
                @endif
                
                <div class="mb-6 flex justify-between">
                    <div>
                        <h3 class="text-lg font-medium text-gray-900 mb-1">用戶管理</h3>
                        <p class="text-sm text-gray-600 mb-4">在這裡可以管理所有用戶，設定權限及刪除用戶。</p>
                    </div>
                    <div>
                        <a href="{{ route('admin.user-locks.index') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-blue-500 active:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            查看登入鎖定的用戶
                        </a>
                    </div>
                </div>
                
                <!-- 篩選狀態摘要 -->
                @if(!empty($keyword) || !empty($role) || !empty($date_from) || !empty($date_to))
                    <div class="mb-4 text-sm">
                        <p class="text-gray-600">目前篩選條件：</p>
                        <div class="flex flex-wrap gap-2 mt-1">
                            @if(!empty($keyword))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    關鍵字: {{ $keyword }}
                                </span>
                            @endif
                            
                            @if(!empty($role))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    權限: 
                                    @if($role == 'admin')
                                        管理員
                                    @elseif($role == 'user')
                                        一般用戶
                                    @endif
                                </span>
                            @endif
                            
                            @if(!empty($date_from) || !empty($date_to))
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                    註冊日期: 
                                    {{ !empty($date_from) ? $date_from : '最早' }} 
                                    - 
                                    {{ !empty($date_to) ? $date_to : '最新' }}
                                </span>
                            @endif
                            
                            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                                清除所有篩選
                            </a>
                        </div>
                    </div>
                @endif
                
                <!-- 篩選功能 (可摺疊) -->
                <div x-data="{ showFilters: {{ (!empty($keyword) || !empty($role) || !empty($date_from) || !empty($date_to)) ? 'true' : 'false' }} }" class="mb-6">
                    <!-- 篩選切換按鈕 -->
                    <button @click="showFilters = !showFilters" type="button" class="flex items-center mb-3 text-gray-600 hover:text-gray-900">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <span class="text-sm font-medium" x-text="showFilters ? '收起篩選' : '展開篩選'">展開篩選</span>
                        <svg class="w-4 h-4 ml-1 transition-transform duration-200" :class="showFilters ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                        @if(!empty($keyword) || !empty($role) || !empty($date_from) || !empty($date_to))
                            <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                篩選中
                            </span>
                        @endif
                    </button>

                    <!-- 篩選表單 -->
                    <div x-show="showFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-4 bg-gray-50 rounded-lg">
                        <form action="{{ route('admin.users.index') }}" method="GET" class="space-y-6">
                            <div class="space-y-6">
                                <!-- 關鍵字和權限 -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- 關鍵字搜尋 -->
                                    <div>
                                        <label for="keyword" class="block text-sm font-medium text-gray-700 mb-1">關鍵字搜尋</label>
                                        <input 
                                            type="text" 
                                            id="keyword" 
                                            name="keyword" 
                                            value="{{ $keyword }}"
                                            placeholder="搜尋名稱或 Email" 
                                            class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                        >
                                    </div>
                                    
                                    <!-- 權限篩選 -->
                                    <div>
                                        <label for="role" class="block text-sm font-medium text-gray-700 mb-1">用戶權限</label>
                                        <select id="role" name="role" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">所有權限</option>
                                            <option value="admin" {{ $role === 'admin' ? 'selected' : '' }}>管理員</option>
                                            <option value="aw4" {{ $role === 'aw4' ? 'selected' : '' }}>網路組教職員</option>
                                            <option value="employees" {{ $role === 'employees' ? 'selected' : '' }}>教職員</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <!-- 註冊日期範圍 -->
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">開始日期</label>
                                        <input type="date" id="date_from" name="date_from" value="{{ $date_from }}" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">結束日期</label>
                                        <input type="date" id="date_to" name="date_to" value="{{ $date_to }}" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 每頁顯示筆數 (作為篩選表單的一部分) -->
                            <input type="hidden" name="per_page" value="{{ $perPage }}">
                            
                            <!-- 篩選按鈕 -->
                            <div class="flex items-center space-x-4">
                                <!-- 提交按鈕 -->
                                <x-primary-button>
                                    {{ __('篩選') }}
                                </x-primary-button>
                                
                                <!-- 重置按鈕 -->
                                <a href="{{ route('admin.users.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                    {{ __('重置') }}
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- 用戶表格 -->
                <div class="overflow-x-auto">
                    <div>
                        <form action="{{ route('admin.users.index') }}" method="GET" class="mb-4">
                            <div class="flex justify-end items-center">
                                <input type="hidden" name="keyword" value="{{ request('keyword') }}">
                                <input type="hidden" name="role" value="{{ request('role') }}">
                                <input type="hidden" name="date_from" value="{{ request('date_from') }}">
                                <input type="hidden" name="date_to" value="{{ request('date_to') }}">

                                <label for="per_page" class="mr-2">{{ __('每頁顯示') }}</label>
                                <select id="per_page" name="per_page" onchange="this.form.submit()" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pr-8 appearance-none">
                                    @foreach ([5, 10, 20, 30, 9999] as $option)
                                        <option value="{{ $option }}" {{ $perPage == $option ? 'selected' : '' }}>
                                            {{ $option == 9999 ? __('全部') : $option }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </form>
                    </div>

                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                            <tr>
                                <th scope="col" class="px-6 py-3">名稱</th>
                                <th scope="col" class="px-6 py-3">Email</th>
                                <th scope="col" class="px-6 py-3">註冊時間</th>
                                <th scope="col" class="px-6 py-3">權限</th>
                                <th scope="col" class="px-6 py-3">操作</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                            <tr class="bg-white border-b hover:bg-gray-50" data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}" data-user-email="{{ $user->email }}" data-user-created="{{ $user->created_at->format('Y-m-d H:i:s') }}" data-user-admin="{{ $user->is_admin ? '1' : '0' }}">
                                <td class="px-6 py-4">
                                    <div class="flex items-center">
                                        <div class="ml-3">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $user->name }}
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">{{ $user->email }}</td>
                                <td class="px-6 py-4">{{ $user->created_at->format('Y-m-d H:i:s') }}</td>
                                <td class="px-6 py-4">
                                    <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                        @if($user->role_id === 1) bg-blue-100 text-blue-800
                                        @elseif($user->role_id === 2) bg-green-100 text-green-800
                                        @else bg-gray-100 text-gray-800
                                        @endif">
                                        @if($user->role_id === 1)
                                            管理員
                                        @elseif($user->role_id === 2)
                                            網路組教職員
                                        @else
                                            教職員
                                        @endif
                                    </span>
                                </td>
                                <td class="px-6 py-4 flex items-center space-x-2">
                                    @if($user->email !== 'a094789@gmail.com')
                                        <button type="button" class="edit-user-btn text-blue-600 hover:text-blue-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                            </svg>
                                        </button>
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('確定要刪除這個用戶嗎？')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-gray-400 italic text-xs">系統開發者帳號 (受保護)</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $users->appends(request()->except('page'))->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('editUserModal');
            const editBtns = document.querySelectorAll('.edit-user-btn');
            const cancelBtn = document.getElementById('cancelEditBtn');
            const saveBtn = document.getElementById('saveUserBtn');
            const errorDiv = document.getElementById('editError');
            const form = document.getElementById('editUserForm');
            
            // 打開編輯模態框
            editBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const row = this.closest('tr');
                    const userId = row.dataset.userId;
                    const userName = row.dataset.userName;
                    const userEmail = row.dataset.userEmail;
                    const userCreated = row.dataset.userCreated;
                    const isAdmin = row.dataset.userAdmin;
                    
                    // 開發者帳號額外保護 (雖然UI上已隱藏按鈕，但仍需在JS中加入防護)
                    if (userEmail === 'a094789@gmail.com') {
                        alert('系統開發者帳號不允許修改！');
                        return;
                    }
                    
                    // 填充表單數據
                    document.getElementById('editUserId').value = userId;
                    document.getElementById('editName').value = userName;
                    document.getElementById('editEmail').textContent = userEmail;
                    document.getElementById('editCreatedAt').textContent = userCreated;
                    
                    if (isAdmin === '1') {
                        document.getElementById('editIsAdmin1').checked = true;
                    } else {
                        document.getElementById('editIsAdmin0').checked = true;
                    }
                    
                    // 顯示模態框
                    modal.style.display = 'flex';
                    errorDiv.classList.add('hidden');
                });
            });
            
            // 關閉模態框
            cancelBtn.addEventListener('click', function() {
                modal.style.display = 'none';
            });
            
            // 點擊模態框外部關閉
            modal.addEventListener('click', function(event) {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
            
            // 保存變更
            saveBtn.addEventListener('click', function() {
                const userId = document.getElementById('editUserId').value;
                const name = document.getElementById('editName').value;
                let isAdmin = document.querySelector('input[name="is_admin"]:checked')?.value;
                const userEmail = document.getElementById('editEmail').textContent;
                
                // 開發者帳號額外保護
                if (userEmail === 'a094789@gmail.com') {
                    errorDiv.textContent = '系統開發者帳號不允許修改！';
                    errorDiv.classList.remove('hidden');
                    return;
                }
                
                // 檢查必要的值是否存在
                if (!name || isAdmin === undefined) {
                    errorDiv.textContent = '請填寫所有必填欄位';
                    errorDiv.classList.remove('hidden');
                    return;
                }
                
                // 發送AJAX請求
                fetch(`/admin/users/${userId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        name: name,
                        is_admin: isAdmin
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('伺服器回應錯誤');
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // 找到對應的行並更新數據
                        const row = document.querySelector(`tr[data-user-id="${userId}"]`);
                        if (!row) {
                            console.error('找不到對應的用戶行，ID:', userId);
                            location.reload(); // 如果找不到行，則刷新頁面
                            return;
                        }
                        
                        // 更新名稱
                        const nameElement = row.querySelector('.text-sm.font-medium.text-gray-900');
                        if (nameElement) {
                            nameElement.textContent = name;
                            row.dataset.userName = name;
                        }
                        
                        // 更新權限標籤
                        const roleSpan = row.querySelector('td:nth-child(4) span');
                        if (roleSpan) {
                            if (isAdmin === '1') {
                                roleSpan.className = 'px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800';
                                roleSpan.textContent = '管理員';
                                row.dataset.userAdmin = '1';
                            } else {
                                roleSpan.className = 'px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800';
                                roleSpan.textContent = '一般用戶';
                                row.dataset.userAdmin = '0';
                            }
                        }
                        
                        // 顯示成功訊息
                        const successMsg = document.createElement('div');
                        successMsg.className = 'mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg';
                        successMsg.setAttribute('role', 'alert');
                        successMsg.textContent = data.message || '已更新使用者資料';
                        
                        // 如果已經有成功訊息，先移除
                        const existingMsg = document.querySelector('.text-green-700.bg-green-100');
                        if (existingMsg) {
                            existingMsg.remove();
                        }
                        
                        // 插入成功訊息在頁面頂部
                        const contentDiv = document.querySelector('.bg-white.overflow-hidden.shadow-xl.sm\\:rounded-lg.p-6');
                        if (contentDiv) {
                            contentDiv.insertBefore(successMsg, contentDiv.firstChild);
                        }
                        
                        // 3秒後移除成功訊息
                        setTimeout(() => {
                            if (successMsg.parentNode) {
                                successMsg.remove();
                            }
                        }, 3000);
                        
                        // 關閉模態框
                        modal.style.display = 'none';
                    } else {
                        // 顯示錯誤消息
                        errorDiv.textContent = data.message || '更新失敗';
                        errorDiv.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    errorDiv.textContent = '編輯使用者資料時發生錯誤，請重試';
                    errorDiv.classList.remove('hidden');
                });
            });
        });
    </script>
</x-app-layout>