<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionFeature extends Model
{
    use HasFactory;

    protected $table = 'subscription_features';

    protected $fillable = [
        'plan_id',
        'feature_name',
        'feature_value',
    ];

    /* =========================
       Relationships
    ========================= */

    public function plan()
    {
        return $this->belongsTo(SubscriptionPlan::class, 'plan_id');
    }
}
