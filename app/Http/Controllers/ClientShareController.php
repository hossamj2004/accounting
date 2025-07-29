<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\URL;
use Illuminate\View\View;

class ClientShareController extends Controller
{
    /**
     * Generate a signed URL for the client.
     */
    public function generate(Request $request): RedirectResponse
    {
        $request->validate(['account_id' => 'required|exists:accounts,id']);
        $account = Account::findOrFail($request->account_id);
        $this->authorize('update', $account);

        // Generate a temporary signed URL that is valid for 7 days.
        $signedUrl = URL::temporarySignedRoute(
            'client.transaction.create',
            now()->addDays(7),
            ['account' => $account->id]
        );

        // Store the URL in the session to display it on the account page.
        return redirect()->route('accounts.show', $account->id)->with('signed_url', $signedUrl);
    }

    /**
     * Show the form for the client to add a transaction.
     */
    public function create(Account $account): View
    {
        return view('client.create', ['account' => $account]);
    }

    /**
     * Store a transaction from the client.
     */
    public function store(Request $request, Account $account): RedirectResponse
    {
        $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);

        $account->transactions()->create([
            'type' => 'credit', // Transactions from clients are always 'credit'
            'amount' => $request->amount,
            'description' => 'معاملة من العميل: ' . $request->description,
        ]);

        return redirect()->route('client.transaction.success');
    }

    /**
     * Show a success page to the client.
     */
    public function success(): View
    {
        return view('client.success');
    }
}
