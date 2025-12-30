<?php
 
namespace App\Http\Controllers\Companies;
 
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
 
use App\Models\Companies;
use Illuminate\Support\Str;
 
use Illuminate\Support\Facades\Storage;
// use Illuminate\Support\Str;
use App\Models\Purcheses;

use Illuminate\Support\Facades\Validator;
 
class CompanyController extends Controller
{
   


    public function viewcompany(){
    $user = Auth::user();
    $user_id = $user->id;
    $companies = Companies::where('user_id',$user_id)->get();
    return $companies;
    return $user_id;

    }
public function create(Request $request)
{
    $validator = Validator::make($request->all(), [
        'company_name'             => 'required|string|max:255',
        'company_about'            => 'nullable|string',
        'company_business_email'   => 'required|email|unique:companies,company_business_email',
        'company_phone'            => 'required|string|max:20',

        'company_logo'             => 'nullable|file|mimes:jpg,jpeg,png|max:2048',
        'address'                  => 'nullable|string',
        'linkdin_url'              => 'nullable|url',

        'pan_number'               => 'required|string|max:20|unique:companies,pan_number',
        'pan_document'             => 'required|file|mimes:pdf,jpg,jpeg,png',

        'gst_number'               => 'required|string|max:20|unique:companies,gst_number',
        'gst_document'             => 'required|file|mimes:pdf,jpg,jpeg,png',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => 'error',
            'message' => 'Validation failed.',
            'errors'  => $validator->errors(),
        ], 422);
    }

    // 🔢 Company Code
    $lastId = Companies::max('id');
    $nextId = $lastId ? $lastId + 1 : 1;

    $prefix = env('CRM_CODE', 'CRM-');
    $slug   = Str::upper(Str::slug($request->company_name, '-'));
    $companyCode = $prefix . $slug . '-' . $nextId;

    // 📁 File uploads
    $companyLogoPath = $request->hasFile('company_logo')
        ? $request->file('company_logo')->store('companies/logos', 'public')
        : null;

    $panDocPath = $request->file('pan_document')
        ->store('companies/pan_documents', 'public');

    $gstDocPath = $request->file('gst_document')
        ->store('companies/gst_documents', 'public');

    $user = Auth::user();

    $company = Companies::create([
        'user_id'                => $user->id,
        'company_code'           => $companyCode,
        'company_name'           => $request->company_name,
        'company_about'          => $request->company_about,
        'company_business_email' => $request->company_business_email,
        'company_phone'          => $request->company_phone,
        'company_logo'           => $companyLogoPath,
        'address'                => $request->address,
        'linkdin_url'            => $request->linkdin_url,
        'pan_number'             => $request->pan_number,
        'pan_document'           => $panDocPath,
        'gst_number'             => $request->gst_number,
        'gst_document'           => $gstDocPath,
    ]);

    return response()->json([
        'status'  => 'success',
        'message' => 'Company created successfully',
        'data'    => $company
    ], 201);
}
 
}