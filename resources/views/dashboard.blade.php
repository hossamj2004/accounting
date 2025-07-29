<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Add new account form -->
                    <div class="mb-6">
                        <h3 class="text-lg font-semibold mb-2">إضافة حساب جديد</h3>
                        <form action="{{ route('accounts.store') }}" method="post">
                            @csrf
                            <div class="flex items-center">
                                <x-text-input id="name" name="name" type="text" class="block w-full" placeholder="اسم الحساب الجديد" required />
                                <x-primary-button class="ml-3">
                                    إضافة
                                </x-primary-button>
                            </div>
                             <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </form>
                    </div>

                    <!-- Account list -->
                    <h3 class="text-lg font-semibold mb-2">قائمة الحسابات</h3>
                    <div class="space-y-4">
                        @forelse ($accounts as $account)
                            <a href="{{ route('accounts.show', $account) }}" class="block p-4 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                <div class="flex justify-between items-center">
                                    <span class="font-bold"><?php echo htmlspecialchars($account->name); ?></span>
                                    <span class="font-bold <?php echo $account->balance >= 0 ? 'text-green-600' : 'text-red-600'; ?>">
                                        <?php echo number_format($account->balance, 2); ?>
                                    </span>
                                </div>
                            </a>
                        @empty
                            <p>لم تقم بإضافة أي حسابات بعد.</p>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
