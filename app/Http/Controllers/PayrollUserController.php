<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\PayrollPeriod;
use App\Models\PayrollItem;

class PayrollUserController extends Controller
{
    public function index()
    {
        $employeeId = auth()->user()->id_employee;
        abort_if(!$employeeId, 403, 'User is not linked to an employee.');
    
        $periods = PayrollPeriod::query()
            ->where('status', 'paid')
            ->whereHas('items', function ($q) use ($employeeId) {
                $q->where('id_employee', $employeeId);
            })
            ->orderByDesc('date_from')
            ->paginate(12);
    
        return view('payroll.user.index', compact('periods'));
    }

    public function show($periodId)
    {
        $employeeId = auth()->user()->id_employee;
        abort_if(!$employeeId, 403, 'User is not linked to an employee.');

        $period = PayrollPeriod::findOrFail($periodId);
        abort_if($period->status !== 'paid', 403);

        $item = PayrollItem::query()
            ->where('payroll_period_id', $period->id)
            ->where('id_employee', $employeeId)
            ->firstOrFail();

        return view('payroll.user.show', compact('period', 'item'));
    }

    public function slip($periodId)
    {
        $employeeId = auth()->user()->id_employee;
        abort_if(!$employeeId, 403, 'User is not linked to an employee.');

        $period = PayrollPeriod::findOrFail($periodId);
        abort_if($period->status !== 'paid', 403);

        $item = PayrollItem::with([
            'employee',
            'period',
            'lines' => fn ($q) => $q->orderBy('type')->orderBy('id'),
        ])
        ->where('payroll_period_id', $period->id)
        ->where('id_employee', $employeeId)
        ->firstOrFail();

        return view('payroll.user.slip', compact('period', 'item'));
    }
}
