<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();

            // Amount: total 15 digits, 2 decimals (suitable for large sums)
            $table->decimal('amount', 15, 2);

            // Sender and receiver user IDs (assuming users table exists)
            $table->unsignedBigInteger('sender_id')->nullable();
            $table->unsignedBigInteger('receiver_id');

            $table->string('type')->nullable();

            // Examples: 'pending', 'completed', 'failed'
            $table->string('status')->default('pending');

            // Optional: notes or description about the transaction
            $table->text('description')->nullable();

            // Timestamps for creation and update
            $table->timestamps();

            // Foreign key constraints (optional, adds referential integrity)
            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('receiver_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
