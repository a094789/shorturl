<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                <div class="max-w-xl">
                    <header>
                        <h2 class="text-lg font-medium text-gray-900">
                            建立短網址
                        </h2>

                        <p class="mt-1 text-sm text-gray-600">
                            將長網址轉換為簡短易記的短網址，方便分享和追蹤點擊次數。
                        </p>
                    </header>

                    <form method="POST" action="{{ route('short-urls.store') }}" class="mt-6">
                        @csrf

                        <div class="mb-6">
                            <label for="original_url" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">輸入網址</label>

                            <input
                                type="url"
                                name="original_url"
                                id="original_url"
                                value="{{ old('original_url') }}"
                                placeholder="https://example.com"
                                class="w-full px-4 py-2 rounded-md border border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm
                                placeholder-gray-400 text-gray-800 dark:bg-zinc-900 dark:border-zinc-700 dark:text-white" />

                            <x-input-error :messages="$errors->get('original_url')" class="mt-2" />
                        </div>

                        @if(auth()->user()->isAdmin())
                        <div class="mb-6">
                            <label for="custom_code" class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">
                                自訂短網址代碼
                                <span class="text-xs text-gray-500 ml-1">(選填，僅限英文與數字)</span>
                            </label>

                            <input
                                type="text"
                                name="custom_code"
                                id="custom_code"
                                value="{{ old('custom_code') }}"
                                pattern="[A-Za-z0-9]+"
                                placeholder="輸入自訂代碼"
                                class="w-full px-4 py-2 rounded-md border border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm
                                placeholder-gray-400 text-gray-800 dark:bg-zinc-900 dark:border-zinc-700 dark:text-white" />

                            <p class="mt-1 text-xs text-gray-500">若不填寫則自動生成隨機代碼</p>
                            <x-input-error :messages="$errors->get('custom_code')" class="mt-2" />
                        </div>
                        @endif

                        <!-- 過期時間選擇 -->
                        <div x-data="{ expireType: '{{ old('expire_type', '1_week') }}' }" class="mb-6">
                            <label class="block mb-2 text-sm font-medium text-gray-700 dark:text-gray-200">過期時間</label>
                            <div class="space-y-3">
                                <div class="flex flex-wrap gap-4">
                                    <label class="flex items-center">
                                        <input type="radio"
                                            name="expire_type"
                                            value="1_day"
                                            x-model="expireType"
                                            class="border-gray-300 text-red-600 shadow-sm focus:border-red-500 focus:ring-red-500">
                                        <span class="ml-2 text-sm text-gray-600">1 天</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio"
                                            name="expire_type"
                                            value="1_week"
                                            x-model="expireType"
                                            class="border-gray-300 text-red-600 shadow-sm focus:border-red-500 focus:ring-red-500">
                                        <span class="ml-2 text-sm text-gray-600">1 週</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="radio"
                                            name="expire_type"
                                            value="1_month"
                                            x-model="expireType"
                                            class="border-gray-300 text-red-600 shadow-sm focus:border-red-500 focus:ring-red-500">
                                        <span class="ml-2 text-sm text-gray-600">1 個月</span>
                                    </label>
                                    @if(auth()->user()->isAdmin())
                                    <label class="flex items-center">
                                        <input type="radio"
                                            name="expire_type"
                                            value="permanent"
                                            x-model="expireType"
                                            class="border-gray-300 text-red-600 shadow-sm focus:border-red-500 focus:ring-red-500">
                                        <span class="ml-2 text-sm text-gray-600">永久有效</span>
                                    </label>
                                    @endif
                                    <label class="flex items-center">
                                        <input type="radio"
                                            name="expire_type"
                                            value="custom"
                                            x-model="expireType"
                                            class="border-gray-300 text-red-600 shadow-sm focus:border-red-500 focus:ring-red-500">
                                        <span class="ml-2 text-sm text-gray-600">自訂</span>
                                    </label>
                                </div>

                                <div x-show="expireType === 'custom'"
                                    x-transition:enter="transition ease-out duration-200"
                                    x-transition:enter-start="opacity-0 translate-y-1"
                                    x-transition:enter-end="opacity-100 translate-y-0"
                                    class="mt-3">
                                    <input type="datetime-local"
                                        name="custom_expires_at"
                                        id="custom_expires_at"
                                        value="{{ old('custom_expires_at') }}"
                                        class="w-full px-4 py-2 rounded-md border border-gray-300 focus:border-red-500 focus:ring-red-500 shadow-sm"
                                        @if(!auth()->user()->isAdmin())
                                        max="{{ now()->addYear()->format('Y-m-d\TH:i') }}"
                                        @endif
                                        min="{{ now()->format('Y-m-d\TH:i') }}">
                                    @if(!auth()->user()->isAdmin())
                                    <p class="mt-1 text-xs text-gray-500">注意：一般用戶只能設置最多一年的過期時間</p>
                                    @endif
                                </div>
                            </div>
                            <x-input-error :messages="$errors->get('expire_type')" class="mt-2" />
                            <x-input-error :messages="$errors->get('custom_expires_at')" class="mt-2" />
                        </div>

                        <div class="flex items-center gap-4 mt-6">
                            <x-primary-button class="py-2 px-4">
                                建立短網址
                            </x-primary-button>
                            <a href="{{ route('dashboard') }}"
                                class="inline-flex items-center px-4 py-2 bg-white border border-gray-300 rounded-md font-semibold text-xs text-gray-700 uppercase tracking-widest shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150">
                                取消
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('expires_at').addEventListener('change', function() {
            const customExpiresAt = document.getElementById('custom_expires_at');
            if (this.value === 'custom') {
                customExpiresAt.classList.remove('hidden');
            } else {
                customExpiresAt.classList.add('hidden');
            }
        });

        // 表單驗證
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const urlInput = document.getElementById('original_url');
            const isAdmin = @json(auth()->user()->isAdmin());
            
            // 顯示錯誤訊息的輔助函數
            function showError(input, message) {
                const errorElement = input.nextElementSibling;
                if (errorElement && errorElement.classList.contains('text-red-600')) {
                    errorElement.textContent = message;
                } else {
                    const newError = document.createElement('p');
                    newError.textContent = message;
                    newError.className = 'mt-2 text-sm text-red-600';
                    input.parentNode.insertBefore(newError, input.nextSibling);
                }
            }
            
            // 清除錯誤訊息
            function clearError(input) {
                const errorElement = input.nextElementSibling;
                if (errorElement && errorElement.classList.contains('text-red-600')) {
                    errorElement.textContent = '';
                }
            }
            
            // 網址輸入框即時驗證
            urlInput.addEventListener('blur', validateUrl);
            urlInput.addEventListener('input', function() {
                clearError(urlInput);
            });
            
            // 網址驗證函數
            function validateUrl() {
                const url = urlInput.value.trim();
                
                // 清除之前的錯誤
                clearError(urlInput);
                
                // 空值檢查
                if (!url) {
                    showError(urlInput, '請輸入網址');
                    return false;
                }
                
                // 確保包含通訊協定
                let urlToValidate = url;
                if (!urlToValidate.match(/^https?:\/\//i)) {
                    urlToValidate = 'http://' + urlToValidate;
                    // 自動更新輸入框中的值
                    urlInput.value = urlToValidate;
                }
                
                // 使用正則表達式檢查網址格式
                const urlPattern = /^(https?:\/\/)((([a-z\d]([a-z\d-]*[a-z\d])*)\.)+[a-z]{2,}|((\d{1,3}\.){3}\d{1,3}))(:\d+)?(\/[-a-z\d%_.~+]*)*(\?[;&a-z\d%_.~+=-]*)?(#[-a-z\d_]*)?$/i;
                if (!urlPattern.test(urlToValidate)) {
                    showError(urlInput, '請輸入有效的網址格式，例如：https://example.com');
                    return false;
                }
                
                // 檢查網域名稱是否為純數字
                const parsedUrl = new URL(urlToValidate);
                const host = parsedUrl.hostname;
                if (host.replace(/\./g, '').match(/^\d+$/)) {
                    showError(urlInput, '不接受純數字 IP 格式，請使用網域名稱');
                    return false;
                }
                
                // 檢查是否是本地 IP
                const localIpPattern = /^(localhost|127\.|10\.|172\.(1[6-9]|2[0-9]|3[0-1])\.|192\.168\.)/i;
                if (localIpPattern.test(host)) {
                    showError(urlInput, '不接受本地網址，請使用公開可訪問的網址');
                    return false;
                }
                
                return true;
            }
            
            // 表單提交驗證
            form.addEventListener('submit', function(e) {
                const isUrlValid = validateUrl();
                
                // 檢查自訂過期時間
                const expireType = document.querySelector('input[name="expire_type"]:checked').value;
                if (expireType === 'custom') {
                    const customDate = document.getElementById('custom_expires_at').value;
                    if (!customDate) {
                        showError(document.getElementById('custom_expires_at'), '請選擇自訂過期時間');
                        e.preventDefault();
                        return false;
                    }
                    
                    // 檢查是否選擇了未來時間
                    const selectedDate = new Date(customDate);
                    const now = new Date();
                    if (selectedDate <= now) {
                        showError(document.getElementById('custom_expires_at'), '過期時間必須是未來的時間');
                        e.preventDefault();
                        return false;
                    }
                    
                    // 檢查非管理員是否選擇了超過一年的時間
                    if (!isAdmin) {
                        const oneYearLater = new Date();
                        oneYearLater.setFullYear(oneYearLater.getFullYear() + 1);
                        
                        if (selectedDate > oneYearLater) {
                            showError(document.getElementById('custom_expires_at'), '一般用戶的自訂過期時間不能超過一年');
                            e.preventDefault();
                            return false;
                        }
                    }
                }
                
                // 如果網址驗證失敗，阻止表單提交
                if (!isUrlValid) {
                    e.preventDefault();
                    return false;
                }
            });
        });
    </script>
</x-app-layout>