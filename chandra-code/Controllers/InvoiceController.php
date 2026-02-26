<?php
namespace App\Controllers;

use App\Models\InvoiceModel;
use CodeIgniter\RESTful\ResourceController;
use Mpdf\Mpdf;

class InvoiceController extends ResourceController
{
    protected $format = 'json';

    public function create()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $user = session();

        $model = new InvoiceModel();

        $data = $this->request->getJSON(true);

        $model->save([
            'customer_name'  => $data['customer_name'],
            'customer_email' => $data['customer_email'],
            'invoice_date'   => $data['invoice_date'],
            'total_amount'   => $data['total_amount'],
            'status'         => $data['status'],
            'created_by'     => $user->get('id'),
        ]);

        return $this->respond(['message' => 'Invoice Created']);
    }

    public function index()
    {
        error_reporting(E_ALL);
        ini_set('display_errors', 1);
        $model   = new InvoiceModel();
        $status  = $this->request->getGet('status');
        $page    = $this->request->getGet('page') ?? 1;
        $perPage = $this->request->getGet('per_page') ?? 5;

        if ($status) {
            $model->where('status', $status)->findAll();
        }
        $invoices    = $model->paginate($perPage, 'default', $page);
        $pager       = $model->pager;
        $currentPage = $pager->getCurrentPage();
        $totalPages  = $pager->getPageCount();
        return $this->respond([
            'status'     => true,
            'data'       => $invoices,
            'pagination' => [
                'current_page'  => $currentPage,
                'per_page'      => $perPage,
                'total_records' => $pager->getTotal(),
                'total_pages'   => $totalPages,
                'next_page'     => $currentPage < $totalPages ? $currentPage + 1 : null,
                'prev_page'     => $currentPage > 1 ? $currentPage - 1 : null,
            ],
        ]);
    }

    public function show($id = null)
    {
        $model   = new InvoiceModel();
        $invoice = $model->find($id);

        if (! $invoice) {
            return $this->failNotFound('Invoice not found');
        }

        return $this->respond($invoice);
    }

    public function update($id = null)
    {
        $model = new InvoiceModel();
        $data  = $this->request->getJSON(true);

        $model->update($id, $data);

        return $this->respond(['message' => 'Invoice Updated']);
    }

    // ✅ Delete Invoice (Admin Only)
    public function delete($id = null)
    {
        $role = session()->get('role');

        if ($role !== 'Admin') {
            return $this->failForbidden('Only Admin can delete invoice');
        }

        $model = new InvoiceModel();
        $model->delete($id);

        return $this->respond(['message' => 'Invoice Deleted']);
    }

    public function downloadPDF($id)
    {
        $model   = new InvoiceModel();
        $invoice = $model->find($id);

        if (! $invoice) {
            return $this->failNotFound('Invoice not found');
        }

        $mpdf = new Mpdf([
            'margin_top'    => 35,
            'margin_bottom' => 25,
        ]);
        $mpdf->SetHTMLHeader('


            <div  style="text-align:center;  border-bottom: 1px solid #000; padding-bottom: 10px;">

                <h2> TCS Pvt Ltd</h2>
                <p>Haldwani, Uttarakhand</p>
                <p>Email: support@gmail.com | Phone: +91-0000000</p>
<img style="margin-left:600px;padding-top:-100px;" src="https://media.istockphoto.com/id/1415537851/photo/asian-graphic-designer-working-in-office-artist-creative-designer-illustrator-graphic-skill.jpg?s=1024x1024&w=is&k=20&c=0nK1oKWKmt-D3o30fc79BXn9r9mWDLspPrdZIof4YCE=" width="200px" height="100px">

 <br><br> <br><br> <br><br>
 <br><br> <br><br> <br><br>
 <br><br> <br><br> <br><br>
            </div>

        ');

        $mpdf->SetHTMLFooter('
            <div style="text-align: center; border-top: 1px solid #000; font-size: 12px;">
                Page {PAGENO} of {nb}
            </div>
        ');

        $html = "
        <h2>Invoice #{$invoice['id']}</h2>
        <h2>Company Name: TCS</h2>
        <p><strong>Customer:</strong> {$invoice['customer_name']}</p>
        <p><strong>Email:</strong> {$invoice['customer_email']}</p>
        <p><strong>Date:</strong> {$invoice['invoice_date']}</p>
        <p><strong>Total:</strong> ₹ {$invoice['total_amount']}</p>
        <p><strong>Status:</strong> {$invoice['status']}</p>
    ";

        $mpdf->WriteHTML($html);

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setBody($mpdf->Output("invoice_{$id}.pdf", 'S'));
    }
}
