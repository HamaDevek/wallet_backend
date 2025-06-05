<?php

namespace App\Listeners;

use App\Events\TransactionProcessed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendTransactionNotification
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TransactionProcessed $event): void
    {
        $subject = match ($event->type) {
            'sent' => 'Money Sent Successfully',
            'received' => 'Money Received',
            'recharge' => 'Wallet Recharged Successfully',
            default => 'Transaction Notification',
        };
        $amount = number_format($event->transaction->amount, 2);
        $userName = $event->user->name;
        $message = match ($event->type) {
            'sent' => "Dear {$userName}, you have successfully sent {$amount} IQD. Transaction ID: {$event->transaction->id}",
            'received' => "Dear {$userName}, you have received {$amount} IQD. Transaction ID: {$event->transaction->id}",
            'recharge' => "Dear {$userName}, your wallet has been recharged with {$amount} IQD. Transaction ID: {$event->transaction->id}",
            default => "Dear {$userName}, a transaction of {$amount} IQD has been processed. Transaction ID: {$event->transaction->id}",
        };

        // Simulate email sending delay
        sleep(1);

        Log::info('Email notification simulated', [
            'to' => $event->user->email,
            'subject' => $subject,
            'message' => $message,
            'timestamp' => now()->toDateTimeString(),
        ]);
    }
    public function failed(TransactionProcessed $event, \Throwable $exception): void
    {
        Log::error('Failed to send transaction notification', [
            'transaction_id' => $event->transaction->id,
            'user_email' => $event->user->email,
            'error' => $exception->getMessage(),
        ]);
    }
}
