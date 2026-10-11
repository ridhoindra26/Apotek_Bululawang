<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptInvoice;
use App\Models\Supplier;
use App\Models\Employees;
use App\Models\Branches;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Auth;

class GoodsReceiptController extends Controller
{
    public function index(Request $request)
    {
        // allow access for guests or users with role admin or superadmin
        if (!(Auth::guest() || (Auth::check() && in_array(Auth::user()->role ?? '', ['admin', 'superadmin'])))) {
            abort(403);
        }
        $query = GoodsReceipt::query()
            ->with(['supplier', 'receiver', 'branch'])
            ->withCount('invoices');

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('sp_reference_number', 'like', "%{$search}%")
                    ->orWhere('sender_name', 'like', "%{$search}%")
                    ->orWhereHas('supplier', function ($supplierQuery) use ($search) {
                        $supplierQuery->where('name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('invoices', function ($invoiceQuery) use ($search) {
                        $invoiceQuery->where('invoice_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('evaluation_status')) {
            $query->where('evaluation_status', $request->evaluation_status);
        }

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('received_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('received_date', '<=', $request->date_to);
        }

        $goodsReceipts = $query
            ->latest('received_date')
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $suppliers = Supplier::active()
            ->orderBy('name')
            ->get(['id', 'name']);

        $branches = Branches::query()->orderBy('name')->get(['id', 'name']);

        return view('goods-receipts.index', compact('goodsReceipts', 'suppliers', 'branches'));
    }

    public function create()
    {
        if (!(Auth::guest() || (Auth::check() && in_array(Auth::user()->role ?? '', ['admin', 'superadmin'])))) {
            abort(403);
        }
        $suppliers = Supplier::active()
            ->orderBy('name')
            ->get(['id', 'name']);

        $employees = Employees::query()
            ->where('id_role', '<>', 13)
            ->where('id_branch', '=', 2)
            ->orderBy('name')
            ->get(['id', 'name']);

        $branches = Branches::query()->orderBy('name')->get(['id', 'name']);

        return view('goods-receipts.create', compact('suppliers', 'employees', 'branches'));
    }

    public function store(Request $request)
    {
        if (!(Auth::guest() || (Auth::check() && in_array(Auth::user()->role ?? '', ['admin', 'superadmin'])))) {
            abort(403);
        }
        $validated = $this->validateGoodsReceipt($request);

        try {
            DB::transaction(function () use ($request, $validated) {
                $invoices = $validated['invoices'] ?? [];
                $totalNominal = collect($invoices)->sum(function ($invoice) {
                    return (float) ($invoice['nominal'] ?? 0);
                });

                $receipt = GoodsReceipt::create([
                    'received_date' => $validated['received_date'] ?? null,
                    'supplier_id' => $validated['supplier_id'] ?? null,
                    'branch_id' => $validated['branch_id'] ?? null,
                    'sp_reference_number' => $validated['sp_reference_number'] ?? null,
                    'received_by' => $validated['received_by'] ?? null,
                    'sender_name' => $validated['sender_name'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'total_nominal' => $totalNominal,
                    'evaluation_status' => $this->resolveReceiptEvaluationStatus($invoices),
                    'evaluation_note' => $validated['evaluation_note'] ?? null,
                    'status' => 'draft',
                ]);

                $this->storeInvoices($request, $receipt, $invoices);

                $this->storeSignatures($request, $receipt);

                if ($validated['submit_action'] === 'submit_approval') {
                    $readinessErrors = $this->approvalReadinessErrors($receipt->fresh());
                    if ($readinessErrors !== []) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'approval' => implode(' ', $readinessErrors),
                        ]);
                    }
                    $receipt->update(['status' => 'waiting_approval']);
                }
            });

            return redirect()
                ->route('goods-receipts.index')
                ->with('success', 'Penerimaan barang berhasil disimpan.');
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Penerimaan barang gagal disimpan. ' . $e->getMessage());
        }
    }

    public function show(GoodsReceipt $goodsReceipt)
    {
        $this->authorizeGoodsReceiptAccess($goodsReceipt);
        $goodsReceipt->load([
            'supplier',
            'receiver',
            'checker',
            'invoices.pages',
            'signatures',
        ]);

        return view('goods-receipts.show', compact('goodsReceipt'));
    }

    public function edit(GoodsReceipt $goodsReceipt)
    {
        $this->authorizeGoodsReceiptAccess($goodsReceipt);
        if ($goodsReceipt->status !== 'draft') {
            return back()->with('error', 'Hanya penerimaan barang berstatus draft yang bisa diedit.');
        }

        $goodsReceipt->load([
            'supplier',
            'receiver',
            'invoices.pages',
            'signatures',
        ]);

        $suppliers = Supplier::active()
            ->orderBy('name')
            ->get(['id', 'name']);

        $employees = Employees::query()
            ->where('id_role', '<>', 13)
            ->orderBy('name')
            ->get(['id', 'name']);

        $branches = Branches::query()->orderBy('name')->get(['id', 'name']);

        return view('goods-receipts.edit', compact(
            'goodsReceipt',
            'suppliers',
            'employees',
            'branches'
        ));
    }

    public function update(Request $request, GoodsReceipt $goodsReceipt)
    {
        $this->authorizeGoodsReceiptAccess($goodsReceipt);
        if ($goodsReceipt->status !== 'draft') {
            return back()->with('error', 'Hanya penerimaan barang berstatus draft yang bisa diedit.');
        }

        $validated = $this->validateGoodsReceipt($request, $goodsReceipt);

        try {
            DB::transaction(function () use ($request, $goodsReceipt, $validated) {
                $deletedPhotoIds = collect($request->input('deleted_photo_ids', []))
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                $deletedInvoiceIds = collect($request->input('deleted_invoice_ids', []))
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->values()
                    ->all();

                $this->deleteSelectedInvoicePhotos($goodsReceipt, $deletedPhotoIds);
                $this->deleteSelectedInvoices($goodsReceipt, $deletedInvoiceIds);

                $invoices = $validated['invoices'] ?? [];
                $totalNominal = collect($invoices)->sum(function ($invoice) {
                    return (float) ($invoice['nominal'] ?? 0);
                });

                $goodsReceipt->update([
                    'received_date' => $validated['received_date'] ?? null,
                    'supplier_id' => $validated['supplier_id'] ?? null,
                    'branch_id' => $validated['branch_id'] ?? null,
                    'sp_reference_number' => $validated['sp_reference_number'] ?? null,
                    'received_by' => $validated['received_by'] ?? null,
                    'sender_name' => $validated['sender_name'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                    'total_nominal' => $totalNominal,
                    'evaluation_status' => $this->resolveReceiptEvaluationStatus($invoices),
                    'evaluation_note' => $validated['evaluation_note'] ?? null,
                    'status' => 'draft',
                ]);

                $this->upsertInvoices($request, $goodsReceipt, $invoices);

                $this->replaceSignaturesIfProvided($request, $goodsReceipt);

                if ($validated['submit_action'] === 'submit_approval') {
                    $readinessErrors = $this->approvalReadinessErrors($goodsReceipt->fresh());
                    if ($readinessErrors !== []) {
                        throw \Illuminate\Validation\ValidationException::withMessages([
                            'approval' => implode(' ', $readinessErrors),
                        ]);
                    }
                    $goodsReceipt->update(['status' => 'waiting_approval']);
                }
            });

            return redirect()
                ->route('goods-receipts.show', $goodsReceipt)
                ->with('success', 'Penerimaan barang berhasil diperbaruiiii.');
        } catch (\Throwable $e) {
            report($e);

            return back()
                ->withInput()
                ->with('error', 'Penerimaan barang gagal diperbarui. ' . $e->getMessage());
        }
    }

    public function destroy(GoodsReceipt $goodsReceipt)
    {
        $this->authorizeSuperadmin();

        DB::transaction(function () use ($goodsReceipt) {
            $this->deleteExistingInvoicesAndPhotos($goodsReceipt);
            $this->deleteExistingSignatures($goodsReceipt);

            $goodsReceipt->delete();
        });

        return redirect()
            ->route('goods-receipts.index')
            ->with('success', 'Penerimaan barang berhasil dihapus.');
    }

    public function submitApproval(GoodsReceipt $goodsReceipt)
    {
        $this->authorizeGoodsReceiptAccess($goodsReceipt);
        $error = DB::transaction(function () use ($goodsReceipt) {
            $receipt = GoodsReceipt::query()->lockForUpdate()->findOrFail($goodsReceipt->id);
            if ($receipt->status !== 'draft') {
                return 'Hanya data draft yang bisa diajukan untuk approval.';
            }

            $readinessErrors = $this->approvalReadinessErrors($receipt);
            if ($readinessErrors !== []) {
                return implode(' ', $readinessErrors);
            }

            $receipt->update(['status' => 'waiting_approval']);
            return null;
        });

        if ($error) return back()->with('error', $error);

        return back()->with('success', 'Penerimaan barang berhasil diajukan approval.');
    }

    public function approve(GoodsReceipt $goodsReceipt)
    {
        $this->authorizeReviewer();

        $changed = DB::transaction(function () use ($goodsReceipt) {
            $receipt = GoodsReceipt::query()->lockForUpdate()->findOrFail($goodsReceipt->id);
            if ($receipt->status !== 'waiting_approval') return false;
            $receipt->update(['status' => 'approved', 'checked_at' => now(), 'checked_by' => auth()->id()]);
            return true;
        });

        if (!$changed) return back()->with('error', 'Hanya data yang menunggu approval yang bisa di-approve.');

        return back()->with('success', 'Penerimaan barang berhasil di-approve.');
    }

    public function reject(Request $request, GoodsReceipt $goodsReceipt)
    {
        $this->authorizeReviewer();

        $validated = $request->validate([
            'evaluation_note' => ['required', 'string', 'max:5000'],
        ], [
            'evaluation_note.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $changed = DB::transaction(function () use ($goodsReceipt, $validated) {
            $receipt = GoodsReceipt::query()->lockForUpdate()->findOrFail($goodsReceipt->id);
            if ($receipt->status !== 'waiting_approval') return false;
            $receipt->update([
                'status' => 'rejected',
                'evaluation_note' => trim($validated['evaluation_note']),
                'checked_at' => now(),
                'checked_by' => auth()->id(),
            ]);
            return true;
        });

        if (!$changed) return back()->with('error', 'Hanya data yang menunggu approval yang bisa ditolak.');

        return back()->with('success', 'Penerimaan barang berhasil ditolak.');
    }

    public function draft(GoodsReceipt $goodsReceipt)
    {
        $this->authorizeGoodsReceiptAccess($goodsReceipt);

        $changed = DB::transaction(function () use ($goodsReceipt) {
            $receipt = GoodsReceipt::query()->lockForUpdate()->findOrFail($goodsReceipt->id);
            if ($receipt->status !== 'waiting_approval') return false;
            $receipt->update(['status' => 'draft']);
            return true;
        });

        if (!$changed) return back()->with('error', 'Hanya data yang menunggu approval yang bisa diubah menjadi draft.');

        return back()->with('success', 'Penerimaan barang berhasil diubah menjadi draft.');
    }

    public function cancel(GoodsReceipt $goodsReceipt)
    {
        $this->authorizeReviewer();

        $changed = DB::transaction(function () use ($goodsReceipt) {
            $receipt = GoodsReceipt::query()->lockForUpdate()->findOrFail($goodsReceipt->id);
            if (!in_array($receipt->status, ['waiting_approval', 'approved', 'rejected'], true)) return false;
            $receipt->update(['status' => 'cancelled', 'checked_at' => now(), 'checked_by' => auth()->id()]);
            return true;
        });

        if (!$changed) return back()->with('error', 'Hanya data menunggu approval, approved, atau rejected yang bisa dibatalkan.');

        return back()->with('success', 'Penerimaan barang berhasil dibatalkan.');
    }

    private function authorizeGoodsReceiptAccess(?GoodsReceipt $goodsReceipt = null): void
    {
        if (!(Auth::guest() || (Auth::check() && in_array(Auth::user()->role ?? '', ['admin', 'superadmin'], true)))) {
            abort(403);
        }
    }

    private function authorizeReviewer(): void
    {
        if (!(Auth::check() && in_array(Auth::user()->role ?? '', ['admin', 'superadmin'], true))) {
            abort(403);
        }
    }

    private function authorizeSuperadmin(): void
    {
        if (!(Auth::check() && (Auth::user()->role ?? '') === 'superadmin')) {
            abort(403);
        }
    }

    private function approvalReadinessErrors(GoodsReceipt $goodsReceipt): array
    {
        $errors = [];
        foreach (['received_date', 'supplier_id', 'branch_id', 'sp_reference_number', 'received_by', 'sender_name'] as $field) {
            if (blank($goodsReceipt->{$field})) {
                $errors[] = 'Lengkapi seluruh informasi penerimaan sebelum mengajukan approval.';
                break;
            }
        }

        $goodsReceipt->loadMissing(['invoices.pages', 'signatures']);
        if ($goodsReceipt->invoices->isEmpty()) {
            $errors[] = 'Minimal harus ada satu faktur.';
        } elseif ($goodsReceipt->invoices->contains(function ($invoice) {
            return blank($invoice->invoice_number)
                || blank($invoice->invoice_date)
                || $invoice->nominal === null
                || $invoice->pages->isEmpty();
        })) {
            $errors[] = 'Lengkapi nomor, tanggal, nominal, dan foto setiap faktur.';
        }

        if ($goodsReceipt->invoices->contains(fn ($invoice) => $invoice->evaluation_status === 'unchecked')) {
            $errors[] = 'Semua faktur harus sudah dievaluasi sebelum diajukan untuk approval.';
        }

        if (blank($goodsReceipt->signatures?->signature_path)) {
            $errors[] = 'Tanda tangan pengirim wajib tersedia sebelum mengajukan approval.';
        }

        return array_values(array_unique($errors));
    }

    private function validateGoodsReceipt(Request $request, ?GoodsReceipt $goodsReceipt = null): array
    {
        $isUpdate = $goodsReceipt !== null;
        $isSubmitting = $request->input('submit_action') === 'submit_approval';
        $required = $isSubmitting ? 'required' : 'nullable';

        $validated = $request->validate([
            'received_date' => [$required, 'date'],
            'supplier_id' => [$required, 'exists:suppliers,id'],
            'branch_id' => [$required, 'exists:branches,id'],

            'sp_reference_number' => [$required, 'string', 'max:255'],
            'received_by' => [$required, 'exists:employees,id'],
            'sender_name' => [$required, 'string', 'max:255'],

            'notes' => ['nullable', 'string'],
            'evaluation_note' => ['nullable', 'string'],

            'submit_action' => ['required', 'in:draft,submit_approval'],

            'invoices' => [$required, 'array', $isSubmitting ? 'min:1' : 'max:100'],
            'invoices.*.id' => ['nullable', 'integer', 'exists:goods_receipt_invoices,id'],

            'invoices.*.invoice_number' => [$required, 'string', 'max:255'],
            'invoices.*.invoice_date' => [$required, 'date'],
            'invoices.*.nominal' => [$required, 'numeric', 'min:0'],

            'invoices.*.evaluation_status' => [
                $required,
                'in:unchecked,matched,rejected,partially_rejected',
            ],
            'invoices.*.evaluation_note' => ['nullable', 'string'],

            // Create: wajib upload foto.
            // Edit: nullable, karena bisa masih punya foto lama.
            'invoices.*.photos' => [($isUpdate || !$isSubmitting) ? 'nullable' : 'required', 'array'],
            'invoices.*.photos.*' => [($isUpdate || !$isSubmitting) ? 'nullable' : 'required', 'file', 'image', 'max:4096'],

            'deleted_invoice_ids' => ['nullable', 'array'],
            'deleted_invoice_ids.*' => ['integer', 'exists:goods_receipt_invoices,id'],

            'deleted_photo_ids' => ['nullable', 'array'],
            'deleted_photo_ids.*' => ['integer', 'exists:goods_receipt_invoice_photos,id'],

            // Create: wajib tanda tangan pengirim.
            // Edit: nullable, karena tanda tangan lama bisa dipertahankan.
            'sender_signature' => [($isUpdate || !$isSubmitting) ? 'nullable' : 'required', 'string'],
            'receiver_signature' => ['nullable', 'string'],
        ], [
            'received_date.required' => 'Tanggal barang datang wajib diisi.',
            'received_date.date' => 'Tanggal barang datang tidak valid.',

            'supplier_id.required' => 'Supplier wajib dipilih.',
            'supplier_id.exists' => 'Supplier tidak valid.',
            'branch_id.required' => 'Cabang wajib dipilih.',
            'branch_id.exists' => 'Cabang tidak valid.',

            'sp_reference_number.required' => 'No referensi SP wajib diisi.',
            'sp_reference_number.max' => 'No referensi SP maksimal 255 karakter.',

            'received_by.required' => 'Nama penerima wajib dipilih.',
            'received_by.exists' => 'Nama penerima tidak valid.',

            'sender_name.required' => 'Nama kurir / pengirim wajib diisi.',
            'sender_name.max' => 'Nama kurir / pengirim maksimal 255 karakter.',

            'submit_action.required' => 'Aksi submit tidak valid.',
            'submit_action.in' => 'Aksi submit tidak valid.',

            'invoices.required' => 'Minimal harus ada 1 faktur.',
            'invoices.array' => 'Format data faktur tidak valid.',
            'invoices.min' => 'Minimal harus ada 1 faktur.',

            'invoices.*.invoice_number.required' => 'Nomor faktur wajib diisi.',
            'invoices.*.invoice_number.max' => 'Nomor faktur maksimal 255 karakter.',

            'invoices.*.invoice_date.required' => 'Tanggal faktur wajib diisi.',
            'invoices.*.invoice_date.date' => 'Tanggal faktur tidak valid.',

            'invoices.*.nominal.required' => 'Nominal faktur wajib diisi.',
            'invoices.*.nominal.numeric' => 'Nominal faktur harus berupa angka.',
            'invoices.*.nominal.min' => 'Nominal faktur tidak boleh kurang dari 0.',

            'invoices.*.evaluation_status.required' => 'Status evaluasi faktur wajib dipilih.',
            'invoices.*.evaluation_status.in' => 'Status evaluasi faktur tidak valid.',

            'invoices.*.photos.required' => 'Foto faktur wajib diupload.',
            'invoices.*.photos.array' => 'Format foto faktur tidak valid.',
            'invoices.*.photos.*.required' => 'Foto faktur wajib diupload.',
            'invoices.*.photos.*.image' => 'File halaman faktur harus berupa gambar.',
            'invoices.*.photos.*.max' => 'Ukuran foto faktur maksimal 4 MB per file.',

            'sender_signature.required' => 'Tanda tangan kurir / pengirim wajib diisi.',
        ]);

        if ($isUpdate && $isSubmitting) {
            $this->validateUpdateInvoicesStillHavePhotos($request, $goodsReceipt);
            $this->validateUpdateSenderSignatureStillExists($request, $goodsReceipt);
        }

        if (
            $validated['submit_action'] === 'submit_approval' &&
            collect($validated['invoices'] ?? [])->contains(
                fn (array $invoice) => $invoice['evaluation_status'] === 'unchecked'
            )
        ) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'invoices' => 'Semua faktur harus sudah dicek sebelum diajukan untuk approval.',
            ]);
        }

        return $validated;
    }

    private function validateUpdateInvoicesStillHavePhotos(Request $request, GoodsReceipt $goodsReceipt): void
    {
        $deletedPhotoIds = collect($request->input('deleted_photo_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $deletedInvoiceIds = collect($request->input('deleted_invoice_ids', []))
            ->filter()
            ->map(fn ($id) => (int) $id)
            ->values();

        $existingInvoices = $goodsReceipt->invoices()
            ->withCount(['pages as remaining_pages_count' => function ($query) use ($deletedPhotoIds) {
                if ($deletedPhotoIds->isNotEmpty()) {
                    $query->whereNotIn('id', $deletedPhotoIds);
                }
            }])
            ->get()
            ->keyBy('id');

        foreach ($request->input('invoices', []) as $index => $invoiceData) {
            $invoiceId = !empty($invoiceData['id'])
                ? (int) $invoiceData['id']
                : null;

            $newPhotoCount = count($request->file("invoices.$index.photos", []) ?? []);

            if (!$invoiceId) {
                if ($newPhotoCount < 1) {
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        "invoices.$index.photos" => 'Foto faktur wajib diupload untuk faktur baru.',
                    ]);
                }

                continue;
            }

            if ($deletedInvoiceIds->contains($invoiceId)) {
                continue;
            }

            $existingInvoice = $existingInvoices->get($invoiceId);

            if (!$existingInvoice) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "invoices.$index.id" => 'Faktur tidak valid.',
                ]);
            }

            $remainingOldPhotoCount = (int) $existingInvoice->remaining_pages_count;
            $totalPhotoAfterUpdate = $remainingOldPhotoCount + $newPhotoCount;

            if ($totalPhotoAfterUpdate < 1) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "invoices.$index.photos" => 'Setiap faktur wajib memiliki minimal 1 foto halaman faktur.',
                ]);
            }
        }
    }

    private function validateUpdateSenderSignatureStillExists(Request $request, GoodsReceipt $goodsReceipt): void
    {
        if ($request->filled('sender_signature')) {
            return;
        }

        $hasExistingSenderSignature = $goodsReceipt->signatures()
            ->whereNotNull('signature_path')
            ->exists();

        if (!$hasExistingSenderSignature) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'sender_signature' => 'Tanda tangan kurir / pengirim wajib diisi.',
            ]);
        }
    }

    private function storeInvoices(Request $request, GoodsReceipt $receipt, array $invoices): void
    {
        foreach ($invoices as $index => $invoiceData) {
            $invoice = $receipt->invoices()->create([
                'invoice_number' => $invoiceData['invoice_number'] ?? null,
                'invoice_date' => $invoiceData['invoice_date'] ?? null,
                'nominal' => $invoiceData['nominal'] ?? 0,
                'evaluation_status' => $invoiceData['evaluation_status'] ?? 'unchecked',
                'evaluation_note' => $invoiceData['evaluation_note'] ?? null,
            ]);

            if ($request->hasFile("invoices.$index.photos")) {
                foreach ($request->file("invoices.$index.photos") as $photoIndex => $photo) {
                    $path = $photo->store('goods-receipts/invoices', 'public');

                    $invoice->pages()->create([
                        'path' => $path,
                        'page_number' => $photoIndex + 1,
                        'original_name' => $photo->getClientOriginalName(),
                        'size' => $photo->getSize(),
                        'mime_type' => $photo->getMimeType(),
                    ]);
                }
            }
        }
    }

    private function storeSignatures(Request $request, GoodsReceipt $receipt): void
    {
        $senderSignaturePath = $this->saveBase64Signature(
            $request->input('sender_signature'),
            'goods-receipts/signatures'
        );

        if ($senderSignaturePath) {
            $receipt->signatures()->create([
                'name' => $receipt->sender_name,
                'signature_path' => $senderSignaturePath,
                'signed_at' => now(),
            ]);
        }
    }

    private function saveBase64Signature(?string $base64, string $folder): ?string
    {
        if (!$base64) {
            return null;
        }

        if (!str_starts_with($base64, 'data:image/png;base64,')) {
            return null;
        }

        $image = str_replace('data:image/png;base64,', '', $base64);
        $image = str_replace(' ', '+', $image);

        $decodedImage = base64_decode($image, true);

        if (!$decodedImage) {
            return null;
        }

        $filename = $folder . '/' . Str::uuid() . '.png';

        Storage::disk('public')->put($filename, $decodedImage);

        return $filename;
    }

    private function resolveReceiptEvaluationStatus(array $invoices): string
    {
        $statuses = collect($invoices)
            ->pluck('evaluation_status')
            ->filter()
            ->unique()
            ->values();

        if ($statuses->isEmpty()) {
            return 'unchecked';
        }

        if ($statuses->contains('unchecked')) {
            return 'unchecked';
        }

        if ($statuses->count() === 1) {
            return $statuses->first();
        }

        return 'partially_rejected';
    }

    private function deleteExistingInvoicesAndPhotos(GoodsReceipt $goodsReceipt): void
    {
        $goodsReceipt->loadMissing('invoices.pages');

        foreach ($goodsReceipt->invoices as $invoice) {
            foreach ($invoice->pages as $page) {
                if ($page->path) {
                    Storage::disk('public')->delete($page->path);
                }
            }
        }

        $goodsReceipt->invoices()->delete();
    }

    private function deleteExistingSignatures(GoodsReceipt $goodsReceipt): void
    {
        $goodsReceipt->loadMissing('signatures');

        foreach ($goodsReceipt->signatures as $signature) {
            if ($signature->signature_path) {
                Storage::disk('public')->delete($signature->signature_path);
            }
        }

        $goodsReceipt->signatures()->delete();
    }

    private function deleteSelectedInvoicePhotos(GoodsReceipt $goodsReceipt, array $photoIds): void
    {
        if (empty($photoIds)) {
            return;
        }

        $photos = $goodsReceipt->invoices()
            ->with(['pages' => function ($query) use ($photoIds) {
                $query->whereIn('id', $photoIds);
            }])
            ->get()
            ->pluck('pages')
            ->flatten();

        foreach ($photos as $photo) {
            if ($photo->path && Storage::disk('public')->exists($photo->path)) {
                Storage::disk('public')->delete($photo->path);
            }

            $photo->delete();
        }
    }

    private function deleteSelectedInvoices(GoodsReceipt $goodsReceipt, array $invoiceIds): void
    {
        if (empty($invoiceIds)) {
            return;
        }

        $invoices = $goodsReceipt->invoices()
            ->whereIn('id', $invoiceIds)
            ->with('pages')
            ->get();

        foreach ($invoices as $invoice) {
            foreach ($invoice->pages as $page) {
                if ($page->path && Storage::disk('public')->exists($page->path)) {
                    Storage::disk('public')->delete($page->path);
                }
            }

            $invoice->delete();
        }
    }

    private function upsertInvoices(Request $request, GoodsReceipt $receipt, array $invoices): void
    {
        foreach ($invoices as $index => $invoiceData) {
            $invoiceId = $invoiceData['id'] ?? null;

            if ($invoiceId) {
                $invoice = $receipt->invoices()
                    ->whereKey($invoiceId)
                    ->firstOrFail();

                $invoice->update([
                    'invoice_number' => $invoiceData['invoice_number'] ?? null,
                    'invoice_date' => $invoiceData['invoice_date'] ?? null,
                    'nominal' => $invoiceData['nominal'] ?? 0,
                    'evaluation_status' => $invoiceData['evaluation_status'] ?? 'unchecked',
                    'evaluation_note' => $invoiceData['evaluation_note'] ?? null,
                ]);
            } else {
                $invoice = $receipt->invoices()->create([
                    'invoice_number' => $invoiceData['invoice_number'] ?? null,
                    'invoice_date' => $invoiceData['invoice_date'] ?? null,
                    'nominal' => $invoiceData['nominal'] ?? 0,
                    'evaluation_status' => $invoiceData['evaluation_status'] ?? 'unchecked',
                    'evaluation_note' => $invoiceData['evaluation_note'] ?? null,
                ]);
            }

            if ($request->hasFile("invoices.$index.photos")) {
                $currentMaxPageNumber = (int) $invoice->pages()->max('page_number');

                foreach ($request->file("invoices.$index.photos") as $photoIndex => $photo) {
                    $path = $photo->store('goods-receipts/invoices', 'public');

                    $invoice->pages()->create([
                        'path' => $path,
                        'page_number' => $currentMaxPageNumber + $photoIndex + 1,
                        'original_name' => $photo->getClientOriginalName(),
                        'size' => $photo->getSize(),
                        'mime_type' => $photo->getMimeType(),
                    ]);
                }
            }
        }

        $this->reorderInvoicePages($receipt);
    }

    private function reorderInvoicePages(GoodsReceipt $receipt): void
    {
        $receipt->load('invoices.pages');

        foreach ($receipt->invoices as $invoice) {
            $invoice->pages
                ->sortBy('page_number')
                ->values()
                ->each(function ($page, $index) {
                    $page->update([
                        'page_number' => $index + 1,
                    ]);
                });
        }
    }

    private function replaceSignaturesIfProvided(Request $request, GoodsReceipt $receipt): void
    {
        $senderSignaturePath = $this->saveBase64Signature(
            $request->input('sender_signature'),
            'goods-receipts/signatures'
        );

        if ($senderSignaturePath) {
            $this->deleteSignatureByType($receipt, 'sender');

            $receipt->signatures()->create([
                'name' => $receipt->sender_name,
                'signature_path' => $senderSignaturePath,
                'signed_at' => now(),
            ]);
        }
    }

    private function deleteSignatureByType(GoodsReceipt $receipt, string $type): void
    {
        $signatures = $receipt->signatures()
            ->get();

        foreach ($signatures as $signature) {
            if (
                $signature->signature_path &&
                Storage::disk('public')->exists($signature->signature_path)
            ) {
                Storage::disk('public')->delete($signature->signature_path);
            }

            $signature->delete();
        }
    }
}
