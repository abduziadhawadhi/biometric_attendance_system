<?php

namespace App\Exports;

use App\Models\Attendance;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class EmployeeAttendanceExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    protected $employeeId;
    protected $start; // Carbon or null
    protected $end;   // Carbon or null
    protected $tz = 'Africa/Nairobi';

    public function __construct(int $employeeId, $start = null, $end = null)
    {
        $this->employeeId = $employeeId;
        $this->start      = $start;
        $this->end        = $end;
    }

    public function collection()
    {
        $query = Attendance::with('employee')   // 👈 load employee relation
            ->where('employee_id', $this->employeeId);

        if ($this->start) {
            $query->where('created_at', '>=', $this->start->toDateTimeString());
        }
        if ($this->end) {
            $query->where('created_at', '<=', $this->end->toDateTimeString());
        }

        $rows = $query->orderBy('created_at', 'desc')->get()->map(function ($a) {
            // created_at is full timestamp: format to local tz
            $created = Carbon::parse($a->created_at)->setTimezone($this->tz);

            // attempt to show check_in/check_out as full datetimes if they contain date
            $checkIn  = $a->check_in;
            $checkOut = $a->check_out;

            // If check_in is time-only (H:i:s), prefix created date
            if ($checkIn && strpos($checkIn, ' ') === false) {
                $checkIn = $created->toDateString() . ' ' . $checkIn;
            }
            if ($checkOut && strpos($checkOut, ' ') === false) {
                $checkOut = $created->toDateString() . ' ' . $checkOut;
            }

            // compute late/overtime (minutes) defensively
            $late = '';
            $overtime = '';
            try {
                if ($checkIn) {
                    $ci = Carbon::parse($checkIn, $this->tz);
                    $officeStart = Carbon::parse($created->toDateString() . ' 08:00:00', $this->tz);
                    $late = $ci->gt($officeStart) ? $ci->diffInMinutes($officeStart) : 0;
                }
                if ($checkOut) {
                    $co = Carbon::parse($checkOut, $this->tz);
                    $officeEnd = Carbon::parse($created->toDateString() . ' 17:00:00', $this->tz);
                    $overtime = $co->gt($officeEnd) ? $co->diffInMinutes($officeEnd) : 0;
                }
            } catch (\Exception $e) {
                $late = '';
                $overtime = '';
            }

            return [
                'Employee Name' => optional($a->employee)->name ?? '',
                'Date'          => $created->toDateString(),
                'Created At'    => $created->toDateTimeString(),
                'Check In'      => $checkIn ?? '',
                'Check Out'     => $checkOut ?? '',
                'Status'        => $a->status,
                'Late (min)'    => $late,
                'Overtime (min)' => $overtime,
                'Notes'         => $a->notes ?? '',
            ];
        });

        // return a Collection of arrays (FromCollection accepts Collection)
        return new Collection($rows->toArray());
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Date',
            'Created At',
            'Check In',
            'Check Out',
            'Status',
            'Late (min)',
            'Overtime (min)',
            'Notes',
        ];
    }
}
