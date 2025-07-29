<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TransactionApiController extends Controller
{
    /**
     * Get transactions for a given account.
     */
    public function index(Request $request, Account $account): JsonResponse
    {
        // Authorize that the user owns the account
        $this->authorize('view', $account);

        $request->validate([
            'limit' => 'integer|min:1|max:100',
            'offset' => 'integer|min:0',
        ]);

        $limit = $request->input('limit', 20);
        $offset = $request->input('offset', 0);

        $transactions = $account->transactions()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->offset($offset)
            ->get();

        // Calculate total balance separately
        $credits = $account->transactions()->where('type', 'credit')->sum('amount');
        $debits = $account->transactions()->where('type', 'debit')->sum('amount');
        $totalBalance = $credits - $debits;

        return response()->json([
            'transactions' => $transactions,
            'total_balance' => $totalBalance,
        ]);
    }
}
