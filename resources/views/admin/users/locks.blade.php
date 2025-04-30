<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('用戶鎖定管理') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- 顯示成功訊息 -->
                    @if(session('success'))
                        <div class="mb-4 p-4 text-sm text-green-700 bg-green-100 rounded-lg" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif

                    <!-- 顯示錯誤訊息 -->
                    @if(session('error'))
                        <div class="mb-4 p-4 text-sm text-red-700 bg-red-100 rounded-lg" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h3 class="text-lg font-medium text-gray-900 mb-1">被鎖定的用戶</h3>
                            <p class="text-sm text-gray-600">這些用戶因為多次登錄失敗而被臨時鎖定。</p>
                        </div>
                        <a href="{{ route('admin.users.index') }}" class="text-sm text-blue-600 hover:text-blue-900">返回用戶管理</a>
                    </div>

                    <!-- 篩選狀態摘要 -->
                    @if(!empty($emailFilter) || !empty($ipFilter))
                        <div class="mb-4 text-sm">
                            <p class="text-gray-600">目前篩選條件：</p>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @if(!empty($emailFilter))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Email: {{ $emailFilter }}
                                    </span>
                                @endif
                                
                                @if(!empty($ipFilter))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        IP: {{ $ipFilter }}
                                    </span>
                                @endif
                                
                                <a href="{{ route('admin.user-locks.index') }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    清除所有篩選
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- 篩選功能 (可摺疊) -->
                    <div x-data="{ showFilters: {{ (!empty($emailFilter) || !empty($ipFilter)) ? 'true' : 'false' }} }" class="mb-6">
                        <!-- 篩選切換按鈕 -->
                        <button @click="showFilters = !showFilters" type="button" class="flex items-center mb-3 text-gray-600 hover:text-gray-900">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            <span class="text-sm font-medium" x-text="showFilters ? '收起篩選' : '展開篩選'">展開篩選</span>
                            <svg class="w-4 h-4 ml-1 transition-transform duration-200" :class="showFilters ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            @if(!empty($emailFilter) || !empty($ipFilter))
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                    篩選中
                                </span>
                            @endif
                        </button>

                        <!-- 篩選表單 -->
                        <div x-show="showFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-4 bg-gray-50 rounded-lg">
                            <form method="GET" action="{{ route('admin.user-locks.index') }}" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <!-- Email 搜尋 -->
                                    <div>
                                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email 關鍵字</label>
                                        <input type="text" id="email" name="email" value="{{ $emailFilter }}" placeholder="輸入電子郵件關鍵字" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    </div>
                                    
                                    <!-- IP 篩選 -->
                                    <div>
                                        <label for="ip" class="block text-sm font-medium text-gray-700 mb-1">IP 位址</label>
                                        <input type="text" id="ip" name="ip" value="{{ $ipFilter }}" placeholder="輸入IP位址" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    </div>
                                </div>
                                
                                <!-- 每頁顯示筆數 (作為篩選表單的一部分) -->
                                <input type="hidden" name="per_page" value="{{ $perPage }}">
                                
                                <!-- 排序設置 (當篩選表單提交時保留排序設置) -->
                                @if(request('sort_by'))
                                <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                                @endif
                                @if(request('sort_order'))
                                <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                                @endif
                                
                                <!-- 篩選按鈕 -->
                                <div class="flex items-center space-x-4 mt-6">
                                    <!-- 提交按鈕 -->
                                    <x-primary-button>
                                        {{ __('篩選') }}
                                    </x-primary-button>
                                    
                                    <!-- 重置按鈕 -->
                                    <a href="{{ route('admin.user-locks.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                        {{ __('重置') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- 每頁顯示筆數選擇 -->
                    <div class="mb-4 flex justify-end">
                        <form method="GET" action="{{ route('admin.user-locks.index') }}" class="flex items-center">
                            <!-- 保留所有其他參數 -->
                            @foreach(request()->except(['page', 'per_page']) as $param => $value)
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endforeach
                            
                            <label for="per_page" class="mr-2 text-sm text-gray-600">每頁顯示：</label>
                            <select id="per_page" name="per_page" onchange="this.form.submit()" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="5" {{ $perPage == 5 ? 'selected' : '' }}>5 筆</option>
                                <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 筆</option>
                                <option value="20" {{ $perPage == 20 ? 'selected' : '' }}>20 筆</option>
                                <option value="30" {{ $perPage == 30 ? 'selected' : '' }}>30 筆</option>
                                <option value="9999" {{ $perPage == 9999 ? 'selected' : '' }}>全部顯示</option>
                            </select>
                        </form>
                    </div>

                    <div class="overflow-x-auto">
                        <!-- 手機版排序選項 -->
                        <div class="md:hidden mb-4">
                            <label for="mobile-sort" class="block text-sm font-medium text-gray-700 mb-1">排序方式：</label>
                            <select id="mobile-sort" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="window.location.href=this.value">
                                <option value="{{ route('admin.user-locks.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'available_in', 'sort_order' => 'desc'])) }}" {{ request('sort_by', 'available_in') === 'available_in' && request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>
                                    剩餘鎖定時間（長 → 短）
                                </option>
                                <option value="{{ route('admin.user-locks.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'available_in', 'sort_order' => 'asc'])) }}" {{ request('sort_by') === 'available_in' && request('sort_order') === 'asc' ? 'selected' : '' }}>
                                    剩餘鎖定時間（短 → 長）
                                </option>
                                <option value="{{ route('admin.user-locks.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'attempts', 'sort_order' => 'desc'])) }}" {{ request('sort_by') === 'attempts' && request('sort_order') === 'desc' ? 'selected' : '' }}>
                                    失敗嘗試次數（多 → 少）
                                </option>
                                <option value="{{ route('admin.user-locks.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'attempts', 'sort_order' => 'asc'])) }}" {{ request('sort_by') === 'attempts' && request('sort_order') === 'asc' ? 'selected' : '' }}>
                                    失敗嘗試次數（少 → 多）
                                </option>
                                <option value="{{ route('admin.user-locks.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'email', 'sort_order' => 'asc'])) }}" {{ request('sort_by') === 'email' && request('sort_order') === 'asc' ? 'selected' : '' }}>
                                    Email（A → Z）
                                </option>
                                <option value="{{ route('admin.user-locks.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'email', 'sort_order' => 'desc'])) }}" {{ request('sort_by') === 'email' && request('sort_order') === 'desc' ? 'selected' : '' }}>
                                    Email（Z → A）
                                </option>
                            </select>
                        </div>

                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3">
                                        <div class="flex items-center">
                                            Email
                                            <a href="{{ route('admin.user-locks.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'email', 'sort_order' => (request('sort_by') === 'email' && request('sort_order') === 'asc') ? 'desc' : 'asc'])) }}" class="ml-1">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('sort_by') === 'email')
                                                        @if(request('sort_order') === 'asc')
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        @endif
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                    @endif
                                                </svg>
                                            </a>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        <div class="flex items-center">
                                            IP 地址
                                            <a href="{{ route('admin.user-locks.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'ip', 'sort_order' => (request('sort_by') === 'ip' && request('sort_order') === 'asc') ? 'desc' : 'asc'])) }}" class="ml-1">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('sort_by') === 'ip')
                                                        @if(request('sort_order') === 'asc')
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        @endif
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                    @endif
                                                </svg>
                                            </a>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        <div class="flex items-center">
                                            失敗嘗試次數
                                            <a href="{{ route('admin.user-locks.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'attempts', 'sort_order' => (request('sort_by') === 'attempts' && request('sort_order') === 'asc') ? 'desc' : 'asc'])) }}" class="ml-1">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('sort_by') === 'attempts')
                                                        @if(request('sort_order') === 'asc')
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        @endif
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                    @endif
                                                </svg>
                                            </a>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        <div class="flex items-center">
                                            鎖定解除剩餘時間
                                            <a href="{{ route('admin.user-locks.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'available_in', 'sort_order' => (request('sort_by') === 'available_in' && request('sort_order') === 'asc') ? 'desc' : 'asc'])) }}" class="ml-1">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('sort_by') === 'available_in')
                                                        @if(request('sort_order') === 'asc')
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"></path>
                                                        @else
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                                        @endif
                                                    @else
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"></path>
                                                    @endif
                                                </svg>
                                            </a>
                                        </div>
                                    </th>
                                    <th scope="col" class="px-6 py-3">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lockedUsers as $user)
                                    <tr class="bg-white border-b hover:bg-gray-50">
                                        <td class="px-6 py-4">{{ $user['email'] }}</td>
                                        <td class="px-6 py-4">{{ $user['ip'] }}</td>
                                        <td class="px-6 py-4">{{ $user['attempts'] }}</td>
                                        <td class="px-6 py-4">
                                            @if($user['available_in'] > 0)
                                                <span class="text-red-600">{{ ceil($user['available_in'] / 3600) }} 小時</span>
                                            @else
                                                <span class="text-green-600">已解除</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            <form method="POST" action="{{ route('admin.user-locks.unlock') }}">
                                                @csrf
                                                <input type="hidden" name="key" value="{{ $user['key'] }}">
                                                <button type="submit" class="font-medium text-blue-600 hover:text-blue-900">
                                                    解除鎖定
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="bg-white border-b">
                                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                                            沒有被鎖定的用戶
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- 分頁導航 -->
                    <div class="mt-6">
                        {{ $lockedUsers->appends(request()->except('page'))->links() }}
                    </div>

                    <div class="mt-8 bg-gray-50 p-4 rounded-lg">
                        <h4 class="font-medium text-gray-900 mb-2">關於登入鎖定機制</h4>
                        <p class="text-sm text-gray-600 mb-2">Laravel 的登入嘗試限制是一種安全機制，用於防止惡意用戶對系統進行暴力破解攻擊。</p>
                        <ul class="list-disc list-inside text-sm text-gray-600 ml-2">
                            <li class="mb-1">用戶連續 5 次輸入錯誤密碼後，將被鎖定 24 小時</li>
                            <li class="mb-1">鎖定是基於用戶的 Email 和 IP 地址組合</li>
                            <li class="mb-1">您可以手動解除用戶的鎖定，或等待鎖定時間結束</li>
                            <li>解除鎖定後，用戶可以立即嘗試登入</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 自動刷新頁面，每30秒更新一次
            setTimeout(function() {
                window.location.reload();
            }, 30000);
        });
    </script>
    
    <!-- 引入 AlpineJS -->
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</x-app-layout> 