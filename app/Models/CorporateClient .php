<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CorporateClient extends Model
{
    use HasFactory;

    protected $table = 'corporate_clients';

    // Fillable fields for mass assignment
    protected $fillable = [
        'user_id',
        'contract_no',
        'company_name',
        'business_type',
        'branch',
        'department',
        'organisation_name',
        'office_zone',
        'designation',
        'gstin',
        'contact_no',
        'landline_no',
        'email',
        'status',
        'inc_status',
        'address',
        'deal_start_date',
        'deal_end_Date',
        'number_of_employee',
        'number_of_designation',
        'gender',
        'number_of_working_days',
        'duty_hours_in_a_day',
        'type',
        'reason',
        'upload_document',
    ];

    /**
     * Relationship: CorporateClient belongs to a User
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
