<?php
namespace App\Models;

use CodeIgniter\Model;

class InvoiceModel extends Model
{
    protected $table      = 'invoices';
    protected $primaryKey = 'id';

    protected $allowedFields = [
        'customer_name',
        'customer_email',
        'invoice_date',
        'total_amount',
        'status',
        'created_by',
        'created_at',
    ];
}
