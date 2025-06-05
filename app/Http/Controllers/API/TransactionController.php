<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\ApiResponseHelper;
use App\Http\Resources\TransactionResource;
use App\Http\Resources\UserResource;
use App\Models\Transactions;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class TransactionController extends Controller
{

    public function recharge(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:1000|max:5000000',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponseHelper::validationError($validator->errors());
        }

        $user = Auth::user();
        $amount = $request->amount;

        try {
            DB::beginTransaction();

            $transaction = Transactions::create([
                'amount' => $amount,
                'receiver_id' => $user->id,
                'type' => 'recharge',
                'status' => 'completed',
                'description' => $request->description ?? 'Account recharge',
            ]);

            $oldBalance = $user->wallet_balance;
            $user->incrementBalance($amount);

            DB::commit();

            $transaction->load(['sender', 'receiver']);

            return ApiResponseHelper::success([
                'transaction' => new TransactionResource($transaction),
                'user' => new UserResource($user->fresh()),
                'old_balance' => (float) $oldBalance,
                'new_balance' => (float) $user->wallet_balance,
                'moved_amount' => (float) $amount,
            ], 'Recharge successful');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponseHelper::serverError('Recharge failed: ' . $e->getMessage());
        }
    }

    public function sendMoney(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_email' => 'required|email',
            'amount' => 'required|numeric|min:1000|max:5000000',
            'description' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return ApiResponseHelper::validationError($validator->errors());
        }

        $user = Auth::user();
        $amount = $request->amount;

        try {
            DB::beginTransaction();

            $receiver = User::where('email', $request->recipient_email)->first();
            if (!$receiver) {
                return ApiResponseHelper::notFound('Recipient not found');
            }

            if (!$user->hasSufficientBalance($amount)) {
                return ApiResponseHelper::error('Insufficient balance', null, 400);
            }

            $transaction = Transactions::create([
                'amount' => $amount,
                'sender_id' => $user->id,
                'receiver_id' => $receiver->id,
                'type' => 'transfer',
                'status' => 'completed',
                'description' => $request->description ?? 'Money transfer',
            ]);

            $oldBalance = $user->wallet_balance;
            $user->deductBalance($amount);
            $receiver->incrementBalance($amount);

            DB::commit();

            $transaction->load(['sender', 'receiver']);

            return ApiResponseHelper::success([
                'transaction' => new TransactionResource($transaction),
                'user' => new UserResource($user->fresh()),
                'old_balance' => (float) $oldBalance,
                'new_balance' => (float) $user->wallet_balance,
                'moved_amount' => (float) $amount,
            ], 'Money sent successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return ApiResponseHelper::serverError('Transaction failed: ' . $e->getMessage());
        }
    }


    public function receivedTransactions()
    {
        $user = Auth::user();
        $transactions = $user->receivedTransactions()->with(['sender', 'receiver'])->latest()->get();

        return ApiResponseHelper::success([
            'transactions' => TransactionResource::collection($transactions),
            'total_count' => $transactions->count(),
        ], 'Received transactions retrieved successfully');
    }

    public function sentTransactions()
    {
        $user = Auth::user();
        $transactions = $user->sentTransactions()->with(['sender', 'receiver'])->latest()->get();

        return ApiResponseHelper::success([
            'transactions' => TransactionResource::collection($transactions),
            'total_count' => $transactions->count(),
        ], 'Sent transactions retrieved successfully');
    }


    public function allTransactions()
    {
        $user = Auth::user();

        $sentTransactions = $user->sentTransactions()->with(['sender', 'receiver']);
        $receivedTransactions = $user->receivedTransactions()->with(['sender', 'receiver']);

        $allTransactions = $sentTransactions->union($receivedTransactions)
            ->orderBy('created_at', 'desc')
            ->get();

        return ApiResponseHelper::success([
            'transactions' => TransactionResource::collection($allTransactions),
            'total_count' => $allTransactions->count(),
        ], 'All transactions retrieved successfully');
    }
}
