<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('使用者管理') }}
        </h2>
    </x-slot>

    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                <tr>
                    <th scope="col" class="px-6 py-3">名稱</th>
                    <th scope="col" class="px-6 py-3">電子郵件</th>
                    <th scope="col" class="px-6 py-3">角色</th>
                    <th scope="col" class="px-6 py-3">建立時間</th>
                    <th scope="col" class="px-6 py-3">操作</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <td class="px-6 py-4 font-medium text-gray-900 dark:text-white">
                            {{ $user->name }}
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4">
                            @if($user->is_admin)
                                <span class="bg-blue-100 text-blue-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-blue-900 dark:text-blue-300">管理員</span>
                            @else
                                <span class="bg-gray-100 text-gray-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-gray-700 dark:text-gray-300">一般用戶</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            {{ $user->created_at->format('Y-m-d H:i:s') }}
                        </td>
                        <td class="px-6 py-4">
                            <form method="POST" action="{{ route('admin.users.toggle-admin', $user) }}" class="inline-block">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="font-medium text-blue-600 dark:text-blue-500 hover:underline me-3">
                                    {{ $user->is_admin ? '移除管理員' : '設為管理員' }}
                                </button>
                            </form>
                            @if($user->id !== auth()->id())
                                <form method="POST" action="{{ route('admin.users.destroy', $user) }}" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" onclick="return confirm('確定要刪除這個使用者嗎？')" class="font-medium text-red-600 dark:text-red-500 hover:underline">
                                        刪除
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr class="bg-white border-b dark:bg-gray-800 dark:border-gray-700">
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">
                            尚無使用者
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</x-app-layout> 