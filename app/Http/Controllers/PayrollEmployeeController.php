<?php

namespace App\Http\Controllers;

use App\Models\Employees;
use Illuminate\Http\Request;

class PayrollEmployeeController extends Controller
{
    public function index()
    {
        $employees = Employees::query()
            ->with(['branches', 'roles'])
            ->orderBy('name')
            ->paginate(20);

        return view('payroll.employees.index', compact('employees'));
    }

    public function update(Request $request, $id)
    {
        $employee = Employees::findOrFail($id);

        $data = $request->validate([
            'base_salary' => ['required', 'integer', 'min:0'],
            'bank_name' => ['nullable', 'string', 'max:255'],
            'bank_account_number' => ['nullable', 'string', 'max:255'],
            'bank_account_holder' => ['nullable', 'string', 'max:255'],
            'payroll_email' => ['nullable', 'email', 'max:255'],
            'payroll_active' => ['nullable'],
        ]);

        $employee->update([
            'base_salary' => (int) $data['base_salary'],
            'bank_name' => $data['bank_name'] ?? null,
            'bank_account_number' => $data['bank_account_number'] ?? null,
            'bank_account_holder' => $data['bank_account_holder'] ?? null,
            'payroll_email' => $data['payroll_email'] ?? null,
            'payroll_active' => (bool) ($data['payroll_active'] ?? false),
        ]);

        return redirect()->back()->with('success', 'Employee payroll settings updated.');
    }
}
