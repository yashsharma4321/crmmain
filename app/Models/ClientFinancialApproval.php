<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientFinancialApproval extends Model
{
    use HasFactory;

    protected $table = 'client_financial_approvals';

    protected $fillable = [
        'client_id',
        'ifd_concurrence',
        'designation_admin_approval',
        'designation_financial_approval',
        'role',
        'payment_mode',
        'designation',
        'email',
        'gstin',
        'address'
    ];

    /**
     * One-to-One: Financial Approval belongs to a Client
     */
    public function client()
    {
        return $this->belongsTo(Client::class, 'client_id');
    }
}
