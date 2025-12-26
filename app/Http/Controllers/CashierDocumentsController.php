<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

use App\Models\CashierDocuments;
use App\Models\CashierDocumentPhotos;
use App\Models\Branches;
use App\Models\Schedules;

class CashierDocumentsController extends Controller
{
    public function index()
    {
        $today = now();
        $hour = $today->hour;
        $defaultShift = ($hour >= 18 && $hour <= 23) ? 'Siang' : 'Pagi';

        $branches = Branches::orderBy('name')->get(['id','name']);

        $defaultBranch = Schedules::where('date', $today)->where('id_employee', auth()->user()->id_employee)->get(['id_branch']);

        return view('cashiers.index', [
            'defaultShift' => $defaultShift,
            'branches' => $branches,
            'defaultBranch' => $defaultBranch
        ]);
    }

    // public function index(Request $request)
    // {
    //     $query = CashierDocuments::query()
    //         ->with(['cashier', 'confirmer'])
    //         ->latest('date');

    //     // optional: filter by type/status
    //     if ($request->filled('type')) {
    //         $query->where('type', $request->type);
    //     }
    //     if ($request->filled('status')) {
    //         $query->where('status', $request->status);
    //     }

    //     $documents = $query->paginate(20);

    //     return view('cashiers.index', compact('documents'));
    // }

    public function list(Request $request)
    {
        $query = CashierDocuments::query()
            ->with(['cashier.branches' , 'branch', 'photos'])
            ->withCount('photos')
            ->latest('date')
            ->latest('created_at');
            
        // ==== FILTER PARAM ====
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('shift')) {
            $query->where('shift', $request->shift);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('date', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                  ->orWhereHas('cashier', function ($sub) use ($search) {
                      $sub->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $documents = $query->paginate(20)->withQueryString();

        $branches = Branches::orderBy('name')->get();

        $filters = $request->only([
            'type', 'shift', 'status', 'date_from', 'date_to', 'branch_id', 'search',
        ]);

        return view('cashiers.list', compact('documents', 'branches', 'filters'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();        

        $request->validate([
            'date'                => ['required', 'date'],
            'shift'               => ['required', 'in:Pagi,Siang'],
            'branch'           => ['required', 'exists:branches,id'],
            'description'         => ['nullable', 'string'],

            'closing_cash_photo'  => ['required', 'image', 'max:5120'],
            'deposit_slip_photo'  => ['nullable', 'image', 'max:5120'],

            'blood_check_photo'   => ['nullable', 'array'],
            'blood_check_photo.*' => ['image', 'max:5120'],

            'petty_cash_photo'   => ['nullable', 'array'],
            'petty_cash_photo.*' => ['image', 'max:5120'],
        ]);

        
        $baseData = [
            'cashier_id'  => $user->id_employee,
            // 'cashier_id'  => 3,
            'date'        => $request->date,
            'shift'       => $request->shift,
            'description' => $request->description,
            'branch_id'   => $request->branch,
        ];
        
        $typeMapping = [
            'closing_cash_photo' => 'closing_cash',
            'deposit_slip_photo' => 'deposit_slip',
            'blood_check_photo'  => 'blood_check',
            'petty_cash_photo'   => 'petty_cash',
        ];
        
        $diskBaseMapping = [
            'closing_cash' => 'google_closing_cash',
            'deposit_slip' => 'google_deposit_slip',
            'blood_check'  => 'google_blood_check',
            'petty_cash'   => 'google_petty_cash',
        ];
        
        foreach ($typeMapping as $inputName => $type)
        {
            $rawFiles = $request->file($inputName);

            if (! $rawFiles) {
                continue; // tidak ada file untuk type ini
            }

            // bisa single UploadedFile atau array
            $files = is_array($rawFiles) ? $rawFiles : [$rawFiles];

            // kalau tidak ada file valid, skip
            $validFiles = array_filter($files, fn ($f) => $f && $f->isValid());
            if (empty($validFiles)) {
                continue;
            }

            // 1) buat satu record cashier_documents untuk type ini
            $document = CashierDocuments::create($baseData + [
                'type'       => $type,
                // 'photo_path' => null,  // optional: tidak dipakai lagi, kita pakai tabel foto
            ]);

            // 2) tentukan "disk name" berdasarkan type + branch
            $diskBaseName = $diskBaseMapping[$type];                // ex: "google_blood_check"
            $diskName     = $diskBaseName . (string) $request->branch;  // ex: "google_blood_check1"

            // 3) simpan semua file sebagai Child Photos
            foreach ($validFiles as $index => $file) {
                // NOTE:
                // Saat ini kamu pakai $file->store($diskName, 'public')
                // -> ini berarti disk = 'public', folder = $diskName.
                // Kalau nanti mau benar‐benar pakai disk Google Drive (google_blood_check1, dll),
                // ganti ke: Storage::disk($diskName)->putFile('', $file)
                $path = $file->store($diskName, 'public');

                CashierDocumentPhotos::create([
                    'cashier_document_id' => $document->id,
                    'photo_path'          => $path,
                    'sort_order'          => $index,
                ]);

                // OPTIONAL: kalau kamu masih ingin isi photo_path di parent pakai foto pertama
                // if ($index === 0 && empty($document->photo_path)) {
                //     $document->photo_path = $path;
                //     $document->save();
                // }
            }
        }
        

        return redirect()
            ->route('cashier.index')
            ->with('success', 'Dokumen kasir berhasil diupload.');
    }

    public function store_google(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'date'                => ['required', 'date'],
            'shift'               => ['required', 'in:Pagi,Siang'],
            'branch'           => ['required', 'exists:branches,id'],
            'description'         => ['nullable', 'string'],
            'closing_cash_photo'  => ['required', 'image', 'max:5120'],
            'deposit_slip_photo'  => ['nullable', 'image', 'max:5120'],
            'blood_check_photo'   => ['nullable', 'image', 'max:5120'],
            'petty_cash_photo'    => ['nullable', 'image', 'max:5120'],
        ]);

        
        $baseData = [
            'cashier_id'  => $user->id_employee,
            'date'        => $request->date,
            'shift'       => $request->shift,
            'description' => $request->description,
            'branch_id'   => $request->branch,
        ];
        
        $typeMapping = [
            'closing_cash_photo' => 'closing_cash',
            'deposit_slip_photo' => 'deposit_slip',
            'blood_check_photo'  => 'blood_check',
            'petty_cash_photo'   => 'petty_cash',
        ];
        
        $diskBaseMapping = [
            'closing_cash' => 'google_closing_cash',
            'deposit_slip' => 'google_deposit_slip',
            'blood_check'  => 'google_blood_check',
            'petty_cash'   => 'google_petty_cash',
        ];
        
        foreach ($typeMapping as $inputName => $type) {
            if (!$request->hasFile($inputName)) {
                continue;
            }
            
            $file = $request->file($inputName);
            $extension = $file->getClientOriginalExtension();
            
            // 5. Tentukan nama disk berdasarkan type + branch
            $diskBaseName = $diskBaseMapping[$type];             // misal "google_closing_cash"
            $diskName     = $diskBaseName . (string) $request->branch;  // jadi "google_closing_cash1" / "google_closing_cash2"
            
            // 6. Nama file yg rapi
            $filename = now()->format('Ymd')
                . '_' . $type
                . '_' . $request->shift
                . '.' . $extension;
            
            // 7. Simpan ke disk yang sesuai
            //    Adapter Google Drive akan otomatis pakai folderId dari disk yg kamu set di config/filesystems.php
            $path = Storage::disk($diskName)->put('', $file, $filename);
            
            // 8. Simpan record ke DB
            CashierDocuments::create($baseData + [
                'type'       => $type,
                'photo_path' => $path,    // biasanya ini fileId / path relatif tergantung adapter
            ]);
        }

        return redirect()
            ->route('cashier.index')
            ->with('success', 'Dokumen kasir berhasil diupload.');
    }

    public function edit(CashierDocuments $cashierDocuments)
    {
        if (!in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        return view('cashiers.edit', compact('cashierDocuments'));
    }

    public function update(Request $request, CashierDocuments $cashierDocuments)
    {
        $user = auth()->user();
        if (! $user) {
            abort(403);
        }

        $isAdmin    = in_array($user->role, ['admin', 'superadmin']);
        $employeeId = optional($user->employee)->id;   // sesuaikan relasi user -> employee

        // Non-admin hanya boleh edit record miliknya sendiri
        if (! $isAdmin && $cashierDocuments->cashier_id !== $employeeId) {
            abort(403);
        }

        $validated = $request->validate([
            'description' => ['nullable', 'string'],

            'new_photos'   => ['nullable', 'array'],
            'new_photos.*' => ['image', 'max:5120'],

            'delete_photo_ids'   => ['nullable', 'array'],
            'delete_photo_ids.*' => ['integer', 'exists:cashier_document_photos,id'],
        ]);

        // --- update description ---
        if (array_key_exists('description', $validated)) {
            $cashierDocuments->description = $validated['description'];
            $cashierDocuments->save();
        }


        // 2) Delete selected old photos (only from this doc)
        if (!empty($validated['delete_photo_ids'])) {
            $photosToDelete = $cashierDocuments->photos()
                ->whereIn('id', $validated['delete_photo_ids'])
                ->get();

            foreach ($photosToDelete as $photo) {
                try {
                    Storage::disk('public')->delete($photo->photo_path);
                } catch (\Throwable $e) {
                    // optional: log error
                }

                $photo->delete();
            }
        }

        // 3) Add new photos, appended after last sort_order
        if (!empty($validated['new_photos'])) {
            $type     = $cashierDocuments->type;
            $branchId = $cashierDocuments->branch_id;

            $diskBaseMapping = [
                'closing_cash' => 'google_closing_cash',
                'deposit_slip' => 'google_deposit_slip',
                'blood_check'  => 'google_blood_check',
                'petty_cash'   => 'google_petty_cash',
            ];

            $diskBaseName = $diskBaseMapping[$type] ?? null;
            $diskName     = $diskBaseName
                ? $diskBaseName . (string) $branchId
                : 'public'; // fallback

            $currentMaxOrder = $cashierDocuments->photos()->max('sort_order') ?? 0;

            foreach ($validated['new_photos'] as $idx => $file) {
                if (! $file->isValid()) {
                    continue;
                }

                // Sesuaikan dengan cara simpanmu – saat ini masih ke disk 'public' folder $diskName
                $path = $file->store($diskName, 'public');

                CashierDocumentPhotos::create([
                    'cashier_document_id' => $cashierDocuments->id,
                    'photo_path'          => $path,
                    'sort_order'          => $currentMaxOrder + $idx + 1,
                ]);
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Dokumen kasir berhasil diperbarui.']);
        }

        return back()->with('success', 'Dokumen kasir berhasil diperbarui.');
    }


    public function destroy(CashierDocuments $cashierDocuments)
    {
        if (!in_array(auth()->user()->role, ['admin', 'superadmin'])) {
            abort(403);
        }

        if ($cashierDocuments->photo_path) {
            Storage::disk('public')->delete($cashierDocuments->photo_path);
        }

        $cashierDocuments->delete();

        return back()->with('success', 'Data foto berhasil dihapus.');
    }

    // ==== FEATURE: ADMIN KONFIRMASI ====
    public function confirm(Request $request, CashierDocuments $cashierDocuments)
    {
        if (!in_array(auth()->user()->role ?? null, ['admin', 'superadmin'])) {
            abort(403);
        }

        $request->validate([
            'status'     => ['required', 'in:pending,confirmed,rejected'],
            'admin_note' => ['nullable', 'string'],
        ]);

        $status = $request->status;

        $cashierDocuments->status       = $status;
        $cashierDocuments->admin_note   = $request->admin_note;
        $cashierDocuments->confirmed_by = $status === 'pending' ? null : auth()->user()->name;
        $cashierDocuments->confirmed_at = $status === 'pending' ? null : now();
        $cashierDocuments->save();

        return back()->with('success', 'Status dokumen berhasil diperbarui.');
    }
}
