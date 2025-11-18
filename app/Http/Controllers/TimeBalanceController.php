<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\TimeBalances;
use App\Models\TimeLedgers;
use App\Models\Employees;

use Illuminate\Support\Facades\DB;

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
        // return back()->with('success', 'Sorry this feature is not yet implemented.');
        $employee = Employees::findOrFail($id);

        $data = $request->validate([
            'penalty_minutes'          => ['required', 'integer', 'min:0'],
            'overtime_applied_minutes' => ['required', 'integer', 'min:0'],
            'note'                     => ['required', 'string', 'max:500'],
        ]);
        
        $penalty  = (int) $data['penalty_minutes'];
        $ot       = (int) $data['overtime_applied_minutes'];
        $note     = $data['note'];
        $today    = now()->toDateString();
        
        if ($penalty === 0 && $ot === 0) {
            return back()->with('error', 'Penalty and overtime cannot be both 0.');
        }

        DB::transaction(function () use ($employee, $penalty, $ot, $note, $today) {
            // Ensure balance exists
            $balance = TimeBalances::firstOrCreate(
                ['id_employee' => $employee->id],
                ['debt_minutes' => 0, 'credit_minutes' => 0]
            );

            // Apply increments (only if > 0)
            if ($penalty > 0) {
                $balance->debt_minutes += $penalty;
            }
            if ($ot > 0) {
                $balance->credit_minutes += $ot;
            }
            $balance->save();

            // Ledger entries (only when something changed)
            $noteExtra = $note ? " | $note" : '';

            if ($penalty > 0) {
                TimeLedgers::create([
                    'id_employee'   => $employee->id,
                    'work_date'     => $today,
                    'id_attendance' => null,
                    'type'          => 'penalty_add',
                    'minutes'       => $penalty,
                    'source'        => 'manual_adjust',
                    'note'          => "Penalty added: +{$penalty} min{$noteExtra}",
                ]);
            }

            if ($ot > 0) {
                TimeLedgers::create([
                    'id_employee'   => $employee->id,
                    'work_date'     => $today,
                    'id_attendance' => null,
                    'type'          => 'overtime_add',
                    'minutes'       => $ot,
                    'source'        => 'manual_adjust',
                    'note'          => "Overtime added: +{$ot} min{$noteExtra}",
                ]);
            }
        });

        return back()->with('success', 'Time balance adjusted.');
    }

    /**
     * Show ledger entries by employee id.
     *
     * @param Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function showMe()
    {
        $user = auth()->user();

        abort_unless($user?->id_employee, 403, 'No employee bound to this user.');

        // Adjust fields/relations to your model names
        $rows = TimeLedgers::where('id_employee', $user->id_employee)
            ->orderByDesc('work_date')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(function ($r) {
                // Normalize to a simple JSON the UI can render
                return [
                    'date'    => optional($r->work_date)->format('Y-m-d'),
                    'time'    => optional($r->created_at)->format('H:i'),
                    'type'    => $r->type,                  // e.g. 'credit' | 'debit' | 'initial_balance'
                    'minutes' => (int) $r->minutes,         // positive integer minutes stored
                    'source'  => (string) $r->source,       // e.g. 'system' | 'manual' | 'attendance'
                    'note'    => (string) ($r->note ?? ''),
                ];
            });

        return response()->json($rows);
    }
}