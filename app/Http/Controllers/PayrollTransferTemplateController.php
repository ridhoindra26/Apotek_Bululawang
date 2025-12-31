<?php

namespace App\Http\Controllers;

use App\Models\PayrollTransferTemplate;
use Illuminate\Http\Request;

class PayrollTransferTemplateController extends Controller
{
    public function index()
    {
        $templates = PayrollTransferTemplate::query()->orderBy('name')->get();

        // Allowed fields for mapping (for UI helper text)
        $allowedFields = [
            'rekening_snapshot',
            'net_pay',
            'email_snapshot',
            'base_salary_snapshot',
            'allowance_total',
            'deduction_total',
        ];

        return view('payroll.templates.index', compact('templates', 'allowedFields'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:payroll_transfer_templates,name'],
            'delimiter' => ['required', 'string', 'max:5'],
            'encoding' => ['required', 'in:utf8,utf8_bom'],
            'include_header' => ['nullable'],
            'columns_json' => ['required'], // we validate structure below
        ]);

        $columns = json_decode($data['columns_json'], true);
        if (!is_array($columns) || count($columns) === 0) {
            return redirect()->back()->with('error', 'columns_json must be a valid JSON array.');
        }

        foreach ($columns as $i => $col) {
            if (!isset($col['header'], $col['field'])) {
                return redirect()->back()->with('error', "columns_json item #".($i+1)." must have header and field.");
            }
        }

        PayrollTransferTemplate::create([
            'name' => $data['name'],
            'delimiter' => $data['delimiter'],
            'encoding' => $data['encoding'],
            'include_header' => (bool) ($data['include_header'] ?? false),
            'columns_json' => $columns,
        ]);

        return redirect()->back()->with('success', 'Template created.');
    }

    public function update(Request $request, $id)
    {
        $template = PayrollTransferTemplate::findOrFail($id);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', "unique:payroll_transfer_templates,name,{$template->id}"],
            'delimiter' => ['required', 'string', 'max:5'],
            'encoding' => ['required', 'in:utf8,utf8_bom'],
            'include_header' => ['nullable'],
            'columns_json' => ['required'],
        ]);

        $columns = json_decode($data['columns_json'], true);
        if (!is_array($columns) || count($columns) === 0) {
            return redirect()->back()->with('error', 'columns_json must be a valid JSON array.');
        }

        foreach ($columns as $i => $col) {
            if (!isset($col['header'], $col['field'])) {
                return redirect()->back()->with('error', "columns_json item #".($i+1)." must have header and field.");
            }
        }

        $template->update([
            'name' => $data['name'],
            'delimiter' => $data['delimiter'],
            'encoding' => $data['encoding'],
            'include_header' => (bool) ($data['include_header'] ?? false),
            'columns_json' => $columns,
        ]);

        return redirect()->back()->with('success', 'Template updated.');
    }

    public function destroy($id)
    {
        $template = PayrollTransferTemplate::findOrFail($id);
        $template->delete();

        return redirect()->back()->with('success', 'Template deleted.');
    }
}
