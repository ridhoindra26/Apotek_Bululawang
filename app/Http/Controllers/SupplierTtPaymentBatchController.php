<?php

namespace App\Http\Controllers;

use App\Models\Branches;
use App\Models\SupplierTtInvoice;
use App\Models\SupplierTtPaymentBatch;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierTtPaymentBatchController extends Controller
{
    public function index(Request $request)
    {
        $query = SupplierTtPaymentBatch::with('branch')->withCount('invoices');
        if ($request->filled('search')) $query->where('batch_number', 'like', '%'.$request->string('search')->trim().'%');
        if ($request->filled('status')) {
            $request->string('status')->toString() === 'unpaid'
                ? $query->whereIn('status', ['draft', 'locked'])
                : $query->where('status', $request->string('status'));
        }
        if ($request->filled('branch_id')) $query->where('branch_id', $request->integer('branch_id'));

        $summaryQuery = clone $query;
        $summary = [
            'unpaid' => (clone $summaryQuery)->whereIn('status', ['draft', 'locked'])->count(),
            'paid' => (clone $summaryQuery)->where('status', 'paid')->count(),
            'unpaid_total' => (clone $summaryQuery)->whereIn('status', ['draft', 'locked'])->sum('total_nominal'),
        ];

        $batches = $query->latest('payment_date')->latest()->paginate(15)->withQueryString();
        $branches = Branches::orderBy('name')->get(['id', 'name']);
        $hasActiveFilters = collect(['search', 'status', 'branch_id'])->contains(fn ($key) => $request->filled($key));
        $summaryContext = $request->filled('branch_id')
            ? ($branches->firstWhere('id', $request->integer('branch_id'))?->name ?? 'Cabang terpilih')
            : 'Semua cabang';

        return view('supplier-tt-payment-batches.index', compact('batches', 'branches', 'summary', 'hasActiveFilters', 'summaryContext'));
    }

    public function create()
    {
        $branches = Branches::orderBy('name')->get(['id', 'name']);
        $invoices = SupplierTtInvoice::with(['supplier:id,name', 'branch:id,name'])
            ->where('status', 'verified')
            ->whereNotNull('branch_id')
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();
        return view('supplier-tt-payment-batches.create', compact('branches', 'invoices'));
    }

    public function store(Request $request)
    {
        $data = $request->validate(['branch_id' => ['required', 'exists:branches,id'], 'payment_date' => ['required', 'date'], 'notes' => ['nullable', 'string'], 'invoice_ids' => ['required', 'array', 'min:1'], 'invoice_ids.*' => ['integer', 'distinct', 'exists:supplier_tt_invoices,id']]);

        $batch = DB::transaction(function () use ($data) {
            $invoices = SupplierTtInvoice::whereKey($data['invoice_ids'])->lockForUpdate()->get();
            if ($invoices->count() !== count($data['invoice_ids']) || $invoices->contains(fn ($invoice) => $invoice->status !== 'verified')) {
                throw ValidationException::withMessages(['invoice_ids' => 'Semua faktur harus masih berstatus Terverifikasi.']);
            }
            if ($invoices->contains(fn ($invoice) => !$invoice->branch_id || (int) $invoice->branch_id !== (int) $data['branch_id'])) {
                throw ValidationException::withMessages(['invoice_ids' => 'Semua faktur dalam batch harus berasal dari cabang yang sama.']);
            }

            $batch = SupplierTtPaymentBatch::create(['batch_number' => 'PENDING-'.uniqid(), 'branch_id' => $data['branch_id'], 'payment_date' => $data['payment_date'], 'total_nominal' => $invoices->sum('net_nominal'), 'status' => 'draft', 'notes' => $data['notes'] ?? null]);
            $batch->update(['batch_number' => 'TTB-'.$batch->payment_date->format('Ymd').'-'.str_pad((string) $batch->id, 6, '0', STR_PAD_LEFT)]);
            $batch->invoices()->attach($invoices->mapWithKeys(fn ($invoice) => [$invoice->id => ['nominal_snapshot' => $invoice->net_nominal]])->all());
            SupplierTtInvoice::whereKey($invoices->pluck('id'))->update(['status' => 'scheduled_payment']);
            return $batch;
        });

        return redirect()->route('supplier-tt-payment-batches.show', $batch)->with('success', 'Batch pembayaran berhasil dibuat.');
    }

    public function show(SupplierTtPaymentBatch $supplierTtPaymentBatch)
    {
        $supplierTtPaymentBatch->load(['branch', 'invoices.supplier', 'locker', 'payer']);
        $groups = $supplierTtPaymentBatch->invoices->sortBy(fn ($invoice) => $invoice->supplier?->name)->groupBy('supplier_id');
        return view('supplier-tt-payment-batches.show', ['batch' => $supplierTtPaymentBatch, 'groups' => $groups]);
    }

    public function edit(SupplierTtPaymentBatch $supplierTtPaymentBatch)
    {
        abort_unless($supplierTtPaymentBatch->status === 'draft', 403, 'Unlock batch terlebih dahulu sebelum mengubahnya.');
        $supplierTtPaymentBatch->load('invoices');
        $branches = Branches::orderBy('name')->get(['id', 'name']);
        $invoices = SupplierTtInvoice::with(['supplier:id,name', 'branch:id,name'])
            ->where(fn ($q) => $q->where('status', 'verified')->orWhereHas('paymentBatch', fn ($batch) => $batch->whereKey($supplierTtPaymentBatch->id)))
            ->whereNotNull('branch_id')
            ->orderByRaw('CASE WHEN due_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('due_date')
            ->orderBy('id')
            ->get();
        return view('supplier-tt-payment-batches.create', ['batch' => $supplierTtPaymentBatch, 'branches' => $branches, 'invoices' => $invoices]);
    }

    public function update(Request $request, SupplierTtPaymentBatch $supplierTtPaymentBatch)
    {
        $data = $request->validate(['branch_id' => ['required', 'exists:branches,id'], 'payment_date' => ['required', 'date'], 'notes' => ['nullable', 'string'], 'invoice_ids' => ['required', 'array', 'min:1'], 'invoice_ids.*' => ['integer', 'distinct', 'exists:supplier_tt_invoices,id']]);
        DB::transaction(function () use ($supplierTtPaymentBatch, $data) {
            $batch = SupplierTtPaymentBatch::whereKey($supplierTtPaymentBatch->id)->lockForUpdate()->firstOrFail();
            if ($batch->status !== 'draft') throw ValidationException::withMessages(['batch' => 'Batch sudah dikunci dan tidak dapat diubah.']);
            $currentIds = $batch->invoices()->pluck('supplier_tt_invoices.id');
            $invoices = SupplierTtInvoice::whereKey(array_unique(array_merge($currentIds->all(), $data['invoice_ids'])))->lockForUpdate()->get();
            $selected = $invoices->whereIn('id', $data['invoice_ids']);
            if ($selected->count() !== count($data['invoice_ids']) || $selected->contains(fn ($invoice) => !in_array($invoice->status, ['verified', 'scheduled_payment']) || (int) $invoice->branch_id !== (int) $data['branch_id'])) throw ValidationException::withMessages(['invoice_ids' => 'Faktur pilihan harus tersedia, terverifikasi, dan berasal dari cabang yang sama.']);
            SupplierTtInvoice::whereKey($currentIds->diff($data['invoice_ids']))->update(['status' => 'verified']);
            SupplierTtInvoice::whereKey($selected->pluck('id'))->update(['status' => 'scheduled_payment']);
            $batch->invoices()->sync($selected->mapWithKeys(fn ($invoice) => [$invoice->id => ['nominal_snapshot' => $invoice->net_nominal]])->all());
            $batch->update(['branch_id' => $data['branch_id'], 'payment_date' => $data['payment_date'], 'total_nominal' => $selected->sum('net_nominal'), 'notes' => $data['notes'] ?? null]);
        });
        return redirect()->route('supplier-tt-payment-batches.show', $supplierTtPaymentBatch)->with('success', 'Batch berhasil diperbarui.');
    }

    public function destroy(SupplierTtPaymentBatch $supplierTtPaymentBatch)
    {
        abort_unless(auth()->user()?->hasRole('superadmin'), 403, 'Hanya superadmin yang dapat menghapus batch pembayaran.');
        DB::transaction(function () use ($supplierTtPaymentBatch) {
            $batch = SupplierTtPaymentBatch::whereKey($supplierTtPaymentBatch->id)->lockForUpdate()->firstOrFail();
            if ($batch->status !== 'draft') throw ValidationException::withMessages(['batch' => 'Batch terkunci atau dibayar tidak dapat dihapus.']);
            SupplierTtInvoice::whereKey($batch->invoices()->pluck('supplier_tt_invoices.id'))->update(['status' => 'verified']);
            $batch->delete();
        });
        return redirect()->route('supplier-tt-payment-batches.index')->with('success', 'Batch Draft berhasil dihapus.');
    }

    public function lockAndPdf(SupplierTtPaymentBatch $supplierTtPaymentBatch)
    {
        DB::transaction(function () use ($supplierTtPaymentBatch) {
            $batch = SupplierTtPaymentBatch::whereKey($supplierTtPaymentBatch->id)->lockForUpdate()->firstOrFail();
            if ($batch->status !== 'draft') throw ValidationException::withMessages(['batch' => 'Hanya batch Draft yang dapat dikunci.']);
            $invoices = SupplierTtInvoice::whereHas('paymentBatch', fn ($q) => $q->whereKey($batch->id))->lockForUpdate()->get();
            if ($invoices->isEmpty() || $invoices->contains(fn ($invoice) => $invoice->status !== 'scheduled_payment' || (int) $invoice->branch_id !== (int) $batch->branch_id)) {
                throw ValidationException::withMessages(['batch' => 'Isi batch berubah atau tidak valid. Muat ulang halaman dan periksa faktur.']);
            }
            $batch->update(['status' => 'locked', 'total_nominal' => $invoices->sum('net_nominal'), 'locked_at' => now(), 'locked_by' => auth()->id()]);
        });
        return $this->pdf($supplierTtPaymentBatch->fresh());
    }

    public function pdf(SupplierTtPaymentBatch $supplierTtPaymentBatch)
    {
        abort_unless(in_array($supplierTtPaymentBatch->status, ['locked', 'paid']), 403, 'Batch harus dikunci sebelum PDF dapat dibuat.');
        $supplierTtPaymentBatch->load(['branch', 'invoices.supplier', 'locker', 'payer']);
        $groups = $supplierTtPaymentBatch->invoices->sortBy(fn ($invoice) => $invoice->supplier?->name)->groupBy('supplier_id');
        return Pdf::loadView('supplier-tt-payment-batches.pdf', ['batch' => $supplierTtPaymentBatch, 'groups' => $groups])->setPaper('a4')->download($supplierTtPaymentBatch->batch_number.'.pdf');
    }

    public function unlock(SupplierTtPaymentBatch $supplierTtPaymentBatch)
    {
        abort_unless(auth()->user()?->hasRole('superadmin'), 403);
        DB::transaction(function () use ($supplierTtPaymentBatch) {
            $batch = SupplierTtPaymentBatch::whereKey($supplierTtPaymentBatch->id)->lockForUpdate()->firstOrFail();
            if ($batch->status !== 'locked') throw ValidationException::withMessages(['batch' => 'Hanya batch terkunci yang dapat dibuka kembali.']);
            $batch->update(['status' => 'draft', 'locked_at' => null, 'locked_by' => null]);
        });
        return back()->with('success', 'Lock batch berhasil dibuka oleh superadmin.');
    }

    public function markPaid(SupplierTtPaymentBatch $supplierTtPaymentBatch)
    {
        DB::transaction(function () use ($supplierTtPaymentBatch) {
            $batch = SupplierTtPaymentBatch::whereKey($supplierTtPaymentBatch->id)->lockForUpdate()->firstOrFail();
            if ($batch->status !== 'locked') throw ValidationException::withMessages(['batch' => 'Batch harus dikunci sebelum ditandai dibayar.']);
            $ids = $batch->invoices()->pluck('supplier_tt_invoices.id');
            if ($ids->isEmpty()) throw ValidationException::withMessages(['batch' => 'Batch kosong tidak dapat ditandai dibayar.']);
            $invoices = SupplierTtInvoice::whereKey($ids)->lockForUpdate()->get();
            if ($invoices->contains(fn ($invoice) => $invoice->status !== 'scheduled_payment')) throw ValidationException::withMessages(['batch' => 'Ada status faktur yang tidak valid dalam batch.']);
            SupplierTtInvoice::whereKey($ids)->update(['status' => 'paid', 'paid_at' => now(), 'paid_by' => auth()->id()]);
            $batch->update(['status' => 'paid', 'paid_at' => now(), 'paid_by' => auth()->id()]);
        });
        return back()->with('success', 'Batch dan seluruh faktur berhasil ditandai dibayar.');
    }
}
