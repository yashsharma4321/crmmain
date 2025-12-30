<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Models\EmployeeDetail;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
class EmployeeController extends Controller
{
    /**
     * 📌 Store Employee
     */
    // public function store(Request $request)
    // {
    //     $validator = Validator::make($request->all(), [
    //         'company_id' => 'required|exists:companies,id',
    //         'client_id' => 'required|exists:clients,id',
    //         'contract_id' => 'required',

    //         'consignee_id' => 'required|exists:client_consignee_details,id',
    //         'designation_id' => 'required|exists:client_services_desigination,id',

    //         'name' => 'required|string|max:150',
    //         'email' => 'required|email|max:150',
    //         'mobile_no' => 'required|digits_between:10,15',

    //         'total_experience' => 'required|numeric|min:0',
    //         'gender' => 'required|in:male,female,other',
    //         'date_of_birth' => 'required|date',

    //         'shift' => 'nullable|string|max:100',
    //         'replaced_employee_id' => 'nullable|exists:employee_details,id',

    //         'religion' => 'required|string|max:100',
    //         'marital_status' => 'required|in:single,married,divorced,widowed',
    //         'number_of_children' => 'nullable|integer|min:0',

    //         'ip_no' => 'required|string|max:50',
    //         'uan_no' => 'required|string|max:50',
    //         'aadhar_no' => 'required|string|max:20',
    //         'pan_card_no' => 'required|string|max:20',
    //         'type' => 'required',
    //         // 'address_proof' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',

    //         'reference' => 'required|string',
    //         'about_employee' => 'required|string'
    //     ]);

    //     if ($validator->fails()) {
    //         return response()->json([
    //             'status' => false,
    //             'errors' => $validator->errors()
    //         ], 422);
    //     }

    //     DB::beginTransaction();

    //     try {
    //         // 📂 Upload file
    //         $path = null;
    //         if ($request->hasFile('address_proof')) {
    //             $path = $request->file('address_proof')
    //                 ->store('employee/address_proof', 'public');
    //         }

    //         $employee = EmployeeDetail::create([
    //             ...$validator->validated(),
    //             'address_proof' => $path,
    //             'status' => 1
    //         ]);

    //         DB::commit();

    //         return response()->json([
    //             'status' => true,
    //             'message' => 'Employee created successfully',
    //             'data' => $employee
    //         ], 201);

    //     } catch (\Exception $e) {
    //         DB::rollBack();

    //         return response()->json([
    //             'status' => false,
    //             'message' => 'Something went wrong',
    //             'error' => $e->getMessage()
    //         ], 500);
    //     }
    // }


   public function store(Request $request)
{
    $validator = Validator::make($request->all(), [
        'name'         => 'required|string|max:255',
        'email'        => 'required|email|unique:employees,email',
        'mobile_no'    => 'required|string|max:15|unique:employees,mobile_no',
        'company_id'   => 'required|exists:companies,id',
        'client_id'    => 'required|exists:clients,id',
        'consignee_id' => 'nullable|exists:client_consignee_details,id',
        'designation'  => 'required|exists:client_services_desigination,id',
        'profile_image'=> 'nullable|image|mimes:jpg,jpeg,png|max:2048',
    ], [
        'client_id.exists'     => 'Selected client does not exist.',
        'consignee_id.exists'  => 'Selected consignee does not exist.',
        'designation.exists'   => 'Selected designation does not exist.',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status'  => false,
            'message' => 'Validation error',
            'errors'  => $validator->errors()
        ], 422);
    }

    // 🔹 Image Upload
    $profileImagePath = null;
    if ($request->hasFile('profile_image')) {
        $image = $request->file('profile_image');
        $profileImagePath = $image->store('employees', 'public');
    }

    $employee = Employee::create([
        'rand_id'          => 'EMP' . strtoupper(Str::random(6)),
        'company_id'       => $request->company_id,
        'name'             => $request->name,
        'client_id'        => $request->client_id,
        'consignee_id'     => $request->consignee_id,
        'designation'      => $request->designation,
        'email'            => $request->email,
        'mobile_no'        => $request->mobile_no,
        'gender'           => $request->gender,
        'religion'         => $request->religion,
        'maritalStatus'    => $request->maritalStatus,
        'noOfChildren'     => $request->noOfChildren,
        'dateOfBirth'      => $request->dateOfBirth,
        'refrence'         => $request->refrence,
        'refer_employee'   => $request->refer_employee,
        'role'             => $request->role,
        'address'          => $request->address,
        'presentCity'      => $request->presentCity,
        'presentState'     => $request->presentState,
        'presentCountry'   => $request->presentCountry,
        'permanentAddress' => $request->permanentAddress,
        'permanentCity'    => $request->permanentCity,
        'permanentState'   => $request->permanentState,
        'permanentCountry' => $request->permanentCountry,
        'bankName'         => $request->bankName,
        'ifsc'             => $request->ifsc,
        'accountNo'        => $request->accountNo,
        'profile_image'    => $profileImagePath,
        'status'           => 1,
        'type'             => $request->type,
    ]);

    return response()->json([
        'status'  => true,
        'message' => 'Employee created successfully',
        'data'    => $employee
    ], 201);
}

 
 public function index(Request $request)
{
    $employees = Employee::where('company_id', $request->company_id)
        ->when($request->filled('client_id'), function ($query) use ($request) {
            $query->where('client_id', $request->client_id);
        })
        ->select('id', 'name', 'profile_image')
        ->orderBy('id', 'desc')
        ->get();

    return response()->json([
        'status' => true,
        'count'  => $employees->count(),
        'data'   => $employees
    ]);
}



    /**
     * 📌 Single Employee
     */
    public function show($id)
    {
        $employee = EmployeeDetail::with([
            'company',
            'client',
            'consignee',
            'designation',
            'replacedEmployee'
        ])->find($id);

        if (!$employee) {
            return response()->json([
                'status' => false,
                'message' => 'Employee not found'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $employee
        ]);
    }
}
