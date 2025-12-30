<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientServicesDesignation extends Model
{
    use HasFactory;

    protected $table = 'client_services_desigination';

    protected $fillable = [
        'client_id',
        'consignee_id',
        'name',
        'skill',
        'qualification',
        'experience_in_years',
        'hire_employee'
        // 'type'
    ];

    /**
     * Service / Designation belongs to Consignee
     */
    public function consignee()
    {
        return $this->belongsTo(ClientConsigneeDetail::class, 'consignee_id');
    }

    /**
     * Also belongs to Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }
}
