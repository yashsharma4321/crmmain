<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\ClientConsigneeDetail;
use App\Models\ClientServicesDesignation;
use App\Models\Client;
use App\Models\ClientServiceDetail;
use Illuminate\Support\Facades\DB;

// use Illuminate\Support\Facades\Auth;

use App\Models\ClientFinancialApproval;
class ClientController extends Controller
{
    //
public function viewclient(Request $request)
{
    // ✅ Base validation
    $validator = Validator::make($request->all(), [
        'company_id' => 'required|exists:companies,id',
        'client_id'  => 'nullable|exists:clients,id',
        'client_type' => 'required',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    // 🔹 Single Client (client_id + company_id)
   if ($request->filled('client_id')) {

        $client = Client::with([
            'consignees.designations',
            'services.consignee.designations',
            'financialApproval'
        ])
        ->where('id', $request->client_id)
        ->where('company_id', $request->company_id)
        ->where('client_type',$request->client_type)
        ->first();

        if (!$client) {
            return response()->json([
                'status' => false,
                'message' => 'Client not found for this company'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'message' => 'Client details fetched successfully',
            'data' => $client
        ], 200);
    }

    // 🔹 Company-wise Client List
    $clients = Client::with([
            'consignees',
            'services',
            'financialApproval'
        ])
        ->where('company_id', $request->company_id)
                ->where('client_type',$request->client_type)

        ->orderBy('id', 'desc')
        ->get();
    // return $clients;
    if ($clients->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No clients found for this company',
            'data' => []
        ], 404);
    }

    return response()->json([
        'status' => true,
        'message' => 'Clients list fetched successfully',
        'count' => $clients->count(),
        'data' => $clients
    ], 200);
}

     public function ClientServicestore(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',

            'services' => 'required|array|min:1',

            'services.*.consignee_id' => 'required|exists:client_consignee_details,id',
            'services.*.designation_id' => 'required|exists:client_services_desigination,id',

            'services.*.gender' => 'nullable|string|max:50',
            'services.*.age_limit' => 'nullable|string|max:200',
            'services.*.education_qualification' => 'nullable|string|max:255',
            'services.*.specialization_for_pg' => 'nullable|string|max:255',
            'services.*.post_graduation' => 'nullable|string|max:255',
            'services.*.type_of_function' => 'nullable|string|max:255',
            'services.*.year_of_experience' => 'nullable|string|max:200',
            'services.*.specialization' => 'nullable|string|max:255',
            'services.*.skill_category' => 'nullable|string|max:200',
            'services.*.district' => 'nullable|string|max:255',
            'services.*.zip_code' => 'nullable|string|max:200',

            'services.*.duty_hours' => 'nullable|string|max:200',
            'services.*.duty_extra_hours' => 'nullable|string|max:50',

            'services.*.min_daily_wages' => 'nullable|string|max:200',
            'services.*.monthly_salary' => 'nullable|string|max:200',

            'services.*.bonus' => 'nullable|string|max:200',
            'services.*.provideant_fund' => 'nullable|string|max:200',
            'services.*.epf_admin_charge' => 'nullable|string|max:200',
            'services.*.edliPerDay' => 'nullable|string|max:200',
            'services.*.esiPerDay' => 'nullable|string|max:200',

            'services.*.optionAllowance1' => 'nullable|string|max:200',
            'services.*.optionAllowance2' => 'nullable|string|max:200',
            'services.*.optionAllowance3' => 'nullable|string|max:200',

            'services.*.no_of_working_day' => 'nullable|string|max:200',
            'services.*.tenure_duration' => 'nullable|string|max:200',
            'services.*.number_of_hire_resource' => 'nullable|string|max:200',
            'services.*.perecnt_service_charge' => 'nullable|string|max:200',
            'services.*.additional_requirement' => 'nullable|string|max:500',

            // boolean flags (true / false)
            'services.*.is_bonus_applicable' => 'required|boolean',
            'services.*.is_pf_applicable' => 'required|boolean',
            'services.*.is_epf_admin_charge_applicable' => 'required|boolean',
            'services.*.is_edli_applicable' => 'required|boolean',
            'services.*.is_esi_applicable' => 'required|boolean',
            'services.*.is_optional_allowance_1_applicable' => 'required|boolean',
            'services.*.is_optional_allowance_2_applicable' => 'required|boolean',
            'services.*.is_optional_allowance_3_applicable' => 'required|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            $savedServices = [];

            foreach ($request->services as $service) {

                $savedServices[] = ClientServiceDetail::create([
                    'client_id' => $request->client_id,

                    'consignee_id' => $service['consignee_id'],
                    'designation_id' => $service['designation_id'],

                    'gender' => $service['gender'] ?? null,
                    'age_limit' => $service['age_limit'] ?? null,
                    'education_qualification' => $service['education_qualification'] ?? null,
                    'specialization_for_pg' => $service['specialization_for_pg'] ?? null,
                    'post_graduation' => $service['post_graduation'] ?? null,
                    'type_of_function' => $service['type_of_function'] ?? null,
                    'year_of_experience' => $service['year_of_experience'] ?? null,
                    'specialization' => $service['specialization'] ?? null,
                    'skill_category' => $service['skill_category'] ?? null,
                    'district' => $service['district'] ?? null,
                    'zip_code' => $service['zip_code'] ?? null,

                    'duty_hours' => $service['duty_hours'] ?? null,
                    'duty_extra_hours' => $service['duty_extra_hours'] ?? null,

                    'min_daily_wages' => $service['min_daily_wages'] ?? null,
                    'monthly_salary' => $service['monthly_salary'] ?? null,

                    'bonus' => $service['bonus'] ?? null,
                    'provideant_fund' => $service['provideant_fund'] ?? null,
                    'epf_admin_charge' => $service['epf_admin_charge'] ?? null,
                    'edliPerDay' => $service['edliPerDay'] ?? null,
                    'esiPerDay' => $service['esiPerDay'] ?? null,

                    'optionAllowance1' => $service['optionAllowance1'] ?? null,
                    'optionAllowance2' => $service['optionAllowance2'] ?? null,
                    'optionAllowance3' => $service['optionAllowance3'] ?? null,

                    'no_of_working_day' => $service['no_of_working_day'] ?? null,
                    'tenure_duration' => $service['tenure_duration'] ?? null,
                    'number_of_hire_resource' => $service['number_of_hire_resource'] ?? null,
                    'perecnt_service_charge' => $service['perecnt_service_charge'] ?? null,
                    'additional_requirement' => $service['additional_requirement'] ?? null,

                    'is_bonus_applicable' => $service['is_bonus_applicable'],
                    'is_pf_applicable' => $service['is_pf_applicable'],
                    'is_epf_admin_charge_applicable' => $service['is_epf_admin_charge_applicable'],
                    'is_edli_applicable' => $service['is_edli_applicable'],
                    'is_esi_applicable' => $service['is_esi_applicable'],
                    'is_optional_allowance_1_applicable' => $service['is_optional_allowance_1_applicable'],
                    'is_optional_allowance_2_applicable' => $service['is_optional_allowance_2_applicable'],
                    'is_optional_allowance_3_applicable' => $service['is_optional_allowance_3_applicable'],
                ]);
            }

            DB::commit();
            
                return response()->json([
                    'status' => true,
                    'message' => 'Client services saved successfully',
                    'data' => $savedServices
                ], 201);
                
            } 
            
            catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong',
                'error' => $e->getMessage()
            ], 500);
        }
    }



   public function consignee(Request $request)
{

    // dd(
    //     'sd'
    // );
    $user = Auth::user();

    $client_id = $request->client_id;
  
    $validator = Validator::make($request->all(), [

        'client_id' => 'required|exists:clients,id'
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    // ✅ Fetch consignees with their designations
    $consignees = ClientConsigneeDetail::where('client_id', $request->client_id)->select('consignee_name','id')->get();

    if ($consignees->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No consignees found for this client',
            'data' => []
        ], 404);
    }

    return response()->json([
        'status' => true,
        'message' => 'Consignee list fetched successfully',
        'count' => $consignees->count(),
        'data' => $consignees
    ], 200);
}


public function designation(Request $request)
{
    $validator = Validator::make($request->all(), [
        'consignee_id' => 'required|exists:client_consignee_details,id',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422);
    }

    $designations = ClientServicesDesignation::where(
        'consignee_id',
        $request->consignee_id
    )->select('id','name')->get();

    if ($designations->isEmpty()) {
        return response()->json([
            'status' => false,
            'message' => 'No designations found for this consignee',
            'data' => []
        ], 404);
    }

    return response()->json([
        'status' => true,
        'message' => 'Designations fetched successfully',
        'count' => $designations->count(),
        'data' => $designations
    ], 200);
}


public function addClientConsignees(Request $request)
{
    $validator = Validator::make($request->all(), [
        'client_id' => 'required|exists:clients,id',
        'consignees' => 'required|array|min:1',

        'consignees.*.id' => 'nullable|exists:client_consignee_details,id',

        'consignees.*.dealing_hand_name' => 'required|string|max:200',
        'consignees.*.dealing_email' => 'required|email|max:200',
        'consignees.*.dealing_contact' => 'required|string|max:20',
        'consignees.*.dealing_designation' => 'required|string|max:200',

        'consignees.*.consignee_name' => 'required|string|max:255',
        'consignees.*.consigness_designation' => 'nullable|string|max:255',
        'consignees.*.consignee_contact_no' => 'nullable|string|max:15',
        'consignees.*.consignee_email' => 'nullable|email|max:255',
        'consignees.*.consignee_gstin' => 'nullable|string|max:20',
        'consignees.*.consignee_addess' => 'nullable|string|max:500',

        'consignees.*.designations' => 'nullable|array',
        'consignees.*.designations.*.id' => 'nullable|exists:client_services_designations,id',
        'consignees.*.designations.*.name' => 'required|string|max:255',
        'consignees.*.designations.*.skill' => 'nullable|string|max:255',
        'consignees.*.designations.*.qualification' => 'nullable|string|max:255',
        'consignees.*.designations.*.experience_in_years' => 'nullable|numeric',
        'consignees.*.designations.*.hire_employee' => 'nullable|integer',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    DB::beginTransaction();

    try {
        $client = Client::findOrFail($request->client_id);
        $savedConsignees = [];

        foreach ($request->consignees as $c) {

            // ✅ Create or Update Consignee
            $consignee = ClientConsigneeDetail::updateOrCreate(
                [
                    'id' => $c['id'] ?? null,
                ],
                [
                    'client_id' => $client->id,
                    'consignee_name' => $c['consignee_name'],
                    'consigness_designation' => $c['consigness_designation'] ?? null,
                    'consignee_contact_no' => $c['consignee_contact_no'] ?? null,
                    'consignee_email' => $c['consignee_email'] ?? null,
                    'consignee_gstin' => $c['consignee_gstin'] ?? null,
                    'consignee_addess' => $c['consignee_addess'] ?? null,
                    'dealing_hand_name' => $c['dealing_hand_name'],
                    'dealing_email' => $c['dealing_email'],
                    'dealing_contact' => $c['dealing_contact'],
                    'dealing_designation' => $c['dealing_designation'],
                ]
            );

            // 🔹 Handle Designations
            if (!empty($c['designations'])) {
                foreach ($c['designations'] as $d) {
                    ClientServicesDesignation::updateOrCreate(
                        [
                            'id' => $d['id'] ?? null
                        ],
                        [
                            'client_id' => $client->id,
                            'consignee_id' => $consignee->id,
                            'name' => $d['name'],
                            'skill' => $d['skill'] ?? null,
                            'qualification' => $d['qualification'] ?? null,
                            'experience_in_years' => $d['experience_in_years'] ?? null,
                            'hire_employee' => $d['hire_employee'] ?? null,
                        ]
                    );
                }
            }

            $savedConsignees[] = $consignee->load('designations');
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'message' => 'Client consignees updated successfully',
            'data' => $savedConsignees
        ], 200);

    } catch (\Exception $e) {
        DB::rollBack();

        return response()->json([
            'status' => false,
            'message' => 'Failed to save consignees',
            'error' => $e->getMessage()
        ], 500);
    }
}

public function createclient(Request $request)
{
    $user = Auth::user();

    $validator = Validator::make($request->all(), [

        'client_id'             => 'nullable|exists:clients,id',

        'contract_no'           => 'required|string|max:200',
        'company_id'            => 'required',
        'service_title'         => 'required|string|max:255',
        'onboard_date'          => 'required|date',
        'bid_no'                => 'required|string|max:200',
        'service_start_date'    => 'required|date',
        'service_end_date'      => 'required|date|after_or_equal:service_start_date',

        'customer_name'         => 'required|string|max:255',
        'client_type'           => 'nullable|string|max:100',
        'type'                  => 'required|string|max:100',

        'ministry'              => 'nullable|string|max:255',
        'department'            => 'nullable|string|max:255',
        'department_nickname'   => 'nullable|string|max:255',

        'organisation_name'     => 'required|string|max:255',
        'office_zone'           => 'nullable|string|max:100',

        'buyer_name'            => 'required|string|max:255',
        'designation'           => 'required|string|max:150',
        'contact_no'            => 'required|digits_between:10,15',
        'email'                 => 'required|email|max:255',

        'gstin' => [
            'required',
            'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
        ],

        'address'               => 'required|string|max:500',
        'apply_gst'             => 'required|boolean',
        'apply_cgst_sgst'        => 'required|boolean',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    // 🔥 Create or Update
    $client = Client::updateOrCreate(
        ['id' => $request->client_id],   // condition
        array_merge(
            $validator->validated(),
            [
                'user_id' => $user->id,
                'status'  => $request->status ?? 1
            ]
        )
    );

    $message = $request->client_id
        ? 'Client updated successfully'
        : 'Client created successfully';

    return response()->json([
        'status'  => true,
        'message' => $message,
        'data'    => $client
    ], 200);
}

public function clientfinancial(Request $request)
{
    $validator = Validator::make($request->all(), [

        'client_id' => 'required|exists:clients,id',

        'ifd_concurrence' => 'required|string|max:255',
        'designation_admin_approval' => 'required|string|max:255',
        'designation_financial_approval' => 'required|string|max:255',
        'role' => 'required|string|max:150',
        'payment_mode' => 'required|string|max:100',
        'designation' => 'required|string|max:150',
        'email' => 'required|email|max:255',

        'gstin' => [
            'required',
            'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z]{1}[1-9A-Z]{1}Z[0-9A-Z]{1}$/'
        ],

        'address' => 'required|string|max:500',
    ]);

    if ($validator->fails()) {
        return response()->json([
            'status' => false,
            'errors' => $validator->errors()
        ], 422);
    }

    // ✅ Create or Update
    $financial = ClientFinancialApproval::updateOrCreate(
        ['client_id' => $request->client_id], // condition
        [
            'ifd_concurrence' => $request->ifd_concurrence,
            'designation_admin_approval' => $request->designation_admin_approval,
            'designation_financial_approval' => $request->designation_financial_approval,
            'role' => $request->role,
            'payment_mode' => $request->payment_mode,
            'designation' => $request->designation,
            'email' => $request->email,
            'gstin' => $request->gstin,
            'address' => $request->address,
        ]
    );

    return response()->json([
        'status' => true,
        'message' => 'Client financial details saved successfully',
        'data' => $financial
    ], 200);
}

}
