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

use Illuminate\Support\Facades\DB;

class AttendanceController extends Controller
{
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
     * GET /attendance/check-in
     */
    public function dummy()
    {
        return response()->json('$data', 200);
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
}
