<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    use HasFactory;

    protected $table = 'employees';

    protected $fillable = [
        'rand_id',
        'name',

        'company_id',
        'client_id',
        'consignee_id',
        'designation',
        'email',
        'mobile_no',
        'gender',
        'religion',
        'maritalStatus',
        'noOfChildren',
        'dateOfBirth',
        'refrence',
        'refer_employee',
        'ifd_concurrence',
        'gstin',
        'address',
        'ipn_no',
        'role',
        'designation_of_administrative_approval',
        'payment_mode',
        'designation_of_financial_approval',
        'profile_image',
        'cvupload',
        'familyName',
        'familyContact',
        'familyPhoto',
        'presentAddress',
        'uan_no',
        'pan_card',
        'pan_card_file',
        'rent_agreement',
        'rent_agreement_file',
        'about_employee',
        'contact_no',
        'presentCity',
        'presentState',
        'presentCountry',
        'permanentAddress',
        'permanentCity',
        'permanentState',
        'permanentCountry',
        'lastQualification',
        'qualificatio_year',
        'uploadMarksheet',
        'anyCertificate',
        'bankName',
        'branchLocation',
        'Selectshift',
        'totalExpence',
        'ipn_file',
        'uan_file',
        'policeVerification',
        'aadhar_no',
        'aadhar_no_file',
        'ifsc',
        'accountNo',
        'reason',
        'type',
        'upload_document',
        'status',
    ];

    public function company()
    {
        return $this->belongsTo(Companies::class);
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
   
    
}
