<x-guest-layout>
    <div class="mb-4 text-center">
        <h2 class="text-lg font-bold">إضافة معاملة لحساب: {{ $account->name }}</h2>
        <p class="text-sm text-gray-600">يمكنك استخدام هذا النموذج لإعلام صاحب الحساب بالمدفوعات التي أرسلتها له.</p>
    </div>

    <form method="POST" action="{{ route('client.transaction.store', ['account' => $account->id]) }}">
        @csrf

        <!-- Amount -->
        <div>
            <x-input-label for="amount" value="المبلغ الذي دفعته" />
            <x-text-input id="amount" class="block mt-1 w-full" type="number" name="amount" step="0.01" required autofocus />
            <x-input-error :messages="$errors->get('amount')" class="mt-2" />
        </div>

        <!-- Description -->
        <div class="mt-4">
            <x-input-label for="description" value="وصف (مثال: دفعة من حساب شهر 5)" />
            <x-text-input id="description" class="block mt-1 w-full" type="text" name="description" />
            <x-input-error :messages="$errors->get('description')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                إضافة المعاملة
            </x-primary-button>
        </div>
    </form>
</x-guest-layout>
