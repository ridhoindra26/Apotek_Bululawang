<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TimeBalances;
use App\Models\TimeLedgers;
use App\Models\Employees;

class TimeBalanceController extends Controller
{
    /**
     * Get all time balances.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $timeBalances = TimeBalances::with('employee:id,name')->paginate(10);

        return view('attendances.timebalances.index', compact('timeBalances'));
    }

    /**
     * Show the form for viewing a time balance.
     *
     */
    public function show(Request $request, int $id)
    {
        $employeeId = $id;
        $employee = Employees::findOrFail($employeeId);
        $balance = TimeBalances::where('id_employee', $employeeId)->first();
        // dd($employeeId);

        $ledgers = TimeLedgers::where('id_employee', $employeeId)
            ->when($request->from, fn($q) => $q->whereDate('work_date', '>=', $request->from))
            ->when($request->to, fn($q) => $q->whereDate('work_date', '<=', $request->to))
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('attendances.timebalances.show', compact('employee', 'balance', 'ledgers'))
            ->with([
                'from' => $request->from,
                'to'   => $request->to,
                'type' => $request->type,
            ]);
    }

    /**
     * Adjust time balance.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function adjust(Request $request, int $id)
    {
        return back()->with('success', 'Sorry this feature is not yet implemented.');
        $employeeId = $id;
        $employee = Employees::findOrFail($employeeId);
        $balance = TimeBalances::where('id_employee', $employeeId)->first();

        $request->validate([
            'debit_minutes' => ['required', 'integer', 'min:0'],
            'credit_minutes' => ['required', 'integer', 'min:0'],
        ]);

        DB::transaction(function () use ($balance, $request) {
            $balance->debt_minutes += $request->debit_minutes;
            $balance->credit_minutes += $request->credit_minutes;
            $balance->save();
        });

        return redirect()->route('attendances.timebalances.show', ['id' => $employeeId])
            ->with('success', 'Time balance adjusted.');
    }
}