<?php

namespace App\Http\Controllers\Subscriptions;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use App\Models\SubscriptionPayment;
use Illuminate\Support\Facades\DB;

use App\Models\Subscription;
use App\Models\SubscriptionPlan;

class PurchaseController extends Controller
{
   public function purchaseSubscription(Request $request)
{

    // return $request->All();
    $user = Auth::user();

    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'Unauthenticated'
        ], 401);
    }

    // ✅ Validation
    $validator = Validator::make($request->all(), [
        'plan_id'         => 'required|integer|exists:subscription_plans,id',
        'billing_type'    => 'required|in:monthly,yearly',
        'auto_renew'      => 'nullable|boolean',

        // payment fields
        'amount'          => 'required|numeric',
      
        'transaction_id'  => 'required|string',
        'payment_status'  => 'required|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422);
    }

  
    $plan = SubscriptionPlan::findOrFail($request->plan_id);

    DB::beginTransaction();

    try {
        // 📅 Dates
        $startDate = Carbon::today();
        $endDate = $request->billing_type === 'monthly'
            ? $startDate->copy()->addMonth()
            : $startDate->copy()->addYear();

        // ❌ block multiple active subscriptions
        $alreadyActive = Subscription::where('user_id', $user->id)
            ->where('status', 'active')
            ->whereDate('end_date', '>=', Carbon::today())
            ->exists();

        if ($alreadyActive) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'You already have an active subscription'
            ], 400);
        }

        // ✅ Create Subscription
        $subscription = Subscription::create([
            'user_id'    => $user->id,
            'plan_id'    => $plan->id,
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'status'     => 'active',
            'auto_renew' => $request->auto_renew ?? false,
        ]);

        // ✅ Create Payment
        SubscriptionPayment::create([
            'subscription_id' => $subscription->id,
            'user_id'         => $user->id,
            'amount'          => $request->amount,
          
            'transaction_id'  => $request->transaction_id,
            'payment_status'  => $request->payment_status,
            'paid_at'         => now(),
        ]);

        // ✅ Commit Transaction
        DB::commit();

        return response()->json([
            'status'  => 'success',
            'message' => 'Subscription purchased & payment saved successfully',
            'data'    => [
                'subscription_id' => $subscription->id,
                'plan_id'         => $plan->id,
                'billing_type'    => $request->billing_type,
                'amount'          => $request->amount,
                'payment_status'  => $request->payment_status,
                'start_date'      => $subscription->start_date->format('d-m-Y'),
                'end_date'        => $subscription->end_date->format('d-m-Y'),
            ]
        ]);

    } catch (\Exception $e) {

        DB::rollBack();

        return response()->json([
            'status'  => 'error',
            'message' => 'Transaction failed',
            'error'   => $e->getMessage() // production me hata dena
        ], 500);
    }
}
}