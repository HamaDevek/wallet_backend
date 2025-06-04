<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Transactions extends Model
{
    protected $fillable = [
        "amount",
        "sender_id",
        "receiver_id",
        "type",
        "status",
        "description",
    ];

    // sender
    public function sender()
    {
        return $this->belongsTo(User::class, "sender_id");
    }
    // receiver
    public function receiver()
    {
        return $this->belongsTo(User::class, "receiver_id");
    }
}
