<?php

namespace App\Http\Controllers\Subscriptions;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;


class SubscriptionController extends Controller
{
    /**
     * Show subscription pricing page
     */


public function issubscribed()
{
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'status' => false,
            'message' => 'Unauthenticated'
        ], 401);
    }

    // ✅ Active subscription check
    $subscription = Subscription::where('user_id', $user->id)
        ->where('status', 'active')
        ->whereDate('end_date', '>=', Carbon::today())
        ->latest()
        ->first();

    if (!$subscription) {
        return response()->json([
            'status' => false,
            'is_subscribed' => false,
            'message' => 'No active subscription'
        ]);
    }

    return response()->json([
        'status' => true,
        'is_subscribed' => true,
        'subscription' => [
            'subscription_id' => $subscription->id,
            'plan_id'         => $subscription->plan_id,
            'start_date'      => $subscription->start_date->format('d-m-Y'),
            'end_date'        => $subscription->end_date->format('d-m-Y'),
            'remaining_days' => $subscription->remainingDays(),
            'auto_renew'     => (bool) $subscription->auto_renew,
        ]
    ]);
}
    public function index(Request $request)
    {
        // Active plans only
        $plans = SubscriptionPlan::active()
            ->with('features')
            ->orderBy('name')
            ->orderBy('billing_type') // monthly first, yearly after
            ->get()
            ->groupBy('name');

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }



   public function issubscribedwithlogin(Request $request)
{
    $user = Auth::user();

    // 🔍 Logged-in user ki active subscription
    $activeSubscription = null;

    if ($user) {
        $activeSubscription = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereDate('end_date', '>=', Carbon::today())
            ->latest()
            ->first();
    }

    // dd($activeSubscription);

    // ✅ Active plans
    $plans = SubscriptionPlan::active()
        ->with('features')
        ->orderBy('name')
        ->orderBy('billing_type') // monthly first  
        ->get()
        ->map(function ($plan) use ($activeSubscription) {

            // default values
            $plan->is_subscribed = false;
            $plan->bg_color = 'default';

            // 🟢 agar user ne ye plan liya hua hai
            if ($activeSubscription && $activeSubscription->plan_id == $plan->id) {
                $plan->is_subscribed = true;
                $plan->bg_color = 'green';
            }

            return $plan;
        })
        ->groupBy('name');

    return response()->json([
        'success' => true,
        'data' => $plans
    ]);
}

    
    /**
     * Get plans by billing type (monthly / yearly)
     */
    public function byBillingType(string $type)
    {
        if (!in_array($type, ['monthly', 'yearly'])) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid billing type'
            ], 422);
        }

        $plans = SubscriptionPlan::active()
            ->where('billing_type', $type)
            ->with('features')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans
        ]);
    }

    /**
     * Show single plan detail
     */
    public function show(int $id)
    {
        $plan = SubscriptionPlan::active()
            ->with('features')
            ->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $plan
        ]);
    }
}
