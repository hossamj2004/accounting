<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AccountController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        Auth::user()->accounts()->create([
            'name' => $request->name,
        ]);

        return redirect(route('dashboard'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Account $account): View
    {
        // Authorize that the user owns the account
        $this->authorize('view', $account);

        // Initial load of transactions will be handled by the API for infinite scroll
        return view('accounts.show', ['account' => $account]);
    }
}
