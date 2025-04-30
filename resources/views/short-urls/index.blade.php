<x-app-layout>
    <x-slot name="header">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('管理短網址') }}
            </h2>
    </x-slot>

    <div x-data="{ 
            showModal: false, 
            qrSrc: '',
            showQrCode(url) {
                this.qrSrc = url;
                window.showQrCode(url);
            }
        }" class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-1">管理短網址</h3>
                    <p class="text-sm text-gray-600 mb-6">在這裡可以管理所有已建立的短網址，查看點擊次數及刪除短網址。</p>

                    <!-- 篩選狀態摘要 -->
                    @if(!empty($keyword) || !empty($status) || !empty($date_from) || !empty($date_to))
                        <div class="mb-4 text-sm">
                            <p class="text-gray-600">目前篩選條件：</p>
                            <div class="flex flex-wrap gap-2 mt-1">
                                @if(!empty($keyword))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        關鍵字: {{ $keyword }}
                                    </span>
                                @endif
                                
                                @if(!empty($status))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        狀態: 
                                        @if($status == 'active')
                                            有效
                                        @elseif($status == 'expired')
                                            已過期
                                        @elseif($status == 'permanent')
                                            永久有效
                                        @endif
                                    </span>
                                @endif
                                
                                @if(!empty($date_from) || !empty($date_to))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        日期: 
                                        {{ !empty($date_from) ? $date_from : '最早' }} 
                                        - 
                                        {{ !empty($date_to) ? $date_to : '最新' }}
                                    </span>
                                @endif
                                
                                <a href="{{ route('short-urls.index') }}" class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 hover:bg-red-200">
                                    <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    清除所有篩選
                                </a>
                            </div>
                        </div>
                    @endif

                    <!-- 篩選功能 (可摺疊) -->
                    <div x-data="{ showFilters: {{ (!empty($keyword) || !empty($status) || !empty($date_from) || !empty($date_to)) ? 'true' : 'false' }} }" class="mb-6">
                        <!-- 篩選切換按鈕 -->
                        <button @click="showFilters = !showFilters" type="button" class="flex items-center mb-3 text-gray-600 hover:text-gray-900">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                            </svg>
                            <span class="text-sm font-medium" x-text="showFilters ? '收起篩選' : '展開篩選'">展開篩選</span>
                            <svg class="w-4 h-4 ml-1 transition-transform duration-200" :class="showFilters ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                            </svg>
                            @if(!empty($keyword) || !empty($status) || !empty($date_from) || !empty($date_to))
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-indigo-100 text-indigo-800">
                                    篩選中
                                </span>
                            @endif
                        </button>

                        <!-- 篩選表單 -->
                        <div x-show="showFilters" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform -translate-y-2" x-transition:enter-end="opacity-100 transform translate-y-0" class="p-4 bg-gray-50 rounded-lg">
                            <form method="GET" action="{{ route('short-urls.index') }}" class="space-y-4">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                    <!-- 關鍵字搜尋 -->
                                    <div>
                                        <label for="keyword" class="block text-sm font-medium text-gray-700 mb-1">關鍵字搜尋</label>
                                        <input type="text" id="keyword" name="keyword" value="{{ $keyword }}" placeholder="輸入原網址或短網址代碼" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                    </div>
                                    
                                    <!-- 過期狀態篩選 -->
                                    <div>
                                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1">過期狀態</label>
                                        <select id="status" name="status" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                            <option value="">全部</option>
                                            <option value="active" {{ $status == 'active' ? 'selected' : '' }}>有效</option>
                                            <option value="expired" {{ $status == 'expired' ? 'selected' : '' }}>已過期</option>
                                            <option value="permanent" {{ $status == 'permanent' ? 'selected' : '' }}>永久有效</option>
                                        </select>
                                    </div>
                                    
                                    <!-- 日期範圍 -->
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">建立日期範圍</label>
                                        <div class="grid grid-cols-2 gap-2">
                                            <div>
                                                <input type="date" id="date_from" name="date_from" value="{{ $date_from }}" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="開始日期">
                                            </div>
                                            <div>
                                                <input type="date" id="date_to" name="date_to" value="{{ $date_to }}" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" placeholder="結束日期">
                                            </div>
                                        </div>
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
                                    <a href="{{ route('short-urls.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                        {{ __('重置') }}
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- 每頁顯示筆數選擇 -->
                    <div class="mb-4 flex justify-end">
                        <form method="GET" action="{{ route('short-urls.index') }}" class="flex items-center">
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

                    <!-- 手機版卡片視圖 -->
                    <div class="md:hidden space-y-4">
                        <!-- 排序選項（僅手機版顯示） -->
                        <div class="mb-4">
                            <label for="mobile-sort" class="block text-sm font-medium text-gray-700 mb-1">排序方式：</label>
                            <select id="mobile-sort" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm" onchange="window.location.href=this.value">
                                <option value="{{ route('short-urls.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'created_at', 'sort_order' => 'desc'])) }}" {{ request('sort_by', 'created_at') === 'created_at' && request('sort_order', 'desc') === 'desc' ? 'selected' : '' }}>
                                    最新建立時間
                                </option>
                                <option value="{{ route('short-urls.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'created_at', 'sort_order' => 'asc'])) }}" {{ request('sort_by') === 'created_at' && request('sort_order') === 'asc' ? 'selected' : '' }}>
                                    最舊建立時間
                                </option>
                                <option value="{{ route('short-urls.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'clicks', 'sort_order' => 'desc'])) }}" {{ request('sort_by') === 'clicks' && request('sort_order') === 'desc' ? 'selected' : '' }}>
                                    點擊次數（多 → 少）
                                </option>
                                <option value="{{ route('short-urls.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'clicks', 'sort_order' => 'asc'])) }}" {{ request('sort_by') === 'clicks' && request('sort_order') === 'asc' ? 'selected' : '' }}>
                                    點擊次數（少 → 多）
                                </option>
                                <option value="{{ route('short-urls.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'expires_at', 'sort_order' => 'desc'])) }}" {{ request('sort_by') === 'expires_at' && request('sort_order') === 'desc' ? 'selected' : '' }}>
                                    過期時間（近期過期優先）
                                </option>
                                <option value="{{ route('short-urls.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'expires_at', 'sort_order' => 'asc'])) }}" {{ request('sort_by') === 'expires_at' && request('sort_order') === 'asc' ? 'selected' : '' }}>
                                    過期時間（永久有效優先）
                                </option>
                            </select>
                        </div>
                        
                        @foreach($urls as $url)
                        <div x-data="{ expanded: false }" class="bg-white border rounded-lg shadow-sm overflow-hidden">
                            <div class="p-4" @click="expanded = !expanded">
                                <div class="flex justify-between items-center">
                                    <div>
                                        <label class="text-xs text-gray-500">短網址</label>
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ $url->short_url }}" target="_blank" class="text-red-600 hover:text-red-800" @click.stop>
                                                {{ $url->short_code }}
                                            </a>
                                            <button onclick="copyToClipboard('{{ $url->short_url }}')" @click.stop class="text-gray-400 hover:text-gray-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="flex items-center">
                                        <span class="mr-3 text-sm">{{ $url->clicks }} 次點擊</span>
                                        <svg class="w-5 h-5 text-gray-500 transition-transform" :class="expanded ? 'transform rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                            <div x-show="expanded" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 transform scale-95" x-transition:enter-end="opacity-100 transform scale-100" class="px-4 pb-4 pt-2 border-t border-gray-100">
                                <div class="mb-3">
                                    <label class="text-xs text-gray-500">原網址</label>
                                    <a href="{{ $url->original_url }}" target="_blank" class="block text-blue-600 hover:text-blue-800 truncate" title="{{ $url->original_url }}">
                                        {{ Str::limit($url->original_url, 30) }}
                                    </a>
                                </div>
                                <div class="grid grid-cols-2 gap-3 mb-3">
                                    <div>
                                        <label class="text-xs text-gray-500">建立者</label>
                                        <p>{{ $url->user->name }}</p>
                                    </div>
                                    <div>
                                        <label class="text-xs text-gray-500">建立時間</label>
                                        <p class="text-sm text-gray-900">{{ $url->created_at->format('Y-m-d H:i:s') }}</p>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label class="text-xs text-gray-500">過期時間</label>
                                    <p class="text-sm {{ $url->expires_at && $url->expires_at->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                                        {{ $url->expires_at ? $url->expires_at->format('Y-m-d H:i:s') : '永久有效' }}
                                    </p>
                                </div>
                                <div class="flex justify-between items-center">
                                    <button type="button"
                                        @click="showQrCode('{{ route('short-urls.qrcode', $url->id) }}')"
                                        class="text-gray-600 hover:text-gray-900 p-2 rounded-full hover:bg-gray-100">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                        </svg>
                                    </button>
                                    <div class="flex items-center space-x-4">
                                        <a href="/short-urls/{{ $url->id }}/clicks" class="text-blue-600 hover:text-blue-900">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </a>
                                        <form method="POST" action="{{ route('short-urls.destroy', $url) }}" class="inline-block">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('確定要刪除這個短網址嗎？')">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- 桌面版表格視圖 -->
                    <div class="hidden md:block">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 w-[12%]">原網址</th>
                                    <th scope="col" class="px-6 py-3 w-[10%]">短網址</th>
                                    <th scope="col" class="px-6 py-3 w-[7%]">QR CODE</th>
                                    <th scope="col" class="px-6 py-3 w-[7%]">
                                        <div class="flex items-center">
                                            點擊次數
                                            <a href="{{ route('short-urls.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'clicks', 'sort_order' => (request('sort_by') === 'clicks' && request('sort_order') === 'asc') ? 'desc' : 'asc'])) }}" class="ml-1">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('sort_by') === 'clicks')
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
                                    <th scope="col" class="px-6 py-3 w-[12%]">
                                        <div class="flex items-center">
                                            建立時間
                                            <a href="{{ route('short-urls.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'created_at', 'sort_order' => (request('sort_by') === 'created_at' && request('sort_order') === 'asc') ? 'desc' : 'asc'])) }}" class="ml-1">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('sort_by') === 'created_at')
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
                                    <th scope="col" class="px-6 py-3 w-[12%]">
                                        <div class="flex items-center">
                                            過期時間
                                            <a href="{{ route('short-urls.index', array_merge(request()->except(['sort_by', 'sort_order']), ['sort_by' => 'expires_at', 'sort_order' => (request('sort_by') === 'expires_at' && request('sort_order') === 'asc') ? 'desc' : 'asc'])) }}" class="ml-1">
                                                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    @if(request('sort_by') === 'expires_at')
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
                                    <th scope="col" class="px-6 py-3 w-[20%]">建立者</th>
                                    <th scope="col" class="px-6 py-3 w-[10%]">操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($urls as $url)
                                <tr class="bg-white border-b hover:bg-gray-50">
                                    <td class="px-6 py-4">
                                        <a href="{{ $url->original_url }}" target="_blank" class="text-gray-900 hover:text-red-600 truncate max-w-[150px] block" title="{{ $url->original_url }}">
                                            {{ Str::limit($url->original_url, 20) }}
                                        </a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ $url->short_url }}" target="_blank" class="text-red-600 hover:text-red-900">
                                                {{ $url->short_code }}
                                            </a>
                                            <button onclick="copyToClipboard('{{ $url->short_url }}')" @click.stop class="text-gray-400 hover:text-gray-600">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path>
                                                </svg>
                                            </button>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <button type="button"
                                            @click="showQrCode('{{ route('short-urls.qrcode', $url->id) }}')"
                                            class="text-gray-600 hover:text-gray-900 p-2 rounded-full hover:bg-gray-100">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z" />
                                            </svg>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4">{{ $url->clicks }}</td>
                                    <td class="px-6 py-4">{{ $url->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td class="px-6 py-4">
                                        <span class="{{ $url->expires_at && $url->expires_at->isPast() ? 'text-red-600' : 'text-gray-900' }}">
                                            {{ $url->expires_at ? $url->expires_at->format('Y-m-d H:i:s') : '永久有效' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-normal break-words">
                                        <span class="inline-block">{{ $url->user->name }}</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center space-x-4">
                                            <a href="/short-urls/{{ $url->id }}/clicks" class="text-blue-600 hover:text-blue-900">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                            <form method="POST" action="{{ route('short-urls.destroy', $url) }}" class="inline-block">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 hover:text-red-900" onclick="return confirm('確定要刪除這個短網址嗎？')">
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
                        
                        <!-- 分頁導航 -->
                        <div class="mt-6">
                            {{ $urls->appends(request()->except('page'))->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- QR Code Modal -->
        <x-modal name="qrcode-modal" :show="false" maxWidth="sm">
            <div class="p-6">
                <h2 class="text-lg font-medium text-gray-900 mb-4">
                    QR Code 設定
                </h2>
                
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Logo 選項
                    </label>
                    <select id="logoOption" class="w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                        <option value="default">使用預設 Logo</option>
                        <option value="none">不使用 Logo</option>
                        <option value="custom">上傳自訂 Logo</option>
                    </select>
                </div>

                <div id="customLogoUpload" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        上傳 Logo
                    </label>
                    <input type="file" id="logoFile" accept="image/png, image/jpeg, image/jpg, image/gif, image/svg+xml" class="w-full">
                    <p class="mt-1 text-sm text-gray-500">支援 PNG、JPG、GIF、SVG 等圖片格式，建議使用透明背景的圖片</p>
                </div>

                <div class="relative bg-gray-100 rounded-lg overflow-hidden mb-4">
                    <img id="qrcode-image" src="" alt="QR Code" class="w-full">
                </div>

                <div class="flex justify-end space-x-4 mt-6">
                    <div class="mr-auto">
                        <x-secondary-button @click="$dispatch('close')">
                            關閉
                        </x-secondary-button>
                    </div>
                    <x-primary-button id="downloadQrCode" class="bg-red-600 hover:bg-red-700">
                        下載 QR Code
                    </x-primary-button>
                </div>
            </div>
        </x-modal>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        function copyToClipboard(text) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('已複製到剪貼簿');
                });
            } else {
                const textArea = document.createElement('textarea');
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    alert('已複製到剪貼簿');
                } catch (err) {
                    console.error('複製失敗:', err);
                }
                document.body.removeChild(textArea);
            }
        }

        // 改為全局函數，便於 Alpine 調用
        window.showQrCode = function(qrcodeUrl) {
            const modal = document.getElementById('qrcode-modal');
            const qrcodeImage = document.getElementById('qrcode-image');
            const logoOption = document.getElementById('logoOption');
            const customLogoUpload = document.getElementById('customLogoUpload');
            const logoFile = document.getElementById('logoFile');
            const downloadButton = document.getElementById('downloadQrCode');
            
            // 顯示模態框
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'qrcode-modal' }));
            
            // 監聽 logo 選項變更
            logoOption.addEventListener('change', function() {
                if (this.value === 'custom') {
                    customLogoUpload.classList.remove('hidden');
                } else {
                    customLogoUpload.classList.add('hidden');
                }
                updateQrCode();
            });

            // 監聽檔案上傳
            logoFile.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file && !file.type.startsWith('image/')) {
                    alert('請上傳圖片檔案');
                    e.target.value = '';
                    return;
                }
                updateQrCode();
            });

            // 更新 QR Code
            async function updateQrCode() {
                const qrcodeImage = document.getElementById('qrcode-image');
                
                try {
                    // 顯示載入中的提示
                    qrcodeImage.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2YzZjRmNiIvPjx0ZXh0IHg9IjEwMCIgeT0iMTAwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiM5Y2EzYWYiPuizh+aWmeS4rS4uLjwvdGV4dD48L3N2Zz4=';

                    // 建立 FormData 物件用於上傳
                    const formData = new FormData();
                    formData.append('logo_option', logoOption.value);
                    
                    // 如果是自訂 logo 且有選擇檔案，則添加到表單
                    if (logoOption.value === 'custom' && logoFile.files[0]) {
                        const file = logoFile.files[0];
                        
                        // 檢查檔案大小
                        if (file.size > 1024 * 1024) { // 1MB
                            alert('圖片檔案過大，請選擇小於 1MB 的圖片');
                            return;
                        }
                        
                        // 直接添加檔案到 FormData
                        formData.append('custom_logo', file);
                    }

                    // 使用 POST 請求上傳資料
                    const response = await fetch(qrcodeUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    });
                    
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    
                    const blob = await response.blob();
                    qrcodeImage.src = URL.createObjectURL(blob);
                } catch (error) {
                    console.error('Error generating QR code:', error);
                    qrcodeImage.src = 'data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48cmVjdCB3aWR0aD0iMjAwIiBoZWlnaHQ9IjIwMCIgZmlsbD0iI2ZlZTJlMiIvPjx0ZXh0IHg9IjEwMCIgeT0iMTAwIiBmb250LWZhbWlseT0iQXJpYWwiIGZvbnQtc2l6ZT0iMTIiIHRleHQtYW5jaG9yPSJtaWRkbGUiIGZpbGw9IiNlZjQ0NDQiPueUn+aIkOWksei0pTwvdGV4dD48L3N2Zz4=';
                    alert('生成 QR Code 時發生錯誤，請重試');
                }
            }

            // 初始載入 QR Code
            updateQrCode();

            // 下載按鈕點擊事件
            downloadButton.onclick = function() {
                // 基本下載 URL
                let downloadUrl = qrcodeUrl.replace('/qrcode', '/qrcode/download');
                
                // 如果選擇自訂 logo 且有上傳檔案，需要先上傳文件再下載
                if (logoOption.value === 'custom' && logoFile.files[0]) {
                    // 檢查檔案大小
                    const file = logoFile.files[0];
                    if (file.size > 1024 * 1024) { // 1MB
                        alert('圖片檔案過大，請選擇小於 1MB 的圖片');
                        return;
                    }
                    
                    // 建立 FormData 物件用於上傳
                    const formData = new FormData();
                    formData.append('logo_option', logoOption.value);
                    formData.append('custom_logo', file);
                    
                    // 顯示載入中的訊息
                    downloadButton.disabled = true;
                    downloadButton.innerText = '處理中...';
                    
                    // 使用 POST 請求上傳自訂 logo 並下載 QR code
                    fetch(downloadUrl, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => {
                        downloadButton.disabled = false;
                        downloadButton.innerText = '下載 QR Code';
                        
                        if (!response.ok) {
                            // 如果伺服器返回錯誤狀態碼，嘗試從回應中獲取詳細錯誤訊息
                            return response.json().then(err => {
                                throw new Error(err.error || `HTTP error! status: ${response.status}`);
                            }).catch(e => {
                                // 如果無法解析為 JSON，則回傳原始錯誤
                                if (e.name === 'SyntaxError') {
                                    throw new Error(`HTTP error! status: ${response.status}`);
                                }
                                throw e;
                            });
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        // 確保我們收到的是圖片
                        if (!blob.type.startsWith('image/')) {
                            throw new Error('伺服器沒有返回有效的圖片檔案');
                        }
                        
                        // 建立一個臨時下載連結
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = `qrcode_${qrcodeUrl.split('/').pop()}.png`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    })
                    .catch(error => {
                        console.error('Error downloading QR code:', error);
                        alert('下載 QR Code 時發生錯誤: ' + error.message);
                    });
                } else {
                    // 添加 logo 選項參數
                    downloadUrl += `?logo_option=${logoOption.value}`;
                    
                    // 對於預設 logo 或無 logo 的情況，使用 fetch 請求下載以保持一致的處理方式
                    fetch(downloadUrl, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        return response.blob();
                    })
                    .then(blob => {
                        // 建立一個臨時下載連結
                        const url = window.URL.createObjectURL(blob);
                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = `qrcode_${qrcodeUrl.split('/').pop()}.png`;
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                        window.URL.revokeObjectURL(url);
                    })
                    .catch(error => {
                        console.error('Error downloading QR code:', error);
                        alert('下載 QR Code 時發生錯誤，請重試');
                    });
                }
            };
        }
    </script>
    @endpush
</x-app-layout> 