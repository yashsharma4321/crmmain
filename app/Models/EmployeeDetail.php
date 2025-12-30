<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeDetail extends Model
{
    use HasFactory;

    protected $table = 'employee_details';

    protected $fillable = [
        'company_id',
        'client_id',
        'contract_id',
        'consignee_id',
        'designation_id',

        'name',
        'email',
        'mobile_no',
        'total_experience',
        'gender',
        'date_of_birth',
        'shift',

        'replaced_employee_id',

        'religion',
        'marital_status',
        'number_of_children',

        'ip_no',
        'uan_no',
        'aadhar_no',
        'pan_card_no',

        'address_proof',
        'reference',
        'about_employee',

        'status'
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'total_experience' => 'decimal:1',
        'number_of_children' => 'integer',
        'status' => 'boolean',
    ];

    /* =====================
        RELATIONSHIPS
    ===================== */

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function consignee()
    {
        return $this->belongsTo(ClientConsigneeDetail::class, 'consignee_id');
    }

    public function designation()
    {
        return $this->belongsTo(ClientServicesDesignation::class, 'designation_id');
    }

    // 🔁 Self reference (Replaced employee)
    public function replacedEmployee()
    {
        return $this->belongsTo(EmployeeDetail::class, 'replaced_employee_id');
    }

    // public function replacements()
    // {
    //     return $this->hasMany(EmployeeDetail::class, 'replaced_employee_id');
    // }
}
