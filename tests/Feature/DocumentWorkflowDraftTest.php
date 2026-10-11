<?php

namespace Tests\Feature;

use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\SupplierTtInvoiceController;
use App\Models\GoodsReceipt;
use App\Models\SupplierTtInvoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class DocumentWorkflowDraftTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        if (!in_array('sqlite', \PDO::getAvailableDrivers(), true)) {
            $this->markTestSkipped('PDO SQLite is required for the isolated test database.');
        }

        parent::setUp();
    }

    public function test_good_receipt_can_be_saved_as_an_empty_partial_draft(): void
    {
        $request = Request::create('/goods-receipts', 'POST', ['submit_action' => 'draft']);

        app(GoodsReceiptController::class)->store($request);

        $receipt = GoodsReceipt::sole();
        $this->assertSame('draft', $receipt->status);
        $this->assertNull($receipt->received_date);
        $this->assertNull($receipt->supplier_id);
        $this->assertCount(0, $receipt->invoices);
    }

    public function test_supplier_tt_invoice_can_be_saved_as_an_empty_partial_draft(): void
    {
        $request = Request::create('/supplier-tt-invoices', 'POST', ['submit_action' => 'draft']);

        app(SupplierTtInvoiceController::class)->store($request);

        $invoice = SupplierTtInvoice::sole();
        $this->assertSame('draft', $invoice->status);
        $this->assertNull($invoice->supplier_id);
        $this->assertNull($invoice->invoice_number);
        $this->assertCount(0, $invoice->photos);
    }

    public function test_rejecting_good_receipt_requires_a_reason(): void
    {
        $admin = User::create([
            'username' => 'workflow-admin',
            'password' => 'secret-password',
            'role' => 'admin',
        ]);
        $receipt = GoodsReceipt::create([
            'status' => 'waiting_approval',
            'evaluation_status' => 'matched',
        ]);
        $this->actingAs($admin);

        try {
            app(GoodsReceiptController::class)->reject(
                Request::create('/goods-receipts/'.$receipt->id.'/reject', 'POST'),
                $receipt
            );
            $this->fail('ValidationException was not thrown.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('evaluation_note', $exception->errors());
        }

        $this->assertSame('waiting_approval', $receipt->fresh()->status);
    }
}
