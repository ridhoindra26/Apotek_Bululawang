<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use ZipArchive;

class SupplierController extends Controller
{
    public function index(Request $request)
    {
        $query = Supplier::query();

        if ($request->filled('search')) {
            $search = $request->search;

            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('contact_person', 'like', "%{$search}%")
                    ->orWhere('bank_name', 'like', "%{$search}%")
                    ->orWhere('bank_account_number', 'like', "%{$search}%")
                    ->orWhere('bank_account_holder', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            }

            if ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $suppliers = $query
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        return view('suppliers.create');
    }

    public function importForm()
    {
        return view('suppliers.import');
    }

    public function downloadImportTemplate()
    {
        $temporaryFile = tempnam(sys_get_temp_dir(), 'supplier-template-');
        $zip = new ZipArchive();

        if ($zip->open($temporaryFile, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Template Excel tidak dapat dibuat.');
        }

        $contentTypes = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            .'<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            .'<Default Extension="xml" ContentType="application/xml"/>'
            .'<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            .'<Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>'
            .'<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            .'</Types>';
        $rootRelations = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            .'</Relationships>';
        $workbook = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            .'<sheets><sheet name="Supplier" sheetId="1" r:id="rId1"/></sheets></workbook>';
        $workbookRelations = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            .'<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>'
            .'<Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>'
            .'</Relationships>';
        $styles = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<fonts count="2"><font><sz val="11"/><name val="Arial"/></font><font><b/><color rgb="FFFFFFFF"/><sz val="11"/><name val="Arial"/></font></fonts>'
            .'<fills count="3"><fill><patternFill patternType="none"/></fill><fill><patternFill patternType="gray125"/></fill><fill><patternFill patternType="solid"><fgColor rgb="FF287C79"/><bgColor indexed="64"/></patternFill></fill></fills>'
            .'<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>'
            .'<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>'
            .'<cellXfs count="3"><xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/><xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1" applyAlignment="1"><alignment horizontal="center" vertical="center"/></xf><xf numFmtId="49" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/></cellXfs>'
            .'<cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles></styleSheet>';

        $rows = [
            ['nama', 'kode', 'telepon', 'email', 'alamat', 'contact_person', 'telepon_contact_person', 'npwp', 'bank_rekening', 'no_rekening', 'nama_pemilik_rekening', 'status'],
            ['PT Contoh Supplier', 'SUP-001', '081234567890', 'supplier@example.com', 'Jl. Contoh No. 1', 'Budi', '081298765432', '00.000.000.0-000.000', 'BCA', '0123456789', 'PT Contoh Supplier', 'aktif'],
        ];
        $sheetRows = '';
        foreach ($rows as $rowIndex => $row) {
            $cells = '';
            foreach ($row as $columnIndex => $value) {
                $column = chr(65 + $columnIndex);
                $style = $rowIndex === 0 ? 1 : (in_array($columnIndex, [1, 2, 6, 7, 9], true) ? 2 : 0);
                $escaped = htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
                $cells .= '<c r="'.$column.($rowIndex + 1).'" t="inlineStr" s="'.$style.'"><is><t>'.$escaped.'</t></is></c>';
            }
            $sheetRows .= '<row r="'.($rowIndex + 1).'"'.($rowIndex === 0 ? ' ht="24" customHeight="1"' : '').'>'.$cells.'</row>';
        }
        $worksheet = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>'
            .'<cols><col min="1" max="1" width="28" customWidth="1"/><col min="2" max="4" width="20" customWidth="1"/><col min="5" max="5" width="35" customWidth="1"/><col min="6" max="12" width="22" customWidth="1"/></cols>'
            .'<sheetData>'.$sheetRows.'</sheetData><autoFilter ref="A1:L2"/></worksheet>';

        $zip->addFromString('[Content_Types].xml', $contentTypes);
        $zip->addFromString('_rels/.rels', $rootRelations);
        $zip->addFromString('xl/workbook.xml', $workbook);
        $zip->addFromString('xl/_rels/workbook.xml.rels', $workbookRelations);
        $zip->addFromString('xl/styles.xml', $styles);
        $zip->addFromString('xl/worksheets/sheet1.xml', $worksheet);
        $zip->close();

        return response()->download(
            $temporaryFile,
            'template-import-supplier.xlsx',
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        )->deleteFileAfterSend(true);
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240',
                function (string $attribute, $value, \Closure $fail) {
                    if (!in_array(strtolower($value->getClientOriginalExtension()), ['xlsx', 'csv'], true)) {
                        $fail('File harus menggunakan format XLSX atau CSV.');
                    }
                },
            ],
            'update_existing' => ['nullable', 'boolean'],
        ]);

        try {
            $rows = $this->readImportRows(
                $request->file('file')->getRealPath(),
                strtolower($request->file('file')->getClientOriginalExtension())
            );
        } catch (\Throwable $exception) {
            report($exception);
            return back()->withInput()->with('error', 'File tidak dapat dibaca. Pastikan format Excel tidak rusak.');
        }

        if (count($rows) < 2) {
            return back()->withInput()->with('error', 'File tidak memiliki data supplier.');
        }

        if (count($rows) > 2001) {
            return back()->withInput()->with('error', 'Maksimal 2.000 supplier dalam satu kali import.');
        }

        $aliases = [
            'nama' => 'name', 'name' => 'name', 'kode' => 'code', 'code' => 'code',
            'telepon' => 'phone', 'phone' => 'phone', 'email' => 'email', 'alamat' => 'address', 'address' => 'address',
            'contact_person' => 'contact_person', 'kontak' => 'contact_person',
            'telepon_contact_person' => 'contact_person_phone', 'contact_person_phone' => 'contact_person_phone',
            'npwp' => 'npwp',
            'bank_rekening' => 'bank_name', 'bank' => 'bank_name', 'bank_name' => 'bank_name',
            'no_rekening' => 'bank_account_number', 'nomor_rekening' => 'bank_account_number', 'bank_account_number' => 'bank_account_number',
            'nama_pemilik_rekening' => 'bank_account_holder', 'pemilik_rekening' => 'bank_account_holder', 'bank_account_holder' => 'bank_account_holder',
            'status' => 'status',
        ];
        $normalizeHeader = function ($header) use ($aliases) {
            $normalized = Str::of((string) $header)
                ->replace(["\xEF\xBB\xBF", "\u{00A0}"], ['', ' '])
                ->trim()
                ->lower()
                ->replaceMatches('/[\s\-]+/u', '_')
                ->value();

            return $aliases[$normalized] ?? null;
        };

        $headerRowIndex = collect($rows)
            ->take(20)
            ->search(fn ($row) => collect($row)->contains(fn ($cell) => $normalizeHeader($cell) === 'name'));

        if ($headerRowIndex === false) {
            return back()->withInput()->with('error', 'Kolom wajib "nama" tidak ditemukan. Jangan mengubah nama header pada template.');
        }

        $headers = collect($rows[$headerRowIndex])->map($normalizeHeader)->all();
        $rows = array_slice($rows, $headerRowIndex + 1);

        $created = 0;
        $updated = 0;
        $errors = [];
        $seenCodes = [];

        DB::transaction(function () use ($rows, $headers, $request, &$created, &$updated, &$errors, &$seenCodes) {
            foreach ($rows as $index => $values) {
                $rowNumber = $index + 2;
                $data = [];
                foreach ($headers as $column => $field) {
                    if ($field) $data[$field] = trim((string) ($values[$column] ?? ''));
                }
                if (collect($data)->every(fn ($value) => $value === '')) continue;

                $status = Str::lower($data['status'] ?? 'aktif');
                $data['is_active'] = !in_array($status, ['nonaktif', 'tidak aktif', 'inactive', '0', 'no'], true);
                unset($data['status']);
                foreach ($data as $key => $value) {
                    if ($value === '') $data[$key] = null;
                }

                $validator = Validator::make($data, [
                    'name' => ['required', 'string', 'max:255'], 'code' => ['nullable', 'string', 'max:50'],
                    'phone' => ['nullable', 'string', 'max:50'], 'email' => ['nullable', 'email', 'max:255'],
                    'address' => ['nullable', 'string'], 'contact_person' => ['nullable', 'string', 'max:255'],
                    'contact_person_phone' => ['nullable', 'string', 'max:50'], 'npwp' => ['nullable', 'string', 'max:100'],
                    'bank_name' => ['nullable', 'string', 'max:100'],
                    'bank_account_number' => ['nullable', 'string', 'max:100'],
                    'bank_account_holder' => ['nullable', 'string', 'max:255'],
                    'is_active' => ['boolean'],
                ]);
                if ($validator->fails()) {
                    $errors[] = "Baris {$rowNumber}: ".implode(' ', $validator->errors()->all());
                    continue;
                }

                $codeKey = Str::lower((string) ($data['code'] ?? ''));
                if ($codeKey !== '' && isset($seenCodes[$codeKey])) {
                    $errors[] = "Baris {$rowNumber}: kode supplier duplikat di dalam file.";
                    continue;
                }
                if ($codeKey !== '') $seenCodes[$codeKey] = true;

                $existing = $data['code'] ? Supplier::where('code', $data['code'])->first() : null;
                if ($existing && !$request->boolean('update_existing')) {
                    $errors[] = "Baris {$rowNumber}: kode {$data['code']} sudah terdaftar.";
                    continue;
                }
                if ($existing) {
                    $existing->update($data);
                    $updated++;
                } else {
                    Supplier::create($data);
                    $created++;
                }
            }
        });

        return redirect()->route('suppliers.index')->with([
            'success' => "Import selesai: {$created} supplier baru, {$updated} supplier diperbarui.",
            'import_errors' => array_slice($errors, 0, 100),
            'warning' => count($errors) > 100 ? 'Hanya 100 kesalahan pertama yang ditampilkan.' : null,
        ]);
    }

    private function readImportRows(string $path, string $extension): array
    {
        if ($extension === 'csv') {
            $handle = fopen($path, 'rb');
            $rows = [];
            $sample = (string) fgets($handle);
            rewind($handle);
            $delimiter = substr_count($sample, ';') > substr_count($sample, ',') ? ';' : ',';
            while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
                if (isset($row[0])) $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', $row[0]);
                $rows[] = $row;
            }
            fclose($handle);
            return $rows;
        }

        if ($extension !== 'xlsx') {
            throw new \RuntimeException('Format Excel lama tidak didukung. Simpan file sebagai XLSX atau CSV.');
        }

        $zip = new ZipArchive();
        if ($zip->open($path) !== true) throw new \RuntimeException('File XLSX tidak dapat dibuka.');

        $sharedStrings = [];
        $sharedXml = $zip->getFromName('xl/sharedStrings.xml');
        if ($sharedXml !== false) {
            $document = new \DOMDocument();
            if (!$document->loadXML($sharedXml, LIBXML_NONET | LIBXML_NOBLANKS)) {
                $zip->close();
                throw new \RuntimeException('Shared strings XLSX tidak dapat dibaca.');
            }
            $xpath = new \DOMXPath($document);
            foreach ($xpath->query('//*[local-name()="si"]') as $item) {
                $text = '';
                foreach ($xpath->query('.//*[local-name()="t"]', $item) as $node) {
                    $text .= $node->textContent;
                }
                $sharedStrings[] = $text;
            }
        }

        $sheetXml = $zip->getFromName('xl/worksheets/sheet1.xml');
        $zip->close();
        if ($sheetXml === false) throw new \RuntimeException('Worksheet pertama tidak ditemukan.');

        $document = new \DOMDocument();
        if (!$document->loadXML($sheetXml, LIBXML_NONET | LIBXML_NOBLANKS)) {
            throw new \RuntimeException('Worksheet pertama tidak dapat dibaca.');
        }
        $xpath = new \DOMXPath($document);
        $rows = [];
        foreach ($xpath->query('//*[local-name()="sheetData"]/*[local-name()="row"]') as $row) {
            $values = [];
            foreach ($xpath->query('./*[local-name()="c"]', $row) as $cell) {
                $reference = $cell->attributes?->getNamedItem('r')?->nodeValue ?? 'A';
                preg_match('/^[A-Z]+/', $reference, $match);
                $column = $this->excelColumnIndex($match[0] ?? 'A');
                $type = $cell->attributes?->getNamedItem('t')?->nodeValue ?? '';
                $valueNode = $xpath->query('./*[local-name()="v"]', $cell)->item(0);
                $value = $valueNode?->textContent ?? '';
                if ($type === 'inlineStr') {
                    $value = '';
                    foreach ($xpath->query('.//*[local-name()="is"]//*[local-name()="t"]', $cell) as $textNode) {
                        $value .= $textNode->textContent;
                    }
                }
                if ($type === 's') $value = $sharedStrings[(int) $value] ?? '';
                $values[$column] = $value;
            }
            if ($values) {
                $lastColumn = max(array_keys($values));
                $rows[] = array_replace(array_fill(0, $lastColumn + 1, ''), $values);
            }
        }
        return $rows;
    }

    private function excelColumnIndex(string $letters): int
    {
        $index = 0;
        foreach (str_split($letters) as $letter) $index = ($index * 26) + (ord($letter) - 64);
        return $index - 1;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => ['nullable', 'string', 'max:50', 'unique:suppliers,code'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_person_phone' => ['nullable', 'string', 'max:50'],
            'npwp' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);

        Supplier::create($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil ditambahkan.');
    }

    public function show(Supplier $supplier)
    {
        $supplier->load([
            'goodsReceipts' => function ($query) {
                $query
                    ->latest('received_date')
                    ->latest()
                    ->limit(10);
            },
        ]);

        return view('suppliers.show', compact('supplier'));
    }

    public function edit(Supplier $supplier)
    {
        return view('suppliers.edit', compact('supplier'));
    }

    public function update(Request $request, Supplier $supplier)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'code' => [
                'nullable',
                'string',
                'max:50',
                Rule::unique('suppliers', 'code')->ignore($supplier->id),
            ],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string'],
            'contact_person' => ['nullable', 'string', 'max:255'],
            'contact_person_phone' => ['nullable', 'string', 'max:50'],
            'npwp' => ['nullable', 'string', 'max:100'],
            'bank_name' => ['nullable', 'string', 'max:100'],
            'bank_account_number' => ['nullable', 'string', 'max:100'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $supplier->update($validated);

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil diperbarui.');
    }

    public function destroy(Supplier $supplier)
    {
        abort_unless(auth()->check() && (auth()->user()->role ?? '') === 'superadmin', 403);

        if ($supplier->goodsReceipts()->exists()) {
            return back()->with(
                'error',
                'Supplier tidak bisa dihapus karena sudah memiliki data penerimaan barang.'
            );
        }

        $supplier->delete();

        return redirect()
            ->route('suppliers.index')
            ->with('success', 'Supplier berhasil dihapus.');
    }
}
