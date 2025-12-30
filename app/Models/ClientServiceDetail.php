<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientServiceDetail extends Model
{
    use HasFactory;

    protected $table = 'client_services_details';

    protected $fillable = [
        'client_id',

        // FK IDs
        'consignee_id',
        'designation_id',

        'list_of_profile',
        'gender',
        'age_limit',
        'education_qualification',
        'specialization_for_pg',
        'post_graduation',
        'type_of_function',
        'year_of_experience',
        'specialization',
        'skill_category',
        'district',
        'zip_code',
        'duty_hours',
        'duty_extra_hours',
        'min_daily_wages',
        'monthly_salary',

        'bonus',
        'provideant_fund',
        'epf_admin_charge',
        'edliPerDay',
        'esiPerDay',

        'optionAllowance1',
        'optionAllowance2',
        'optionAllowance3',

        'no_of_working_day',
        'tenure_duration',
        'number_of_hire_resource',
        'perecnt_service_charge',
        'additional_requirement',
        'type',

        // Boolean flags
        'is_bonus_applicable',
        'is_pf_applicable',
        'is_epf_admin_charge_applicable',
        'is_edli_applicable',
        'is_esi_applicable',
        'is_optional_allowance_1_applicable',
        'is_optional_allowance_2_applicable',
        'is_optional_allowance_3_applicable',
    ];

    protected $casts = [
        'is_bonus_applicable' => 'boolean',
        'is_pf_applicable' => 'boolean',
        'is_epf_admin_charge_applicable' => 'boolean',
        'is_edli_applicable' => 'boolean',
        'is_esi_applicable' => 'boolean',
        'is_optional_allowance_1_applicable' => 'boolean',
        'is_optional_allowance_2_applicable' => 'boolean',
        'is_optional_allowance_3_applicable' => 'boolean',
    ];

    /*
    |--------------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------------
    */

    public function client()
    {
        return $this->belongsTo(User::class, 'client_id');
    }

    public function consignee()
    {
        return $this->belongsTo(ClientConsigneeDetail::class, 'consignee_id');
    }

    public function designation()
    {
        return $this->belongsTo(ClientServicesDesignation::class, 'designation_id');
    }
}
