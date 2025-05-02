<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('已刪除的短網址') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-1">管理已刪除的短網址</h3>
                    <p class="text-sm text-gray-600 mb-6">在這裡可以管理所有已刪除的短網址，可以選擇復原或永久刪除。短網址將在30天後自動永久刪除。</p>

                    <!-- 篩選狀態摘要 -->
                    @if(!empty($keyword) || !empty($date_from) || !empty($date_to) || !empty($creator))
                        <div class="mb-4 text-sm">
                            <p class="text-gray-600">目前篩選條件：</p>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @if(!empty($keyword))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        關鍵字: {{ $keyword }}
                                    </span>
                                @endif
                                
                                @if(!empty($date_from) || !empty($date_to))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        刪除日期: 
                                        {{ !empty($date_from) ? $date_from : '最早' }} 
                                        - 
                                        {{ !empty($date_to) ? $date_to : '最新' }}
                                    </span>
                                @endif

                                @if(!empty($creator) && Auth::user()->isAdmin())
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        建立者: {{ $users->firstWhere('id', $creator)->name }}
                                    </span>
                                @endif
                                
                                <a href="{{ route('short-urls.trashed') }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    清除所有篩選
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- 篩選功能 (可摺疊) -->
                    <div x-data="{ showFilters: {{ (!empty($keyword) || !empty($date_from) || !empty($date_to)) ? 'true' : 'false' }} }" class="mb-6">
                        <!-- 篩選切換按鈕 -->
                        <button @click="showFilters = !showFilters" type="button" class="flex items-center mb-3 text-gray-600 hover:text-gray-900">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            <span class="text-sm font-medium" x-text="showFilters ? '收起篩選' : '展開篩選'">展開篩選</span>
                            <svg class="w-4 h-4 ml-1 transition-transform duration-200" :class="showFilters ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            @if(!empty($keyword) || !empty($date_from) || !empty($date_to))
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                    篩選中
                                </span>
                            @endif
                        </button>

                        <!-- 篩選表單 -->
                        <div x-show="showFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-4 bg-gray-50 rounded-lg">
                            <form method="GET" action="{{ route('short-urls.trashed') }}" class="space-y-6">
                                <div class="space-y-6">
                                    <!-- 關鍵字和建立者 -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <!-- 關鍵字搜尋 -->
                                        <div>
                                            <label for="keyword" class="block text-sm font-medium text-gray-700 mb-1">關鍵字搜尋</label>
                                            <input type="text" id="keyword" name="keyword" value="{{ request('keyword') }}" placeholder="輸入原網址或短網址代碼" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        </div>
                                        
                                        <!-- 建立者篩選 -->
                                        @if(Auth::user()->isAdmin())
                                        <div x-data="{ 
                                            open: false,
                                            search: '',
                                            selectedId: '{{ request('creator') }}',
                                            selectedName: '{{ $users->where('id', request('creator'))->first()?->name ?? '' }}',
                                            users: {{ Js::from($users) }},
                                            get filteredUsers() {
                                                if (!this.search) return this.users;
                                                return this.users.filter(user => 
                                                    user.name.toLowerCase().includes(this.search.toLowerCase())
                                                );
                                            }
                                        }">
                                            <label for="creator" class="block text-sm font-medium text-gray-700 mb-1">建立者</label>
                                            <div class="relative">
                                                <input
                                                    type="text"
                                                    x-model="search"
                                                    x-on:focus="open = true"
                                                    x-on:click="open = true"
                                                    :placeholder="selectedName || '搜尋建立者...'"
                                                    class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm"
                                                >
                                                <input type="hidden" name="creator" x-model="selectedId">
                                                
                                                <!-- 下拉選單 -->
                                                <div
                                                    x-show="open"
                                                    x-on:click.away="open = false"
                                                    class="absolute z-10 w-full mt-1 bg-white border border-gray-300 rounded-md shadow-lg max-h-60 overflow-y-auto"
                                                    style="display: none;"
                                                >
                                                    <!-- 清除選擇 -->
                                                    <div
                                                        x-on:click="selectedId = ''; selectedName = ''; search = ''; open = false"
                                                        class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                                                    >
                                                        全部使用者
                                                    </div>
                                                    
                                                    <!-- 使用者列表 -->
                                                    <template x-for="user in filteredUsers" :key="user.id">
                                                        <div
                                                            x-on:click="selectedId = user.id; selectedName = user.name; search = user.name; open = false"
                                                            class="px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 cursor-pointer"
                                                            :class="{'bg-gray-50': selectedId == user.id}"
                                                        >
                                                            <span x-text="user.name"></span>
                                                        </div>
                                                    </template>
                                                    
                                                    <!-- 無結果提示 -->
                                                    <div
                                                        x-show="filteredUsers.length === 0"
                                                        class="px-4 py-2 text-sm text-gray-500"
                                                    >
                                                        找不到符合的使用者
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    
                                    <!-- 日期範圍 -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">開始日期</label>
                                            <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">結束日期</label>
                                            <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- 每頁顯示筆數 (作為篩選表單的一部分) -->
                                <input type="hidden" name="per_page" value="{{ request('per_page', 10) }}">
                                
                                <!-- 排序設置 (當篩選表單提交時保留排序設置) -->
                                @if(request('sort_by'))
                                <input type="hidden" name="sort_by" value="{{ request('sort_by') }}">
                                @endif
                                @if(request('sort_order'))
                                <input type="hidden" name="sort_order" value="{{ request('sort_order') }}">
                                @endif
                                
                                <!-- 篩選按鈕 -->
                                <div class="flex items-center space-x-4">
                                    <!-- 提交按鈕 -->
                                    <x-primary-button>
                                        {{ __('篩選') }}
                                    </x-primary-button>
                                    
                                    <!-- 重置按鈕 -->
                                    <a href="{{ route('short-urls.trashed') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                        {{ __('重置') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- 每頁顯示筆數選擇 -->
                    <div class="mb-4 flex justify-end">
                        <form method="GET" action="{{ route('short-urls.trashed') }}" class="flex items-center">
                            <!-- 保留所有其他參數 -->
                            @foreach(request()->except(['page', 'per_page']) as $param => $value)
                                <input type="hidden" name="{{ $param }}" value="{{ $value }}">
                            @endforeach
                            
                            <label for="per_page" class="mr-2 text-sm text-gray-600">每頁顯示：</label>
                            <select id="per_page" name="per_page" onchange="this.form.submit()" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="5" {{ request('per_page', 10) == 5 ? 'selected' : '' }}>5 筆</option>
                                <option value="10" {{ request('per_page', 10) == 10 ? 'selected' : '' }}>10 筆</option>
                                <option value="20" {{ request('per_page', 10) == 20 ? 'selected' : '' }}>20 筆</option>
                                <option value="30" {{ request('per_page', 10) == 30 ? 'selected' : '' }}>30 筆</option>
                                <option value="9999" {{ request('per_page', 10) == 9999 ? 'selected' : '' }}>全部顯示</option>
                            </select>
                        </form>
                    </div>

                    <!-- 桌面版表格視圖 -->
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 w-[35%]">原網址</th>
                                    <th scope="col" class="px-6 py-3 w-[15%]">短網址代碼</th>
                                    <th scope="col" class="px-6 py-3 w-[15%]">
                                        <div class="flex items-center">
                                            建立者
                                            <a href="{{ route('short-urls.trashed', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'user_name', 'sort_order' => (request('sort_by') === 'user_name' && request('sort_order') === 'asc') ? 'desc' : 'asc'])) }}" class="ml-1">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('sort_by') === 'user_name')
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
                                    <th scope="col" class="px-6 py-3 w-[15%]">
                                        <div class="flex items-center">
                                            刪除時間
                                            <a href="{{ route('short-urls.trashed', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'deleted_at', 'sort_order' => (request('sort_by') === 'deleted_at' && request('sort_order') === 'asc') ? 'desc' : 'asc'])) }}" class="ml-1">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('sort_by') === 'deleted_at')
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
                                    <th scope="col" class="px-6 py-3 w-[15%]">
                                        <div class="flex items-center">
                                            自動清理時間
                                            <a href="{{ route('short-urls.trashed', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'auto_cleanup_at', 'sort_order' => (request('sort_by') === 'auto_cleanup_at' && request('sort_order') === 'asc') ? 'desc' : 'asc'])) }}" class="ml-1">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('sort_by') === 'auto_cleanup_at')
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
                                    <th scope="col" class="px-6 py-3 w-[5%]">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($trashedUrls as $url)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <a href="{{ $url->original_url }}" target="_blank" class="text-gray-900 hover:text-red-600 truncate max-w-[150px] block" title="{{ $url->original_url }}">
                                            {{ Str::limit($url->original_url, 20) }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $url->short_code }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $url->user->name }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $url->deleted_at->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                        {{ $url->deleted_at->addDays(30)->format('Y-m-d H:i:s') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex justify space-x-2">
                                            <form action="{{ route('short-urls.restore', $url) }}" method="POST" class="inline restore-form">
                                                @csrf
                                                <button type="submit" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
                                                </button>
                                            </form>
                                            <form action="{{ route('short-urls.force-delete', $url) }}" method="POST" class="inline force-delete-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" onclick="return confirm('確定要永久刪除這個短網址嗎？此操作無法復原！')" 
                                                    class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- 分頁 -->
                    <div class="mt-6">
                        {{ $trashedUrls->appends(request()->except('page'))->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // 處理復原表單提交
            document.querySelectorAll('.restore-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    fetch(this.action, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                            alert(data.message);
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('復原失敗，請稍後再試');
                    });
                });
            });

            // 處理永久刪除表單提交
            document.querySelectorAll('.force-delete-form').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    if (!confirm('確定要永久刪除這個短網址嗎？此操作無法復原！')) {
                        return;
                    }

                    fetch(this.action, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        body: JSON.stringify({})
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.message) {
                            alert(data.message);
                            window.location.reload();
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('永久刪除失敗，請稍後再試');
                    });
                });
            });
        });
    </script>
    @endpush
</x-app-layout>
