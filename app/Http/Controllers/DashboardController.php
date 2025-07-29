<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the user's dashboard.
     */
    public function index(): View
    {
        $accounts = Auth::user()->accounts()->with(['transactions'])->get();

        $accounts->each(function ($account) {
            $credits = $account->transactions->where('type', 'credit')->sum('amount');
            $debits = $account->transactions->where('type', 'debit')->sum('amount');
            $account->balance = $credits - $debits;
        });

        return view('dashboard', ['accounts' => $accounts]);
    }
}
