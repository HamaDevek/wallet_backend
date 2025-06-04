<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    /**
     * Self recharge - user adds money to their own account
     */
    public function recharge(Request $request)
    {
        $request->validate([
            'description' => 'nullable|string|max:255',
        ]);

        $user = Auth::user();

        $amount = $request->amount;

        try {
            DB::beginTransaction();

            $transaction = Transactions::create([
                'amount' => $amount,
                // 'sender_id' => $user->id,
                'receiver_id' => $user->id,
                'type' => 'recharge',
                'status' => 'completed',
                'description' => $request->description ?? 'Account recharge',
            ]);
            $oldBalance = $user->wallet_balance;
            $user->incrementBalance($amount);

            DB::commit();
            $transaction->load(['sender', 'receiver']);
            return response()->json([
                'success' => true,
                'message' => 'Recharge successful',
                'transaction' => $transaction,
                'old_balance' => (float) $oldBalance,
                'new_balance' => $user->wallet_balance,
                'moved_amount' => (float) $amount,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Recharge failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    // send money
    public function sendMoney(Request $request)
    {
        $request->validate([
            'recipient_email' => 'required|email',
            'amount' => 'required|numeric|min:1000|max:5000000',
            'description' => 'nullable|string|max:255',
        ]);
        $user = Auth::user();
        $amount = $request->amount;
        try {
            DB::beginTransaction();

            $receiver = User::where('email', $request->recipient_email)->first();
            if (!$receiver) {
                return response()->json(['success' => false, 'message' => 'Receiver not found'], 404);
            }

            if (!$user->hasSufficientBalance($amount)) {
                return response()->json(['success' => false, 'message' => 'Insufficient balance'], 400);
            }

            $transaction = Transactions::create([
                'amount' => $amount,
                'sender_id' => $user->id,
                'receiver_id' => $receiver->id,
                'type' => 'transfer',
                'status' => 'completed',
                'description' => $request->description ?? 'Money transfer',
            ]);
            $old_balance = $user->wallet_balance;
            $user->deductBalance($amount);
            $receiver->incrementBalance($amount);

            DB::commit();
            $transaction->load(['sender', 'receiver']);
            return response()->json([
                'success' => true,
                'message' => 'Money sent successfully',
                'transaction' => $transaction,
                'old_balance' => (float)  $old_balance,
                'new_balance' => (float) $user->wallet_balance,
                'moved_amount' => (float) $amount,
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Transaction failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
    // receivedTransactions
    public function receivedTransactions()
    {
        $user = Auth::user();
        $transactions = $user->receivedTransactions()->with(['sender', 'receiver'])->get();
        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ], 200);
    }
    public function sentTransactions()
    {
        $user = Auth::user();
        $transactions = $user->sentTransactions()->with(['sender', 'receiver'])->get();
        return response()->json([
            'success' => true,
            'transactions' => $transactions,
        ], 200);
    }
}
