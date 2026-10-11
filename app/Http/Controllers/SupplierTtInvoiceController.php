<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\Branches;
use App\Models\Supplier;
use App\Models\SupplierTtInvoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Throwable;

class SupplierTtInvoiceController extends Controller
{
    private const DEDUCTION_TYPES = ['return', 'damaged_goods', 'price_correction', 'discount', 'promo', 'rounding', 'other'];

    public function index(Request $request)
    {
        $query = SupplierTtInvoice::query()->with(['supplier', 'branch', 'goodsReceipt', 'paymentBatch'])->withCount('photos');

        if ($request->filled('search')) {
            $search = $request->string('search')->trim()->toString();
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('goodsReceipt', fn ($receipt) => $receipt->where('sp_reference_number', 'like', "%{$search}%"));
            });
        }
        if ($request->filled('supplier_id')) $query->where('supplier_id', $request->integer('supplier_id'));
        if ($request->filled('branch_id')) $query->where('branch_id', $request->integer('branch_id'));
        if ($request->filled('status')) $query->where('status', $request->string('status'));
        if ($request->filled('due_status')) {
            match ($request->string('due_status')->toString()) {
                'due_today' => $query->dueToday(), 'due_soon' => $query->dueSoon(), 'overdue' => $query->overdue(), default => null,
            };
        }
        if ($request->filled('date_from')) $query->whereDate('due_date', '>=', $request->date('date_from'));
        if ($request->filled('date_to')) $query->whereDate('due_date', '<=', $request->date('date_to'));

        $summaryQuery = clone $query;
        $summary = [
            'due_today' => (clone $summaryQuery)->dueToday()->count(),
            'due_soon' => (clone $summaryQuery)->dueSoon()->count(),
            'overdue' => (clone $summaryQuery)->overdue()->count(),
            'unpaid_total' => (clone $summaryQuery)->unpaid()->sum('net_nominal'),
            'paid_total' => (clone $summaryQuery)->where('status', 'paid')->sum('net_nominal'),
        ];

        $query->orderByRaw("CASE WHEN status = 'cancelled' THEN 3 WHEN status = 'paid' THEN 2 ELSE 1 END")
            ->orderByRaw("CASE WHEN status NOT IN ('paid', 'cancelled') THEN due_date END ASC")
            ->orderByRaw("CASE WHEN status = 'paid' THEN paid_at END DESC")
            ->latest('id');

        $supplierTtInvoices = $query->paginate(15)->withQueryString();
        $suppliers = Supplier::active()->orderBy('name')->get(['id', 'name']);
        $branches = Branches::orderBy('name')->get(['id', 'name']);
        $hasActiveFilters = collect(['search', 'supplier_id', 'branch_id', 'status', 'due_status', 'date_from', 'date_to'])
            ->contains(fn ($key) => $request->filled($key));
        $summaryContext = $request->filled('branch_id')
            ? ($branches->firstWhere('id', $request->integer('branch_id'))?->name ?? 'Cabang terpilih')
            : 'Semua cabang';

        return view('supplier-tt-invoices.index', compact('supplierTtInvoices', 'suppliers', 'branches', 'summary', 'hasActiveFilters', 'summaryContext'));
    }

    public function create()
    {
        $suppliers = Supplier::active()->orderBy('name')->get(['id', 'name']);
        $branches = Branches::orderBy('name')->get(['id', 'name']);
        return view('supplier-tt-invoices.create', compact('suppliers', 'branches'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateInvoice($request);
        $uploaded = [];
        try {
            $invoice = DB::transaction(function () use ($request, $validated, &$uploaded) {
                $net = $this->netNominal($validated);
                $this->assertReceiptCapacity($validated, $net);
                $status = $this->submittedStatus($validated);
                $invoice = SupplierTtInvoice::create($this->attributes($validated, $status, $net));
                $this->saveDeductions($invoice, $validated['deductions'] ?? []);
                $this->savePhotos($request, $invoice, $uploaded);
                return $invoice;
            });
        } catch (Throwable $e) {
            foreach ($uploaded as $path) Storage::disk('public')->delete($path);
            throw $e;
        }

        return redirect()->route('supplier-tt-invoices.show', $invoice)
            ->with('success', $invoice->status === 'draft' ? 'Faktur TT disimpan sebagai draft.' : 'Faktur TT berhasil disimpan dan diverifikasi.');
    }

    public function show(SupplierTtInvoice $supplierTtInvoice)
    {
        $supplierTtInvoice->load(['supplier', 'branch', 'goodsReceipt.supplier', 'goodsReceiptInvoice', 'photos', 'deductions', 'verifier', 'payer', 'paymentBatch']);
        return view('supplier-tt-invoices.show', compact('supplierTtInvoice'));
    }

    public function edit(SupplierTtInvoice $supplierTtInvoice)
    {
        if (!$this->canEdit($supplierTtInvoice)) return back()->with('error', 'Status faktur atau lock batch tidak mengizinkan perubahan data.');
        $supplierTtInvoice->load(['supplier', 'goodsReceipt', 'photos', 'deductions']);
        $suppliers = Supplier::active()->orderBy('name')->get(['id', 'name']);
        $branches = Branches::orderBy('name')->get(['id', 'name']);
        return view('supplier-tt-invoices.create', compact('supplierTtInvoice', 'suppliers', 'branches'));
    }

    public function update(Request $request, SupplierTtInvoice $supplierTtInvoice)
    {
        if (!$this->canEdit($supplierTtInvoice)) return back()->with('error', 'Status faktur atau lock batch tidak mengizinkan perubahan data.');
        $validated = $this->validateInvoice($request, $supplierTtInvoice);
        $uploaded = []; $deleteAfterCommit = [];
        try {
            DB::transaction(function () use ($request, $validated, $supplierTtInvoice, &$uploaded, &$deleteAfterCommit) {
                $net = $this->netNominal($validated);
                $this->assertReceiptCapacity($validated, $net, $supplierTtInvoice);
                if ($validated['submit_action'] === 'verify') {
                    $this->assertPhotosRemain($request, $supplierTtInvoice);
                }
                if ($supplierTtInvoice->status === 'scheduled_payment') $this->detachFromBatches($supplierTtInvoice);
                $supplierTtInvoice->update($this->attributes($validated, $this->submittedStatus($validated), $net));
                $supplierTtInvoice->deductions()->delete();
                $this->saveDeductions($supplierTtInvoice, $validated['deductions'] ?? []);

                $ids = collect($validated['deleted_photo_ids'] ?? [])->map(fn ($id) => (int) $id);
                if ($ids->isNotEmpty()) {
                    $photos = $supplierTtInvoice->photos()->whereIn('id', $ids)->get();
                    $deleteAfterCommit = $photos->pluck('path')->filter()->all();
                    $supplierTtInvoice->photos()->whereIn('id', $ids)->delete();
                }
                $this->savePhotos($request, $supplierTtInvoice, $uploaded);
                $this->reorderPhotos($supplierTtInvoice);
            });
        } catch (Throwable $e) {
            foreach ($uploaded as $path) Storage::disk('public')->delete($path);
            throw $e;
        }
        foreach ($deleteAfterCommit as $path) Storage::disk('public')->delete($path);

        return redirect()->route('supplier-tt-invoices.show', $supplierTtInvoice)->with('success', 'Faktur TT berhasil diperbarui.');
    }

    public function prepareEdit(SupplierTtInvoice $supplierTtInvoice)
    {
        if ($supplierTtInvoice->status !== 'scheduled_payment') {
            return redirect()->route('supplier-tt-invoices.edit', $supplierTtInvoice);
        }

        DB::transaction(function () use ($supplierTtInvoice) {
            $invoice = SupplierTtInvoice::whereKey($supplierTtInvoice->id)->lockForUpdate()->firstOrFail();
            $hasLockedBatch = $invoice->paymentBatch()->where('status', 'locked')->exists();

            if ($hasLockedBatch) {
                throw ValidationException::withMessages([
                    'invoice' => 'Faktur berada dalam batch terkunci. Superadmin harus unlock batch terlebih dahulu.',
                ]);
            }

            $this->detachFromBatches($invoice);
            $invoice->update(['status' => 'verified']);
        });

        return redirect()->route('supplier-tt-invoices.edit', $supplierTtInvoice)
            ->with('success', 'Faktur dikeluarkan dari batch dan dikembalikan ke status Terverifikasi.');
    }

    public function destroy(SupplierTtInvoice $supplierTtInvoice)
    {
        if (!auth()->user()?->hasRole('superadmin')) abort(403, 'Hanya superadmin yang bisa menghapus Faktur TT.');
        if (!$supplierTtInvoice->isDeletable()) return back()->with('error', 'Faktur yang sudah dibayar tidak bisa dihapus.');
        if ($supplierTtInvoice->paymentBatch()->where('status', 'locked')->exists()) return back()->with('error', 'Unlock batch terlebih dahulu sebelum menghapus faktur.');
        $paths = $supplierTtInvoice->photos()->pluck('path')->filter()->all();
        DB::transaction(function () use ($supplierTtInvoice) {
            $this->detachFromBatches($supplierTtInvoice);
            $supplierTtInvoice->delete();
        });
        foreach ($paths as $path) Storage::disk('public')->delete($path);
        return redirect()->route('supplier-tt-invoices.index')->with('success', 'Faktur TT berhasil dihapus.');
    }

    public function verify(SupplierTtInvoice $supplierTtInvoice)
    {
        if ($supplierTtInvoice->status !== 'draft') return back()->with('error', 'Hanya faktur Draft yang dapat diverifikasi.');
        $errors = $this->verificationReadinessErrors($supplierTtInvoice);
        if ($errors !== []) return back()->with('error', implode(' ', $errors));
        $supplierTtInvoice->update(['status' => 'verified', 'verified_at' => now(), 'verified_by' => auth()->id()]);
        return back()->with('success', 'Faktur berhasil diverifikasi.');
    }

    public function moveToDraft(SupplierTtInvoice $supplierTtInvoice)
    {
        if ($supplierTtInvoice->status !== 'verified') return back()->with('error', 'Hanya faktur Terverifikasi yang dapat dikembalikan ke Draft.');
        $supplierTtInvoice->update(['status' => 'draft', 'verified_at' => null, 'verified_by' => null]);
        return back()->with('success', 'Faktur dikembalikan ke Draft.');
    }

    public function cancel(SupplierTtInvoice $supplierTtInvoice)
    {
        if (in_array($supplierTtInvoice->status, ['paid', 'cancelled'])) return back()->with('error', 'Faktur tidak dapat dibatalkan.');
        $locked = $supplierTtInvoice->paymentBatch()->where('status', 'locked')->exists();
        if ($locked) return back()->with('error', 'Faktur berada dalam batch terkunci. Superadmin harus unlock batch terlebih dahulu.');
        DB::transaction(function () use ($supplierTtInvoice) {
            $this->detachFromBatches($supplierTtInvoice);
            $supplierTtInvoice->update(['status' => 'cancelled']);
        });
        return back()->with('success', 'Faktur TT dibatalkan.');
    }

    public function availableGoodsReceipts(Request $request)
    {
        $data = $request->validate(['supplier_id' => ['required', 'exists:suppliers,id'], 'branch_id' => ['required', 'exists:branches,id'], 'exclude_tt_invoice_id' => ['nullable', 'exists:supplier_tt_invoices,id']]);
        $exclude = $data['exclude_tt_invoice_id'] ?? null;
        $receipts = GoodsReceipt::query()->where('supplier_id', $data['supplier_id'])->where('branch_id', $data['branch_id'])->where('status', 'approved')->with('supplier:id,name')
            ->withCount(['supplierTtInvoices as tt_count' => function ($q) use ($exclude) { $q->where('status', '!=', 'cancelled'); if ($exclude) $q->whereKeyNot($exclude); }])
            ->withSum(['supplierTtInvoices as active_tt_total' => function ($q) use ($exclude) { $q->active(); if ($exclude) $q->whereKeyNot($exclude); }], 'net_nominal')
            ->latest('received_date')->get()->map(function ($receipt) {
                $used = (float) ($receipt->active_tt_total ?? 0); $total = (float) $receipt->total_nominal; $remaining = max(0, $total - $used);
                return ['id' => $receipt->id, 'received_date' => $receipt->received_date?->format('Y-m-d'), 'supplier_name' => $receipt->supplier?->name,
                    'sp_reference_number' => $receipt->sp_reference_number, 'status' => $receipt->status, 'status_label' => $receipt->status_label,
                    'total_nominal' => $total, 'active_tt_total' => $used, 'remaining_nominal' => $remaining, 'tt_count' => (int) $receipt->tt_count,
                    'tt_count_label' => (int) $receipt->tt_count === 0 ? 'Belum Ada Faktur TT' : ((int) $receipt->tt_count === 1 ? '1 Faktur TT' : 'Multi Faktur TT'),
                    'is_available' => $remaining > 0];
            })->filter(fn ($receipt) => $receipt['is_available'])->values();
        return response()->json(['data' => $receipts]);
    }

    private function validateInvoice(Request $request, ?SupplierTtInvoice $invoice = null): array
    {
        $isVerifying = $request->input('submit_action') === 'verify';
        $required = $isVerifying ? 'required' : 'nullable';
        $data = $request->validate([
            'supplier_id' => [$required, 'integer', 'exists:suppliers,id'], 'branch_id' => [$required, 'integer', 'exists:branches,id'], 'goods_receipt_id' => ['nullable', 'integer', 'exists:goods_receipts,id'],
            'invoice_number' => [$required, 'string', 'max:255', Rule::unique('supplier_tt_invoices', 'invoice_number')->where(fn ($q) => $q->where('supplier_id', $request->input('supplier_id')))->ignore($invoice?->id)],
            'invoice_date' => [$required, 'date'], 'received_date' => [$required, 'date'], 'due_date' => [$required, 'date', 'after_or_equal:invoice_date'],
            'gross_nominal' => [$required, 'numeric', $isVerifying ? 'gt:0' : 'min:0'], 'notes' => ['nullable', 'string'],
            'submit_action' => ['required', Rule::in(['draft', 'verify'])], 'verification_note' => ['nullable', 'string'],
            'photos' => [($invoice || !$isVerifying) ? 'nullable' : 'required', 'array', 'max:20'], 'photos.*' => ['image', 'max:4096'],
            'deleted_photo_ids' => ['nullable', 'array'], 'deleted_photo_ids.*' => ['integer', 'exists:supplier_tt_invoice_photos,id'],
            'deductions' => ['nullable', 'array', 'max:50'], 'deductions.*.type' => ['required', Rule::in(self::DEDUCTION_TYPES)],
            'deductions.*.amount' => ['required', 'numeric', 'gt:0'], 'deductions.*.reference_number' => ['nullable', 'string', 'max:255'],
            'deductions.*.reference_date' => ['nullable', 'date'], 'deductions.*.description' => ['nullable', 'string'],
        ]);
        if (!empty($data['goods_receipt_id'])) {
            $receipt = GoodsReceipt::find($data['goods_receipt_id']);
            if (!$receipt || empty($data['supplier_id']) || (int) $receipt->supplier_id !== (int) $data['supplier_id']) throw ValidationException::withMessages(['goods_receipt_id' => 'Goods Receipt harus berasal dari supplier yang dipilih.']);
            if ($receipt->status !== 'approved') throw ValidationException::withMessages(['goods_receipt_id' => 'Goods Receipt harus sudah disetujui.']);
            if (empty($data['branch_id']) || (int) $receipt->branch_id !== (int) $data['branch_id']) throw ValidationException::withMessages(['goods_receipt_id' => 'Goods Receipt harus berasal dari cabang yang sama dengan Faktur TT.']);
        }
        return $data;
    }

    private function netNominal(array $data): float
    {
        $gross = (float) ($data['gross_nominal'] ?? 0); $deductions = collect($data['deductions'] ?? [])->sum(fn ($row) => (float) $row['amount']);
        if ($deductions > $gross) throw ValidationException::withMessages(['deductions' => 'Total potongan tidak boleh melebihi nominal bruto.']);
        return $gross - $deductions;
    }

    private function assertReceiptCapacity(array $data, float $net, ?SupplierTtInvoice $current = null): void
    {
        if (empty($data['goods_receipt_id'])) return;
        $receipt = GoodsReceipt::query()->lockForUpdate()->findOrFail($data['goods_receipt_id']);
        $used = $receipt->supplierTtInvoices()->active()->when($current, fn ($q) => $q->whereKeyNot($current->id))->sum('net_nominal');
        $remaining = max(0, (float) $receipt->total_nominal - (float) $used);
        if ($net > $remaining) throw ValidationException::withMessages(['gross_nominal' => 'Net nominal melebihi sisa Goods Receipt Rp '.number_format($remaining, 0, ',', '.')]);
    }

    private function submittedStatus(array $data): string { return $data['submit_action'] === 'verify' ? 'verified' : 'draft'; }

    private function attributes(array $data, string $status, float $net): array
    {
        $verified = $status !== 'draft';
        $gross = (float) ($data['gross_nominal'] ?? 0);
        return ['supplier_id' => $data['supplier_id'] ?? null, 'branch_id' => $data['branch_id'] ?? null, 'goods_receipt_id' => $data['goods_receipt_id'] ?? null, 'goods_receipt_invoice_id' => null,
            'invoice_number' => $data['invoice_number'] ?? null, 'invoice_date' => $data['invoice_date'] ?? null, 'received_date' => $data['received_date'] ?? null, 'due_date' => $data['due_date'] ?? null,
            'gross_nominal' => $gross, 'deduction_total' => $gross - $net, 'net_nominal' => $net, 'status' => $status,
            'verification_note' => $data['verification_note'] ?? null, 'notes' => $data['notes'] ?? null,
            'verified_at' => $verified ? now() : null, 'verified_by' => $verified ? auth()->id() : null, 'paid_at' => null, 'paid_by' => null];
    }

    private function saveDeductions(SupplierTtInvoice $invoice, array $rows): void
    {
        foreach ($rows as $row) $invoice->deductions()->create(['type' => $row['type'], 'amount' => $row['amount'], 'reference_number' => $row['reference_number'] ?? null, 'reference_date' => $row['reference_date'] ?? null, 'description' => $row['description'] ?? null]);
    }

    private function verificationReadinessErrors(SupplierTtInvoice $invoice): array
    {
        $errors = [];
        foreach (['supplier_id', 'branch_id', 'invoice_number', 'invoice_date', 'received_date', 'due_date'] as $field) {
            if (blank($invoice->{$field})) {
                $errors[] = 'Lengkapi seluruh informasi faktur sebelum verifikasi.';
                break;
            }
        }
        if ((float) $invoice->gross_nominal <= 0) $errors[] = 'Nominal bruto harus lebih dari 0 sebelum verifikasi.';
        if ($invoice->photos()->doesntExist()) $errors[] = 'Minimal satu foto Faktur TT wajib tersedia sebelum verifikasi.';
        if ((float) $invoice->deduction_total > (float) $invoice->gross_nominal) $errors[] = 'Total potongan tidak boleh melebihi nominal bruto.';
        if ($invoice->due_date && $invoice->invoice_date && $invoice->due_date->lt($invoice->invoice_date)) $errors[] = 'Tanggal jatuh tempo tidak boleh sebelum tanggal faktur.';
        return array_values(array_unique($errors));
    }

    private function savePhotos(Request $request, SupplierTtInvoice $invoice, array &$uploaded): void
    {
        $next = (int) $invoice->photos()->max('page_number') + 1;
        foreach ($request->file('photos', []) as $index => $photo) {
            $path = $photo->store('supplier-tt-invoices/'.now()->format('Y/m'), 'public'); $uploaded[] = $path;
            $invoice->photos()->create(['path' => $path, 'page_number' => $next + $index, 'original_name' => $photo->getClientOriginalName(), 'size' => $photo->getSize(), 'mime_type' => $photo->getMimeType()]);
        }
    }

    private function assertPhotosRemain(Request $request, SupplierTtInvoice $invoice): void
    {
        $ids = collect($request->input('deleted_photo_ids', []))->map(fn ($id) => (int) $id);
        if ($invoice->photos()->whereNotIn('id', $ids)->count() + count($request->file('photos', [])) < 1) throw ValidationException::withMessages(['photos' => 'Minimal harus ada satu foto Faktur TT.']);
    }

    private function reorderPhotos(SupplierTtInvoice $invoice): void
    {
        $invoice->photos()->orderBy('page_number')->get()->values()->each(fn ($photo, $index) => $photo->update(['page_number' => $index + 1]));
    }

    private function canEdit(SupplierTtInvoice $invoice): bool
    {
        return in_array($invoice->status, ['draft', 'verified']);
    }

    private function detachFromBatches(SupplierTtInvoice $invoice): void
    {
        $batches = $invoice->paymentBatch()->lockForUpdate()->get();
        foreach ($batches as $batch) {
            $batch->invoices()->detach($invoice->id);
            $batch->update(['total_nominal' => $batch->invoices()->sum('supplier_tt_payment_batch_items.nominal_snapshot')]);
        }
    }
}
