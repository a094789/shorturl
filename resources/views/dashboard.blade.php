<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <!-- 歡迎區塊 -->
            <div class="p-4 sm:p-8 bg-white shadow sm:rounded-lg">
                <div class="max-w-xl">
                    <h2 class="text-lg font-medium text-gray-900">
                        歡迎使用 ShortURL
                    </h2>
                    <p class="mt-1 text-sm text-gray-600">
                        快速創建和管理您的短網址，追蹤點擊數據，優化您的連結分享體驗。
                    </p>
                    <div class="mt-6 flex gap-4">
                        <a href="{{ route('short-urls.create') }}"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700 focus:bg-red-700 active:bg-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 transition ease-in-out duration-150">
                            建立短網址
                        </a>
                        <a href="{{ route('short-urls.index') }}"
                            class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                            查看短網址
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>