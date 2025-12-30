<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $table = 'clients';

    // Mass assignable fields
    protected $fillable = [
        'user_id',
        'company_id',
        'contract_no',
        'service_title',
        'onboard_date',
        'bid_no',
        'service_start_date',
        'service_end_date',
      'client_type',
        'customer_name',
        'type',
        'ministry',
        'department',
        'department_nickname',
        'organisation_name',
        'office_zone',
        'buyer_name',
        'designation',
        'dealing_hand_name',
        'dealing_email',
        'dealing_contact',
        'dealing_designation',
        'contact_no',
        'email',
        'gstin',
        'address',
        'apply_gst',
        'apply_cgst_sgst'
    ];

    /**
     * Client belongs to User
     */

    public function financialApproval()
{
    return $this->hasOne(ClientFinancialApproval::class, 'client_id');
}

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    
public function services()
{
    return $this->hasMany(ClientServiceDetail::class);
}

    // Client has many consignees
public function consignees()
{
    return $this->hasMany(ClientConsigneeDetail::class);
}

}
