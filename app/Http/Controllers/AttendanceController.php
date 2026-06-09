<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Attendances;
use App\Models\AttendancePhotos;
use App\Models\AttendanceEvents;
use App\Models\Schedules;
use App\Models\ShiftTimes;
use App\Models\TimeBalances;
use App\Models\TimeLedgers;
use App\Models\Branches;
use App\Models\Greetings;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AttendanceController extends Controller
{

    public function greeting()
    {
        $greeting = Greetings::whereHas('type', function ($query) {
            $query->where('name', 'Attendance');
        })->inRandomOrder()->limit(1)->get(['name'])->first();

        return response()->json(['greeting' => $greeting->name ?? 'Have a good day']);
    }

    /**
     * Ensure balance row exists for an employee.
     */
    private function ensureBalance(int $employeeId): TimeBalances
    {
        return TimeBalances::firstOrCreate(
            ['id_employee' => $employeeId],
            ['debt_minutes' => 0, 'credit_minutes' => 0]
        );
    }

    /**
     * Get today's schedule for an employee (nullable).
     */
    private function getTodaySchedule(int $employeeId): ?Schedules
    {
        return Schedules::where('id_employee', $employeeId)
            ->whereDate('date', today())
            ->first();
    }

    /** Compute scheduled start (with tolerance) & end (with tolerance) as Carbon instances */
    private function computeShiftBounds(?Schedules $schedule): array
    {
        // Defaults if no shiftTime assigned
        $defaults = [
            'Pagi'  => ['start' => '06:50', 'end' => '14:50', 'tolLate' => 0, 'tolEarly' => 0, 'spans' => false],
            'Siang' => ['start' => '13:00', 'end' => '21:00', 'tolLate' => 0, 'tolEarly' => 0, 'spans' => false],
        ];

        if (!$schedule) {
            $conf = $defaults['Pagi'];
            $start = now()->setTimeFromTimeString($conf['start'])->addMinutes($conf['tolLate']);
            $end   = now()->setTimeFromTimeString($conf['end'])->subMinutes($conf['tolEarly']);
            return [$start, $end];
        }

        if ($schedule->shiftTime) {
            $st = $schedule->shiftTime;
            $start = now()->setTimeFromTimeString($st->start_time)
                          ->addMinutes($st->tolerance_late_minutes ?? 0);
            $end   = now()->setTimeFromTimeString($st->end_time)
                          ->subMinutes($st->tolerance_early_minutes ?? 0);

            // handle overnight window
            $rawStart = now()->setTimeFromTimeString($st->start_time);
            if (($st->spans_midnight ?? false) && $end->lessThan($rawStart)) {
                $end->addDay();
            }
            return [$start, $end];
        }

        // Fallback by shift group
        $conf = $defaults[$schedule->shift] ?? $defaults['Pagi'];
        $start = now()->setTimeFromTimeString($conf['start'])->addMinutes($conf['tolLate']);
        $end   = now()->setTimeFromTimeString($conf['end'])->subMinutes($conf['tolEarly']);
        return [$start, $end];
    }

    /**
     * Compute overtime minutes using end time (and tolerance early) when present,
     * otherwise fallback by Pagi/Siang defaults.
     */
    private function computeOvertimeMinutes(?Schedules $schedule): int
    {
        if (!$schedule) return 0;

        if ($schedule->relationLoaded('shiftTime') || $schedule->id_shift_time) {
            $st = $schedule->shiftTime; /** @var ShiftTimes|null $st */
            if ($st) {
                $end = now()->setTimeFromTimeString($st->end_time);

                // Handle overnight
                $startCandidate = now()->setTimeFromTimeString($st->start_time);
                if (($st->spans_midnight ?? false) && $end->lessThan($startCandidate)) {
                    $end->addDay();
                }

                $end = $end->subMinutes($st->tolerance_early_minutes ?? 0);

                $diff = now()->diffInMinutes($end, false);
                return max(0, $diff);
            }
        }

        // Fallback by group (Pagi/Siang)
        $defaults = [
            'Pagi' => ['end' => '14:50', 'tolerance_early' => 0],
            'Siang'=> ['end' => '21:00', 'tolerance_early' => 0],
        ];
        $conf = $defaults[$schedule->shift] ?? $defaults['Pagi'];

        $end  = now()->setTimeFromTimeString($conf['end'])->subMinutes($conf['tolerance_early']);
        $diff = now()->diffInMinutes($end, false);

        return max(0, $diff);
    }

    /**
     * Create AttendanceEvent and optional AttendancePhoto.
     * Returns the created AttendanceEvent.
     */
    private function storeEventWithPhoto(Attendances $attendance, Request $request, string $type): AttendanceEvents
    {
        $event = AttendanceEvents::create([
            'id_attendance' => $attendance->id,
            'type'          => $type, // 'check_in' | 'check_out'
            'event_at'      => now(),
            'source'        => 'mobile',
            'ip_address'    => $request->ip(),
            'user_agent'    => (string) $request->userAgent(),
            'lat'           => $request->input('lat'),   // optional
            'lng'           => $request->input('lng'),   // optional
            'accuracy_m'    => $request->input('acc'),   // optional
        ]);

        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            // $path = $file->store('Apotek/TUTUP KASIR Bllw1 (File responses)/Foto Kertas Tutup Kasir (File responses)', 'google_closing_cash2');
            $path = $file->store('attendance/'.today()->format('Y/m/d'), 'public');

            AttendancePhotos::create([
                'id_attendance_event' => $event->id,
                'disk'   => 'public',
                'path'   => $path,
                'mime'   => $file->getClientMimeType(),
                'size_kb'=> (int) ceil(($file->getSize() ?? 0) / 1024),
                'width'  => null,  // fill if you want via Intervention/Image later
                'height' => null,
                'hash'   => null,
            ]);
        }

        return $event;
    }

    /**
     * POST /attendance/check-in
     */
    public function checkIn(Request $request)
    {
        $user = $request->user();
        if (!$user?->id_employee) {
            return response()->json(['message' => 'Not linked to employee.'], 422);
        }

        $request->validate([
            'photo' => ['required', 'image'], // 5MB
        ]);

        $today = today();

        // Preload schedule + shiftTime to avoid N+1
        $schedule = $this->getTodaySchedule($user->id_employee);
        if ($schedule) {
            $schedule->load('shiftTime');
        } else {
            return response()->json(['message' => 'No schedule for today.'], 422);
        }

        $attendance = Attendances::firstOrCreate([
            'id_employee' => $user->id_employee,
            'work_date'   => $today,
            'id_branch'   => $schedule->id_branch,
            'id_schedule' => $schedule->id,
        ]);

        if ($attendance->check_in_at) {
            return response()->json(['message' => 'Already checked in.'], 422);
        }

        
        [$startWithTol, $endWithTol] = $this->computeShiftBounds($schedule);
        // late vs early check-in (both in minutes)
        $diffToStart = now()->diffInMinutes($startWithTol, false); // negative if after start
        $lateMinutes = max(0, -$diffToStart);                       // arrived after start(+tol)
        $earlyCheckinMinutes = max(0,  $diffToStart);               // arrived before start(+tol)

        // return response()->json($lateMinutes, 400);

        DB::transaction(function () use ($attendance, $request, $lateMinutes, $earlyCheckinMinutes) {
            // store event + photo first (so event_at used everywhere)
            $this->storeEventWithPhoto($attendance, $request, 'check_in');

            // update attendance summary
            $attendance->update([
                'check_in_at'     => now(),
                'late_minutes'    => $lateMinutes,
                'early_checkin_minutes' => $earlyCheckinMinutes,
                'status'          => 'in_progress',
            ]);
        });

        return response()->json([
            'message' => 'Check-in recorded.',
            'meta' => [
                'late_minutes'          => $lateMinutes,
                'early_checkin_minutes' => $earlyCheckinMinutes,
                'diff_minutes' => $diffToStart,
            ],
        ]);
    }

    /**
     * POST /attendance/check-out
     */
    public function checkOut(Request $request)
    {
        $user = $request->user();
        if (!$user?->id_employee) {
            return response()->json(['message' => 'Not linked to employee.'], 422);
        }

        $request->validate([
            'photo' => ['required', 'image'], // 5MB
        ]);

        $today = today();

        $attendance = Attendances::where('id_employee', $user->id_employee)
            ->whereDate('work_date', $today)
            ->first();

        if (!$attendance || !$attendance->check_in_at) {
            return response()->json(['message' => 'You have not checked in.'], 422);
        }
        if ($attendance->check_out_at) {
            return response()->json(['message' => 'Already checked out.'], 422);
        }

        // Preload schedule + shiftTime
        $schedule = $this->getTodaySchedule($user->id_employee);
        if ($schedule) {
            $schedule->load('shiftTime');
        } else {
            return response()->json(['message' => 'No schedule for today.'], 422);
        }

        [$startWithTol, $endWithTol] = $this->computeShiftBounds($schedule);
        // Overtime vs early leave (minutes)
        $diffToEnd = now()->diffInMinutes($endWithTol, false); // positive if after end
        $earlyLeaveMinutes   = max(0,  $diffToEnd);               // stayed after end(-tolEarly)
        $overtimeMinutes = max(0, -$diffToEnd);               // left before end(-tolEarly)

        DB::transaction(function () use ($attendance, $request, $overtimeMinutes, $earlyLeaveMinutes) {
            $workMinutes = $attendance->check_in_at->diffInMinutes($attendance->check_out_at);
            $this->storeEventWithPhoto($attendance, $request, 'check_out');

            $attendance->update([
                'check_out_at'             => now(),
                'work_minutes'             => $workMinutes,
                'overtime_minutes'         => $overtimeMinutes,
                'early_leave_minutes'      => $earlyLeaveMinutes,
                'status'                   => 'completed',
            ]);
        });

        return response()->json([
            'message' => 'Check-out recorded.',
            'meta' => [
                'overtime_minutes'    => $overtimeMinutes,
                'early_leave_minutes' => $earlyLeaveMinutes,
                'diff_to_end'        => $diffToEnd,
            ],
        ]);
    }

    /**
     * GET /attendance/photo/{type}/{id}
     */
    public function getPhoto(string $type, int $id)
    {
        $event = AttendanceEvents::with('photos')
            ->where('id_attendance', $id)
            ->where('type', $type)
            ->first();

        if (!$event) {
            return response()->json(['message' => 'Photoo not found.'], 404);
        }

        $photo = $event->photos->first();

        if (!$photo) {
            return response()->json(['message' => 'Photo not found.'], 404);
        }

        return response()->json(['img' => $photo->url()]);
    }


    public function index(Request $req)
    {
        // Filters: q(name), branch, date_from, date_to, status
        $qName  = trim($req->get('q', ''));
        $branch = $req->get('branch');
        $from   = $req->get('from');
        $to     = $req->get('to');
        $status = $req->get('status');

        $att = Attendances::query()
            ->with(['employee:id,name', 'branch:id,name'])
            ->when($qName, fn($q) => $q->whereHas('employee', fn($qq) => $qq->where('name','like',"%{$qName}%")))
            ->when($branch, fn($q) => $q->where('id_branch', $branch))
            ->when($status, fn($q) => $q->where('status', $status))
            ->when($from, fn($q) => $q->whereDate('work_date','>=',$from))
            ->when($to, fn($q) => $q->whereDate('work_date','<=',$to))
            ->orderByDesc('created_at')
            ->orderBy('id_employee')
            ->paginate(12)
            ->withQueryString();

        // dropdown branches (optional)
        $branches = Branches::orderBy('name')->get(['id','name']);

        return view('attendances.index', compact('att', 'branches', 'qName', 'branch', 'from', 'to', 'status'));
    }

    public function minutesData(Attendances $attendance)
    {
        // $this->authorize('view', $attendance); // optional

        $penaltySuggested = max(
            (($attendance->late_minutes ?? 0) * 1) //Rumus penalty
            + ($attendance->early_leave_minutes ?? 0),0
        );
        $overtimeSuggested = ($attendance->overtime_minutes ?? 0) + ($attendance->early_checkin_minutes ?? 0);

        return response()->json([
            'ok' => true,
            'attendance' => [
                'id' => $attendance->id,
                'date' => optional($attendance->work_date)->format('d M Y'),
                'employee' => $attendance->employee->name ?? null,
                'branch' => $attendance->branch->name ?? null,
                'status' => $attendance->status,
                'work_minutes' => $attendance->work_minutes ?? 0,
                'late_minutes' => $attendance->late_minutes ?? 0,
                'early_leave_minutes' => $attendance->early_leave_minutes ?? 0,
                'early_checkin_minutes' => $attendance->early_checkin_minutes ?? 0,
                'overtime_minutes' => $attendance->overtime_minutes ?? 0,
                'penalty_minutes' => $attendance->penalty_minutes ?? 0,
                'overtime_applied_minutes' => $attendance->overtime_applied_minutes ?? 0,
                'is_confirmed' => $attendance->is_confirmed,
                'is_late' => $attendance->is_late,
                'late_type' => $attendance->late_type,
            ],
            'suggestions' => [
                'penalty' => $penaltySuggested,
                'overtime' => $overtimeSuggested,
            ],
        ]);
    }

    public function minutesConfirm(Request $request, Attendances $attendance)
    {
        // Max overtime = Early Check-in + Overtime
        $cap = max(($attendance->early_checkin_minutes ?? 0) + ($attendance->overtime_minutes ?? 0), 0);

        $data = $request->validate([
            'is_late'                  => ['required','boolean'],
            'late_type' => ['nullable', 'required_if:is_late,true', 'in:with_permission,without_permission'],
            'penalty_minutes'          => ['required','integer','min:0'],
            'overtime_applied_minutes' => ['required','integer','min:0','max:'.$cap],
            'note'                     => ['nullable','string','max:500'],
        ], [
            'overtime_applied_minutes.max' =>
                "Overtime applied cannot exceed total (Early Check-in + Overtime) = $cap minute(s).",
        ]);

        DB::transaction(function () use ($attendance, $data) {
            $employeeId = $attendance->id_employee;
            $workDate   = $attendance->work_date?->toDateString();

            // --- Old vs New values
            $oldPenalty = (int) ($attendance->penalty_minutes ?? 0);
            $oldOT      = (int) ($attendance->overtime_applied_minutes ?? 0);
            $newPenalty = (int) $data['penalty_minutes'];
            $newOT      = (int) $data['overtime_applied_minutes'];

            // --- Update attendance
            $attendance->penalty_minutes = $newPenalty;
            $attendance->overtime_applied_minutes = $newOT;
            $attendance->notes = $data['note'];
            $attendance->is_confirmed = true;
            $attendance->is_late = $data['is_late'];
            $attendance->late_type = $data['is_late'] ? $data['late_type'] : null;
            $attendance->save();

            // --- Calculate deltas
            $deltaPenalty = $newPenalty - $oldPenalty; // + => add, - => reduce
            $deltaOT      = $newOT - $oldOT;           // + => add, - => spend

            // --- Ensure balance exists
            $balance = TimeBalances::firstOrCreate(
                ['id_employee' => $employeeId],
                ['debt_minutes' => 0, 'credit_minutes' => 0]
            );

            // --- Update TimeBalances
            if ($deltaPenalty > 0) {
                $balance->debt_minutes += $deltaPenalty; // add debt
            } elseif ($deltaPenalty < 0) {
                $balance->debt_minutes = max(0, $balance->debt_minutes + $deltaPenalty); // reduce debt
            }

            if ($deltaOT > 0) {
                $balance->credit_minutes += $deltaOT; // add credit
            } elseif ($deltaOT < 0) {
                $balance->credit_minutes = max(0, $balance->credit_minutes + $deltaOT); // spend credit
            }

            $balance->save();

            // --- Create ledger entries for audit
            $noteExtra = $data['note'] ? (' | '.$data['note']) : '';

            if ($deltaPenalty !== 0) {
                TimeLedgers::create([
                    'id_employee'   => $employeeId,
                    'work_date'     => $workDate,
                    'id_attendance' => $attendance->id,
                    'type'          => $deltaPenalty > 0 ? 'penalty_add' : 'penalty_reduce',
                    'minutes'       => abs($deltaPenalty),
                    'source'        => 'admin_confirm',
                    'note'          => "Penalty adjusted: {$oldPenalty} → {$newPenalty}{$noteExtra}",
                ]);
            }

            if ($deltaOT !== 0) {
                TimeLedgers::create([
                    'id_employee'   => $employeeId,
                    'work_date'     => $workDate,
                    'id_attendance' => $attendance->id,
                    'type'          => $deltaOT > 0 ? 'overtime_add' : 'overtime_spend',
                    'minutes'       => abs($deltaOT),
                    'source'        => 'admin_confirm',
                    'note'          => "Overtime adjusted: {$oldOT} → {$newOT}{$noteExtra}",
                ]);
            }
        });

        return response()->json([
            'ok' => true,
            'message' => 'Minutess confirmed & balances updated.',
            'delta' => [
                'penalty' => $data['penalty_minutes'] - $attendance->penalty_minutes,
                'overtime' => $data['overtime_applied_minutes'] - $attendance->overtime_applied_minutes,
            ]
        ]);
    }

    // return JSON {img: "..."} using your AttendancePhotos::url()
    public function photoUrl(Attendances $attendance, string $type)
    {
        $event = AttendanceEvents::with('photos')
            ->where('id_attendance', $attendance->id)
            ->where('type', $type)
            ->latest('event_at')
            ->first();

        if (!$event || $event->photos->isEmpty()) {
            return response()->json(['message' => 'Photo not found.'], 404);
        }

        return response()->json(['ok'=>true, 'img' => $event->photos->first()->url()]);
    }

    public function resetById(Request $request)
    {
        $validated = $request->validate([
            'attendance_id' => ['required', 'integer', 'exists:attendances,id'],
            'mode' => ['required', 'in:check_in,check_out'],
        ]);

        $attendance = Attendances::findOrFail($validated['attendance_id']);

        // Optional (recommended)
        // $this->authorize('update', $attendance);

        $mode = $validated['mode'];

        DB::transaction(function () use ($attendance, $mode) {

            // Load both events once + photos (avoid repeated queries)
            $events = $attendance->events()
                ->whereIn('type', ['check_in', 'check_out'])
                ->with('photos')
                ->get()
                ->keyBy('type');

            $checkInEvent  = $events->get('check_in');
            $checkOutEvent = $events->get('check_out');

            // Helper: delete photos (db + storage) and delete event row
            $deleteEventWithPhotos = function ($event) {
                if (!$event) return;

                foreach ($event->photos as $photo) {
                    // Adjust column name if not "path"
                    if (!empty($photo->path)) {
                        Storage::disk('public')->delete($photo->path);
                    }
                    $photo->delete();
                }

                $event->delete();
            };

            if ($mode === 'check_out') {
                // Reset ONLY checkout: delete checkout event+photos
                $deleteEventWithPhotos($checkOutEvent);

                $attendance->update([
                    'check_out_at' => null,
                    'work_minutes' => null,      // keep your preference (null)
                    'overtime_minutes' => 0,
                    'early_leave_minutes' => 0,
                    'status' => 'in_progress',
                ]);

                return;
            }

            // mode === 'check_in' => reset ALL: delete both events+photos
            $deleteEventWithPhotos($checkInEvent);
            $deleteEventWithPhotos($checkOutEvent);

            $attendance->update([
                'check_in_at' => null,
                'check_out_at' => null,
                'work_minutes' => null,        // keep your preference (null)

                'late_minutes' => 0,
                'early_leave_minutes' => 0,
                'early_checkin_minutes' => 0,
                'overtime_minutes' => 0,

                'status' => 'not_checked_in',
            ]);
        });

        return response()->json(['message' => 'Reset success.'], 200);
    }

    public function lateness_index(Request $request)
    {
        abort_unless(auth()->user()?->hasRole('superadmin'), 403);

        $month    = $request->input('month', now()->format('Y-m'));
        $branchId = $request->input('branch_id');
        $lateType = $request->input('late_type');
        $search   = trim((string) $request->input('search'));

        $startDate = Carbon::parse($month . '-01')->startOfMonth()->toDateString();
        $endDate   = Carbon::parse($month . '-01')->endOfMonth()->toDateString();

        /*
        |--------------------------------------------------------------------------
        | Main lateness query
        |--------------------------------------------------------------------------
        | Used for summary, daily trend, branch summary, and lateness detail rows.
        | This query only reads actual lateness rows: attendances.is_late = true.
        */
        $latenessBaseQuery = Attendances::query()
            ->join('employees', 'employees.id', '=', 'attendances.id_employee')
            ->leftJoin('branches', 'branches.id', '=', 'employees.id_branch')
            ->whereBetween('attendances.work_date', [$startDate, $endDate])
            ->where('attendances.is_late', true)
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('employees.id_branch', $branchId);
            })
            ->when($lateType, function ($query) use ($lateType) {
                $query->where('attendances.late_type', $lateType);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employees.name', 'like', "%{$search}%")
                    ->orWhere('branches.name', 'like', "%{$search}%");
                });
            });

        /*
        |--------------------------------------------------------------------------
        | Summary cards
        |--------------------------------------------------------------------------
        */
        $summary = (clone $latenessBaseQuery)
            ->selectRaw("
                COUNT(*) as total_late_count,
                COUNT(DISTINCT attendances.id_employee) as total_late_employees,
                COALESCE(SUM(attendances.late_minutes), 0) as total_late_minutes,
                COALESCE(SUM(attendances.penalty_minutes), 0) as total_penalty_minutes,
                COALESCE(ROUND(AVG(attendances.late_minutes)), 0) as avg_late_minutes,
                COALESCE(MAX(attendances.late_minutes), 0) as max_late_minutes,
                COALESCE(SUM(CASE WHEN attendances.late_type = 'with_permission' THEN 1 ELSE 0 END), 0) as with_permission_count,
                COALESCE(SUM(CASE WHEN attendances.late_type = 'without_permission' THEN 1 ELSE 0 END), 0) as without_permission_count
            ")
            ->first();

        /*
        |--------------------------------------------------------------------------
        | Daily trend chart
        |--------------------------------------------------------------------------
        */
        $dailyTrend = (clone $latenessBaseQuery)
            ->selectRaw("
                DATE(attendances.work_date) as date,
                COUNT(*) as late_count,
                COALESCE(SUM(attendances.late_minutes), 0) as late_minutes
            ")
            ->groupBy(DB::raw('DATE(attendances.work_date)'))
            ->orderBy('date')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Employee lateness base
        |--------------------------------------------------------------------------
        | This starts from employees so we can show all employees,
        | including employees with 0 lateness.
        */
        $employeeLateBase = DB::table('employees')
            ->leftJoin('branches', 'branches.id', '=', 'employees.id_branch')
            ->leftJoin('attendances', function ($join) use ($startDate, $endDate, $lateType) {
                $join->on('attendances.id_employee', '=', 'employees.id')
                    ->whereBetween('attendances.work_date', [$startDate, $endDate])
                    ->where('attendances.is_late', true);

                if ($lateType) {
                    $join->where('attendances.late_type', $lateType);
                }
            })
            ->when($branchId, function ($query) use ($branchId) {
                $query->where('employees.id_branch', $branchId);
            })
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('employees.name', 'like', "%{$search}%")
                    ->orWhere('branches.name', 'like', "%{$search}%");
                });
            });

        /*
        |--------------------------------------------------------------------------
        | Employee summary table - paginated
        |--------------------------------------------------------------------------
        */
        $employeeLateSummary = (clone $employeeLateBase)
            ->selectRaw("
                employees.id as employee_id,
                employees.name as employee_name,
                branches.name as branch_name,

                COUNT(attendances.id) as total_late_count,

                COALESCE(SUM(CASE WHEN attendances.late_type = 'with_permission' THEN 1 ELSE 0 END), 0) as with_permission_count,

                COALESCE(SUM(CASE WHEN attendances.late_type = 'without_permission' THEN 1 ELSE 0 END), 0) as without_permission_count,

                COALESCE(SUM(attendances.late_minutes), 0) as total_late_minutes,

                COALESCE(SUM(attendances.penalty_minutes), 0) as total_penalty_minutes
            ")
            ->groupBy('employees.id', 'employees.name', 'branches.name')
            ->orderByDesc('total_late_count')
            ->orderByDesc('without_permission_count')
            ->orderBy('employees.name')
            ->paginate(20, ['*'], 'employees_page')
            ->withQueryString();

        /*
        |--------------------------------------------------------------------------
        | Top employee chart - Top 10 only
        |--------------------------------------------------------------------------
        */
        $topEmployeeLateChart = (clone $employeeLateBase)
            ->selectRaw("
                employees.id as employee_id,
                employees.name as employee_name,

                COUNT(attendances.id) as total_late_count,

                COALESCE(SUM(CASE WHEN attendances.late_type = 'with_permission' THEN 1 ELSE 0 END), 0) as with_permission_count,

                COALESCE(SUM(CASE WHEN attendances.late_type = 'without_permission' THEN 1 ELSE 0 END), 0) as without_permission_count
            ")
            ->groupBy('employees.id', 'employees.name')
            ->havingRaw('COUNT(attendances.id) > 0')
            ->orderByDesc('total_late_count')
            ->orderByDesc('without_permission_count')
            ->limit(10)
            ->get();

        $employeeChartLabels = $topEmployeeLateChart
            ->pluck('employee_name')
            ->values();

        $employeeChartWithPermission = $topEmployeeLateChart
            ->pluck('with_permission_count')
            ->map(fn ($value) => (int) $value)
            ->values();

        $employeeChartWithoutPermission = $topEmployeeLateChart
            ->pluck('without_permission_count')
            ->map(fn ($value) => (int) $value)
            ->values();

        /*
        |--------------------------------------------------------------------------
        | Branch summary
        |--------------------------------------------------------------------------
        */
        $branchSummary = (clone $latenessBaseQuery)
            ->selectRaw("
                COALESCE(branches.name, 'Tanpa Cabang') as branch_name,
                COUNT(*) as late_count,
                COALESCE(SUM(attendances.late_minutes), 0) as total_late_minutes
            ")
            ->groupBy('branches.name')
            ->orderByDesc('late_count')
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Lateness detail rows - paginated
        |--------------------------------------------------------------------------
        */
        $latenessRows = (clone $latenessBaseQuery)
            ->select([
                'attendances.id',
                'attendances.work_date',
                'attendances.check_in_at',
                'attendances.late_minutes',
                'attendances.penalty_minutes',
                'attendances.late_type',

                // Use this if your confirmation panel saves note into minutes_note.
                'attendances.notes',

                // If your real column is notes, replace the line above with:
                // 'attendances.notes',

                'employees.name as employee_name',
                'branches.name as branch_name',
            ])
            ->orderByDesc('attendances.work_date')
            ->paginate(10, ['*'], 'details_page')
            ->withQueryString();

        $branches = Branches::orderBy('name')->get(['id', 'name']);

        /*
        |--------------------------------------------------------------------------
        | Suggestions
        |--------------------------------------------------------------------------
        | Use topEmployeeLateChart, not employeeLateSummary paginator.
        */
        $suggestions = $this->buildLatenessSuggestions(
            $summary,
            $topEmployeeLateChart,
            $branchSummary
        );

        return view('attendances.lateness.index', compact(
            'month',
            'branchId',
            'lateType',
            'search',
            'summary',
            'dailyTrend',
            'employeeLateSummary',
            'topEmployeeLateChart',
            'employeeChartLabels',
            'employeeChartWithPermission',
            'employeeChartWithoutPermission',
            'branchSummary',
            'latenessRows',
            'branches',
            'suggestions'
        ));
    }

    private function buildLatenessSuggestions($summary, $topEmployees, $branchSummary): array
    {
        $suggestions = [];

        $totalLate         = (int) ($summary->total_late_count ?? 0);
        $withoutPermission = (int) ($summary->without_permission_count ?? 0);
        $withPermission    = (int) ($summary->with_permission_count ?? 0);
        $avgLate           = (int) ($summary->avg_late_minutes ?? 0);

        if ($totalLate === 0) {
            return [
                [
                    'type' => 'success',
                    'title' => 'Kedisiplinan bulan ini baik',
                    'body' => 'Belum ada data keterlambatan resmi pada periode ini. Pertahankan monitoring rutin.',
                ],
            ];
        }

        if ($withoutPermission > $withPermission) {
            $suggestions[] = [
                'type' => 'danger',
                'title' => 'Telat tanpa izin lebih dominan',
                'body' => 'Jumlah telat tanpa izin lebih tinggi daripada telat dengan izin. Pertimbangkan briefing kedisiplinan, reminder jam masuk, atau evaluasi shift tertentu.',
            ];
        }

        if ($avgLate >= 15) {
            $suggestions[] = [
                'type' => 'warning',
                'title' => 'Rata-rata keterlambatan cukup tinggi',
                'body' => "Rata-rata keterlambatan sekitar {$avgLate} menit. Ini sudah cukup signifikan untuk memengaruhi operasional shift.",
            ];
        }

        $topEmployee = $topEmployees->first();

        if ($topEmployee && (int) $topEmployee->total_late_count >= 3) {
            $suggestions[] = [
                'type' => 'warning',
                'title' => 'Ada karyawan dengan pola telat berulang',
                'body' => "{$topEmployee->employee_name} tercatat telat {$topEmployee->total_late_count}x bulan ini. Disarankan lakukan coaching personal sebelum masuk ke penalti lebih berat.",
            ];
        }

        $topWithoutPermissionEmployee = $topEmployees
            ->where('without_permission_count', '>', 0)
            ->sortByDesc('without_permission_count')
            ->first();

        if ($topWithoutPermissionEmployee && (int) $topWithoutPermissionEmployee->without_permission_count >= 2) {
            $suggestions[] = [
                'type' => 'danger',
                'title' => 'Ada karyawan sering telat tanpa izin',
                'body' => "{$topWithoutPermissionEmployee->employee_name} tercatat telat tanpa izin {$topWithoutPermissionEmployee->without_permission_count}x bulan ini. Ini perlu diprioritaskan untuk evaluasi kedisiplinan.",
            ];
        }

        $topBranch = $branchSummary->first();

        if ($topBranch && (int) $topBranch->late_count >= 5) {
            $suggestions[] = [
                'type' => 'info',
                'title' => 'Perlu evaluasi cabang',
                'body' => "Cabang {$topBranch->branch_name} memiliki jumlah keterlambatan tertinggi. Cek apakah masalahnya personal, jadwal shift, jarak, atau load kerja cabang.",
            ];
        }

        if (empty($suggestions)) {
            $suggestions[] = [
                'type' => 'info',
                'title' => 'Keterlambatan masih terkendali',
                'body' => 'Data menunjukkan keterlambatan ada, tetapi belum menunjukkan pola risiko besar. Tetap pantau tren mingguan.',
            ];
        }

        return $suggestions;
    }

}
