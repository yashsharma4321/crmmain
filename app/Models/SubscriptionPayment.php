<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPayment extends Model
{
    use HasFactory;

    protected $table = 'subscription_payments';

    protected $fillable = [
        'subscription_id',
        'user_id',
        'amount',
        
        'transaction_id',
        'payment_status',
        'paid_at',
    ];

    protected $casts = [
        'payment_status' => 'boolean',
        'amount'         => 'decimal:2',
        'paid_at'        => 'datetime',
    ];

    /* ================== RELATIONSHIPS ================== */

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
