<?php

namespace App\Http\Controllers;

use App\Models\PayrollPeriod;
use App\Models\PayrollItem;
use App\Models\PayrollItemLine;
use App\Models\PayrollTransferTemplate;
use App\Models\Employees;

use App\Services\Payroll\PayrollGenerator;
use App\Services\Payroll\PayrollCalculator;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollPeriodController extends Controller
{
    public function index()
    {
        $periods = PayrollPeriod::query()
            ->withCount('items')
            ->orderByDesc('date_from')
            ->orderByDesc('id')
            ->paginate(15);

        return view('payroll.periods.index', compact('periods'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:payroll_periods,code'],
            'name' => ['required', 'string', 'max:255'],
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
        ]);

        PayrollPeriod::create([
            'code' => $data['code'],
            'name' => $data['name'],
            'date_from' => $data['date_from'],
            'date_to' => $data['date_to'],
            'status' => 'draft',
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('payroll.periods.index')->with('success', 'Payroll period created.');
    }

    public function show($id)
    {
        $period = PayrollPeriod::with([
            'items.employee',
            'items.lines' => fn ($q) => $q->orderBy('type')->orderBy('id'),
        ])->findOrFail($id);

        $employees = Employees::query()
            ->orderBy('name')
            ->get(['id', 'name', 'id_branch']);

        $templates = PayrollTransferTemplate::query()->orderBy('name')->get();

        return view('payroll.periods.show', compact('period', 'templates', 'employees'));
    }

    public function generateItems(Request $request, $id, PayrollGenerator $generator)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft periods can generate items.');
        }

        $data = $request->validate([
            'employee_ids' => ['required', 'array', 'min:1'],
            'employee_ids.*' => ['integer', 'exists:employees,id'],
        ]);

        try {
            $result = $generator->generateForPeriod($period, $data['employee_ids']); // implement below
            return redirect()->back()->with('success', "Generated: {$result['created']} (skipped existing: {$result['skipped']}).");
        } catch (\Throwable $e) {
            return redirect()->back()->with('error', $e->getMessage());
        }
    }


    public function lock($id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->status !== 'draft') {
            return redirect()->back()->with('error', 'Only draft periods can be locked.');
        }

        $period->status = 'locked';
        $period->save();

        return redirect()->back()->with('success', 'Payroll period locked.');
    }

    private function ensureDraftByItem(PayrollItem $item): void
    {
        $item->loadMissing('period');
        if (!$item->period || $item->period->status !== 'draft') {
            abort(403, 'Payroll period is locked / not editable.');
        }
    }

    private function ensureDraftByLine(PayrollItemLine $line): PayrollItem
    {
        $item = PayrollItem::findOrFail($line->payroll_item_id);
        $this->ensureDraftByItem($item);
        return $item;
    }

    public function storeLine(Request $request, PayrollCalculator $calculator)
    {
        $data = $request->validate([
            'payroll_item_id' => ['required', 'integer', 'exists:payroll_items,id'],
            'type' => ['required', 'in:allowance,deduction'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['required', 'integer', 'min:0'],
        ]);

        $item = PayrollItem::findOrFail($data['payroll_item_id']);
        $this->ensureDraftByItem($item);

        DB::transaction(function () use ($data, $item, $calculator) {
            PayrollItemLine::create([
                'payroll_item_id' => $item->id,
                'type' => $data['type'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'amount' => (int) $data['amount'],
                'source' => 'manual',
                'created_by' => auth()->id(),
            ]);

            $calculator->recalculate($item);
        });

        return redirect()->back()->with('success', 'Line item added.');
    }

    public function updateLine(Request $request, $id, PayrollCalculator $calculator)
    {
        $line = PayrollItemLine::findOrFail($id);
        $item = $this->ensureDraftByLine($line);

        $data = $request->validate([
            'type' => ['required', 'in:allowance,deduction'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'amount' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($line, $data, $item, $calculator) {
            $line->update([
                'type' => $data['type'],
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'amount' => (int) $data['amount'],
            ]);

            $calculator->recalculate($item);
        });

        return redirect()->back()->with('success', 'Line item updated.');
    }

    public function destroyLine($id, PayrollCalculator $calculator)
    {
        $line = PayrollItemLine::findOrFail($id);
        $item = $this->ensureDraftByLine($line);

        DB::transaction(function () use ($line, $item, $calculator) {
            $line->delete();
            $calculator->recalculate($item);
        });

        return redirect()->back()->with('success', 'Line item deleted.');
    }

    public function exportCsv(Request $request, $id)
    {
        $period = PayrollPeriod::with('items')->findOrFail($id);

        // Recommended: allow export only when locked (to prevent mismatch)
        if ($period->status !== 'locked' && $period->status !== 'paid') {
            return redirect()->back()->with('error', 'Export allowed only when period is locked/paid.');
        }

        $data = $request->validate([
            'template_id' => ['required', 'integer', 'exists:payroll_transfer_templates,id'],
        ]);

        $template = PayrollTransferTemplate::findOrFail($data['template_id']);
        $columns = $template->columns_json; // array

        $filename = "payroll_{$period->code}.csv";

        $items = PayrollItem::query()
            ->where('payroll_period_id', $period->id)
            ->orderBy('id_employee')
            ->get();

        $delimiter = $template->delimiter ?? ',';

        $callback = function () use ($items, $columns, $delimiter, $template) {
            $out = fopen('php://output', 'w');

            // Optional BOM
            if (($template->encoding ?? 'utf8') === 'utf8_bom') {
                echo "\xEF\xBB\xBF";
            }

            if ($template->include_header) {
                fputcsv($out, array_map(fn ($c) => $c['header'], $columns), $delimiter);
            }

            foreach ($items as $item) {
                $row = [];
                foreach ($columns as $col) {
                    $field = $col['field'] ?? null;
                    $row[] = $field ? data_get($item, $field) : '';
                }
                fputcsv($out, $row, $delimiter);
            }

            fclose($out);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=utf-8',
        ]);
    }

    public function markPaid(Request $request, $id)
    {
        $period = PayrollPeriod::findOrFail($id);

        if ($period->status !== 'locked') {
            return redirect()->back()->with('error', 'Only locked periods can be marked as paid.');
        }

        $data = $request->validate([
            'paid_note' => ['nullable', 'string', 'max:2000'],
        ]);

        $period->update([
            'status' => 'paid',
            'paid_at' => now(),
            'paid_note' => $data['paid_note'] ?? null,
        ]);

        return redirect()->back()->with('success', 'Payroll period marked as paid.');
    }

    public function invoice($id)
    {
        $item = PayrollItem::with([
            'employee',
            'period',
            'lines' => fn ($q) => $q->orderBy('type')->orderBy('id'),
        ])->findOrFail($id);

        return view('payroll.admin.invoice', compact('item'));
    }

    public function sendInvoiceWhatsapp($id)
    {
        $item = PayrollItem::with(['employee', 'period'])->findOrFail($id);

        if ($item->period->status !== 'paid') {
            return redirect()->back()->with('error', 'Only PAID periods can be sent.');
        }

        $rawPhone = $item->employee->phone ?? null;
        $name = $item->employee->name ?? "GAADA NAMANYA BJIR";
        $waNumber = $this->normalizeIdWhatsappNumber($rawPhone);

        if (!$waNumber) {
            return redirect()->back()->with(
                'error',
                'Nomor telepon tidak valid. Gunakan format 62xxxxxxxxxx (contoh: 62812xxxxxxx) atau 08xxxxxxxxxx.'
            );
        }

        // Send USER slip URL (not admin)
        $slipUrl = route('payroll.user.slip', ['periodId' => $item->payroll_period_id]);
        $periodLabel = $item->period->name ?? $item->period->code;

        $message = "Hiii {$name},\nSlip gaji sudah tersedia dan bisa dicek melalui website Apotek.\n\nSilakan login ke akun masing-masing, lalu buka menu Gaji.Jika ada kendala akses atau data tidak sesuai, bisa hubungi RIDHO ya.\n\nTerima kasih.";

        $waUrl = 'https://wa.me/' . $waNumber . '?text=' . urlencode($message);

        return redirect()->away($waUrl);
    }

    /**
     * Normalize Indonesian phone number to WhatsApp format: digits only, starts with 62.
     * Accepts: 62..., +62..., 0..., 8...
     */
    private function normalizeIdWhatsappNumber(?string $raw): ?string
    {
        if (!$raw) return null;

        $digits = preg_replace('/\D+/', '', $raw);
        if (!$digits) return null;

        if (str_starts_with($digits, '62')) {
            $wa = $digits;
        } elseif (str_starts_with($digits, '0')) {
            $wa = '62' . substr($digits, 1);
        } elseif (str_starts_with($digits, '8')) {
            $wa = '62' . $digits;
        } else {
            return null;
        }

        // Basic sanity length check (Indonesia numbers usually fit here)
        $len = strlen($wa);
        if ($len < 10 || $len > 15) return null;

        return $wa;
    }

}