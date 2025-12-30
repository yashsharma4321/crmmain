<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionPlan extends Model
{
    use HasFactory;

    protected $table = 'subscription_plans';

    protected $fillable = [
        'name',
        'billing_type',   // monthly | yearly
        'price',
        'duration_days',
        'description',
        'is_active',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    /* =========================
       Scopes
    ========================= */

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeMonthly($query)
    {
        return $query->where('billing_type', 'monthly');
    }

    public function scopeYearly($query)
    {
        return $query->where('billing_type', 'yearly');
    }

    /* =========================
       Helpers
    ========================= */

    public function isMonthly()
    {
        return $this->billing_type === 'monthly';
    }

    public function isYearly()
    {
        return $this->billing_type === 'yearly';
    }

    public function formattedPrice()
    {
        return '₹' . number_format($this->price, 2);
    }

    public function perMonthPrice()
    {
        if ($this->isYearly()) {
            return round($this->price / 12, 2);
        }

        return $this->price;
    }

    /* =========================
       Relationships
    ========================= */

    public function features()
    {
        return $this->hasMany(SubscriptionFeature::class, 'plan_id');
    }
}
