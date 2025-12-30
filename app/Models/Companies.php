<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class Companies extends Model
{
    //
 
     protected $fillable = [
        'user_id',
        'company_code',
        'company_name',
        'company_about',
        'company_business_email',
        'company_phone',
        'company_logo',
        'address',
        'linkdin_url',
        'pan_number',
        'pan_document',
        'gst_number',
        'gst_document',
        
    ];
}