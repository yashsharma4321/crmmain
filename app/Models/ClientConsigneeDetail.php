<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientConsigneeDetail extends Model
{
    use HasFactory;

    protected $table = 'client_consignee_details';

    protected $fillable = [
        'client_id',
        'consignee_name',
        'consigness_designation',
        'consignee_contact_no',
        'consignee_email',
        'consignee_gstin',
        'consignee_addess',
        // 'type',
        'dealing_hand_name',
        'dealing_designation',
        'dealing_contact',
        'dealing_email',
    ];

    /**
     * Consignee belongs to Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * Consignee has many Designations / Services
     */
    public function designations()
    {
        return $this->hasMany(ClientServicesDesignation::class, 'consignee_id');
    }
}
    