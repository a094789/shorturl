<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('點擊記錄') }}
            </h2>
            <a href="{{ route('short-urls.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                返回列表
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-lg font-medium text-gray-900 mb-1">點擊記錄</h3>
                    <p class="text-sm text-gray-600 mb-6">查看此短網址的所有點擊記錄，包含訪問時間、IP位址和來源資訊。</p>
                    <a href="{{ route('short-urls.index') }}" class="inline-flex items-center px-4 py-2 mb-4 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700 focus:bg-gray-700 active:bg-gray-900 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        返回列表
                    </a>
                    <!-- 每頁顯示筆數選擇 -->
                    <div class="mb-4 flex justify-end">
                        <form method="GET" action="{{ route('short-urls.clicks', $shortUrl) }}" class="flex items-center">
                            <label for="per_page" class="mr-2 text-sm text-gray-600">每頁顯示：</label>
                            <select id="per_page" name="per_page" onchange="this.form.submit()" class="border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm pr-8 appearance-none">
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
                        @foreach ($clicks as $click)
                        <div class="bg-white border rounded-lg shadow-xl p-4">
                            <div class="mb-3">
                                <label class="text-xs text-gray-500">IP 位址</label>
                                <p class="text-sm text-gray-900">{{ $click->ip_address }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-xs text-gray-500">瀏覽器資訊</label>
                                <p class="text-sm text-gray-900 break-all">{{ $click->user_agent }}</p>
                            </div>
                            <div class="mb-3">
                                <label class="text-xs text-gray-500">來源網址</label>
                                <p class="text-sm text-gray-900 break-all">{{ $click->referer ?: '直接訪問' }}</p>
                            </div>
                            <div>
                                <label class="text-xs text-gray-500">點擊時間</label>
                                <p class="text-sm text-gray-900">{{ $click->created_at->format('Y-m-d H:i:s') }}</p>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    <!-- 桌面版表格視圖 -->
                    <div class="hidden md:block">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3">IP 位址</th>
                                    <th class="px-6 py-3">瀏覽器資訊</th>
                                    <th class="px-6 py-3">來源網址</th>
                                    <th class="px-6 py-3">點擊時間</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach ($clicks as $click)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $click->ip_address }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="truncate max-w-xs" title="{{ $click->user_agent }}">
                                                {{ $click->user_agent }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="truncate max-w-xs" title="{{ $click->referer }}">
                                                {{ $click->referer ?: '直接訪問' }}
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $click->created_at->format('Y-m-d H:i:s') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $clicks->appends(request()->except('page'))->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 