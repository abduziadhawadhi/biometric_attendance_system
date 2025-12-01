<?php

namespace App\Exports;

use App\Models\Attendance;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceExport implements FromCollection, WithHeadings
{
    protected ?string $employeeName;
    protected ?string $startDate;
    protected ?string $endDate;
    protected string $tz = 'Africa/Nairobi';

    /**
     * @param string|null $employeeName
     * @param string|null $startDate    YYYY-MM-DD or null
     * @param string|null $endDate      YYYY-MM-DD or null
     */
    public function __construct(?string $employeeName = null, ?string $startDate = null, ?string $endDate = null)
    {
        $this->employeeName = $employeeName ?: null;
        $this->startDate    = $startDate ?: null;
        $this->endDate      = $endDate ?: null;
    }

    public function collection()
    {
        $tz    = $this->tz;
        $today = Carbon::now($tz)->toDateString();

        $query = Attendance::with('employee');

        // -----------------------------
        // DATE FILTERS – SAFE LOGIC
        // -----------------------------
        if ($this->startDate && $this->endDate) {
            // Both provided: use between (full day bounds)
            $query->whereBetween('created_at', [
                $this->startDate . ' 00:00:00',
                $this->endDate   . ' 23:59:59',
            ]);
        } elseif ($this->startDate) {
            // Only start
            $query->where('created_at', '>=', $this->startDate . ' 00:00:00');
        } elseif ($this->endDate) {
            // Only end
            $query->where('created_at', '<=', $this->endDate . ' 23:59:59');
        } else {
            // No dates selected: default to TODAY
            $query->whereDate('created_at', $today);
        }

        // -----------------------------
        // SEARCH BY EMPLOYEE / DEPT / EMAIL / POSITION
        // -----------------------------
        if ($this->employeeName) {
            $name = $this->employeeName;

            $query->whereHas('employee', function ($q) use ($name) {
                $q->where('name', 'ILIKE', "%{$name}%")
                    ->orWhere('email', 'ILIKE', "%{$name}%")
                    ->orWhere('department', 'ILIKE', "%{$name}%")
                    ->orWhere('position', 'ILIKE', "%{$name}%");
            });
        }

        $records = $query
            ->orderBy('created_at', 'desc')
            ->get();

        // Map to rows with computed late/overtime columns
        return $records->map(function ($att) use ($tz) {
            // created_at -> date for this record (timezone-aware)
            $created = Carbon::parse($att->created_at)->setTimezone($tz);
            $date = $created->toDateString();

            // office schedule
            $officeStart = Carbon::parse($date . ' 08:00:00', $tz);
            $officeEnd   = Carbon::parse($date . ' 17:00:00', $tz);

            $lateMinutes = 0;
            $overtimeMinutes = 0;

            // Helper to create Carbon from either full timestamp or H:i:s
            $makeDateTime = function (?string $value) use ($date, $tz) {
                if (!$value) return null;

                // if it already looks like a full date/time (yyyy-mm-dd)
                if (preg_match('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}/', $value)) {
                    try {
                        return Carbon::parse($value, $tz);
                    } catch (\Exception $e) {
                        // fallthrough
                    }
                }

                // if only time like "08:45:11" or "8:45:11"
                // or any other time string: combine with record date
                try {
                    return Carbon::parse($date . ' ' . trim($value), $tz);
                } catch (\Exception $e) {
                    return null;
                }
            };

            $checkInDT = $makeDateTime($att->check_in);
            $checkOutDT = $makeDateTime($att->check_out);

            if ($checkInDT && $checkInDT->gt($officeStart)) {
                $lateMinutes = $checkInDT->diffInMinutes($officeStart);
            }

            if ($checkOutDT && $checkOutDT->gt($officeEnd)) {
                $overtimeMinutes = $checkOutDT->diffInMinutes($officeEnd);
            }

            return [
                'Employee Name' => optional($att->employee)->name,
                'Department'    => optional($att->employee)->department,
                'Email'         => optional($att->employee)->email,
                'Date'          => $created->toDateString(),
                // present the check-in/out as stored (time or timestamp)
                'Check In'      => $att->check_in,
                'Check Out'     => $att->check_out,
                'Late (min)'    => $lateMinutes,
                'Overtime (min)'=> $overtimeMinutes,
                'Status'        => ucfirst($att->status),
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Department',
            'Email',
            'Date',
            'Check In',
            'Check Out',
            'Late (min)',
            'Overtime (min)',
            'Status',
        ];
    }
}
