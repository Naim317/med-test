<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index()
    {
        // Fetch all transactions and current balance for the authenticated user
        $user = auth()->user();
        $transactions = Transaction::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();
        $currentBalance = $user->balance;

        return view('transaction.index', compact('transactions', 'currentBalance'));
    }

    public function showDeposits()
    {
        // Fetch all deposited transactions for the authenticated user
        $user = auth()->user();
        $deposits = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'Deposit')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transaction.deposit', compact('deposits'));
    }

    public function deposit(Request $request)
    {
        // Validate the deposit form input
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Get the authenticated user
        $user = auth()->user();

        // Update the user's balance by adding the deposited amount
        $user->balance += $request->amount;
        $user->save();

        // Create a deposit transaction
        try{
            Transaction::create([
                'user_id' => $user->id,
                'transaction_type' => 'Deposit',
                'amount' => $request->amount,
                'fee' => $request->fee,
                'date' => $request->date,
            ]);
    
            return redirect()->route('transaction.index')->with('success', 'Amount Deposit successfull.');
        }
        catch(\Exception $e) {
            // $msg = $e->getMessage();
            return  redirect()->route('transaction.index')->with('fail', "Amount Deposit Failed! Please try again"); 
        } 
    }

    public function showWithdrawals()
    {
        // Fetch all withdrawal transactions for the authenticated user
        $user = auth()->user();
        $withdrawals = Transaction::where('user_id', $user->id)
            ->where('transaction_type', 'Withdrawal')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transaction.withdrawal', compact('withdrawals'));
    }

    public function withdrawal(Request $request)
    {
        // Validate the withdrawal form input
        $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        // Get the authenticated user
        $user = auth()->user();

        // Calculate the withdrawal fee based on the user's account type
        $withdrawalFee = $user->account_type === 'Business' ? 0.025 : 0.015;

        // Check if it's a Friday, and if it is, make the withdrawal free of charge
        if (now()->isFriday()) {
            $withdrawalFee = 0.00;
        }

        // Calculate the total withdrawal amount including the fee
        $totalWithdrawalAmount = $request->amount * (1 + $withdrawalFee);

        // Check if the user has sufficient balance for the withdrawal
        if ($user->balance < $totalWithdrawalAmount) {
            return redirect()->route('transaction.index')->with('error', 'Insufficient balance.');
        }

        // Apply the withdrawal fee
        $withdrawalAmount = $request->amount;
        $feeAmount = $withdrawalAmount * $withdrawalFee;
        $finalWithdrawalAmount = $withdrawalAmount - $feeAmount;

        // Update the user's balance
        $user->balance -= $totalWithdrawalAmount;
        $user->save();

        // Create a withdrawal transaction with the fee amount
        try{
            Transaction::create([
                'user_id' => $user->id,
                'transaction_type' => 'Withdrawal',
                'amount' => $finalWithdrawalAmount,
                'fee' => $feeAmount, // Store the fee in the transaction
            ]);
            return redirect()->route('transaction.index')->with('success', 'Amount Withdrawal successfull.');
        }
        catch(\Exception $e) {
            // $msg = $e->getMessage();
            return  redirect()->route('transaction.index')->with('fail', "Amount Withdrawal Failed! Please try again"); 
        } 
    }
}
