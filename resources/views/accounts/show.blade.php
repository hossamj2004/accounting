<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            حساب: {{ $account->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    <!-- Client Share Link Section -->
                    <div class="mb-6 p-4 border rounded-lg bg-gray-50">
                        <h3 class="text-lg font-semibold mb-2">مشاركة الحساب مع العميل</h3>
                        @if (session('signed_url'))
                            <p class="mb-2">رابط المشاركة (صالح لمدة 7 أيام):</p>
                            <x-text-input class="w-full" :value="session('signed_url')" readonly />
                        @else
                             <form action="{{ route('client.share.generate') }}" method="post">
                                @csrf
                                <input type="hidden" name="account_id" value="{{ $account->id }}">
                                <x-primary-button>
                                    إنشاء رابط مشاركة
                                </x-primary-button>
                            </form>
                        @endif
                    </div>

                    <!-- Add new transaction form -->
                    <div class="mb-6 p-4 border rounded-lg">
                        <h3 class="text-lg font-semibold mb-2">إضافة معاملة جديدة</h3>
                        <form action="{{ route('transactions.store') }}" method="post">
                            @csrf
                            <input type="hidden" name="account_id" value="{{ $account->id }}">

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <x-input-label for="type" value="النوع" />
                                    <select name="type" id="type" class="block mt-1 w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                        <option value="credit">له (دائن)</option>
                                        <option value="debit">عليه (مدين)</option>
                                    </select>
                                </div>
                                <div>
                                    <x-input-label for="amount" value="المبلغ" />
                                    <x-text-input id="amount" name="amount" type="number" step="0.01" class="block mt-1 w-full" required />
                                </div>
                                <div class="md:col-span-2">
                                    <x-input-label for="description" value="الوصف (اختياري)" />
                                    <x-text-input id="description" name="description" type="text" class="block mt-1 w-full" />
                                </div>
                            </div>

                            <div class="mt-4">
                                <x-primary-button>إضافة المعاملة</x-primary-button>
                            </div>
                        </form>
                    </div>

                    <!-- Transactions list -->
                    <h3 class="text-lg font-semibold mb-2">كشف الحساب</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="py-2 px-4 border-b">التاريخ</th>
                                    <th class="py-2 px-4 border-b">النوع</th>
                                    <th class="py-2 px-4 border-b">المبلغ</th>
                                    <th class="py-2 px-4 border-b">الوصف</th>
                                    <th class="py-2 px-4 border-b">الرصيد</th>
                                </tr>
                            </thead>
                            <tbody id="transactions-tbody">
                                <!-- JS will populate this -->
                            </tbody>
                        </table>
                    </div>
                     <div id="loader" style="display: none; text-align: center; padding: 20px;">
                        <p>يتم تحميل المزيد من المعاملات...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tbody = document.getElementById('transactions-tbody');
            const loader = document.getElementById('loader');
            const accountId = {{ $account->id }};

            let transactions = [];
            let totalBalance = 0;
            let offset = 0;
            const limit = 20;
            let isLoading = false;
            let allLoaded = false;

            function createTransactionRow(tx, balance) {
                const row = document.createElement('tr');
                const typeClass = tx.type === 'credit' ? 'text-green-600' : 'text-red-600';
                const typeText = tx.type === 'credit' ? 'له' : 'عليه';

                const descCell = document.createElement('td');
                descCell.textContent = tx.description || '';
                descCell.className = 'py-2 px-4 border-b';

                row.innerHTML = `
                    <td class="py-2 px-4 border-b">${new Date(tx.created_at).toLocaleString('ar-EG')}</td>
                    <td class="py-2 px-4 border-b ${typeClass}">${typeText}</td>
                    <td class="py-2 px-4 border-b">${parseFloat(tx.amount).toFixed(2)}</td>
                    <td class="py-2 px-4 border-b balance-cell">${balance.toFixed(2)}</td>
                `;
                row.insertBefore(descCell, row.children[3]);
                return row;
            }

            function renderTransactions() {
                tbody.innerHTML = '';
                let currentBalance = totalBalance;
                transactions.forEach(tx => {
                    const row = createTransactionRow(tx, currentBalance);
                    tbody.appendChild(row);
                    currentBalance -= (tx.type === 'credit' ? parseFloat(tx.amount) : -parseFloat(tx.amount));
                });
            }

            async function loadMoreTransactions() {
                if (isLoading || allLoaded) return;

                isLoading = true;
                loader.style.display = 'block';

                try {
                    const response = await fetch(`/api/accounts/${accountId}/transactions?limit=${limit}&offset=${offset}`, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        }
                    });

                    if(response.status === 401) {
                        window.location.href = '/login';
                        return;
                    }

                    const data = await response.json();

                    if (data.error) throw new Error(data.error);

                    if (transactions.length === 0) {
                        totalBalance = data.total_balance;
                    }

                    if (data.transactions.length > 0) {
                        transactions.push(...data.transactions);
                        offset += data.transactions.length;
                        renderTransactions();
                    } else {
                        allLoaded = true;
                        if (transactions.length === 0) {
                             tbody.innerHTML = '<tr><td colspan="5" class="text-center py-4">لا توجد معاملات لعرضها.</td></tr>';
                        }
                    }

                } catch (error) {
                    console.error('Error fetching transactions:', error);
                    loader.innerHTML = 'فشل تحميل المزيد من المعاملات.';
                } finally {
                    isLoading = false;
                    loader.style.display = 'none';
                }
            }

            window.addEventListener('scroll', () => {
                if ((window.innerHeight + window.scrollY) >= document.body.offsetHeight - 100) {
                    loadMoreTransactions();
                }
            });

            // Initial load
            loadMoreTransactions();
        });
    </script>
    @endpush
</x-app-layout>
