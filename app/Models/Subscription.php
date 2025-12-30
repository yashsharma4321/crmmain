<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'user_id',
        'plan_id',
        'start_date',
        'end_date',
        'status',       // active / expired / cancelled
        'auto_renew',   // 0 = false, 1 = true
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'auto_renew' => 'boolean',
    ];

    /* =========================
       Relationships
    ========================= */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }

    public function payments()
    {
        return $this->hasMany(SubscriptionPayment::class, 'subscription_id');
    }

    /* =========================
       Helpers
    ========================= */

    public function isActive()
    {
        return $this->status === 'active' && $this->end_date->gte(Carbon::today());
    }

    public function isExpired()
    {
        return $this->status === 'expired' || $this->end_date->lt(Carbon::today());
    }

    public function remainingDays()
    {
        if ($this->isExpired()) {
            return 0;
        }

        return $this->end_date->diffInDays(Carbon::today());
    }
}
