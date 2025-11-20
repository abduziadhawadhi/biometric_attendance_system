<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;

class AttendanceExport implements FromCollection, WithHeadings
{
    protected $employeeName;
    protected $startDate;
    protected $endDate;

    public function __construct($employeeName, $startDate, $endDate)
    {
        $this->employeeName = $employeeName;
        $this->startDate    = $startDate;
        $this->endDate      = $endDate;
    }

    public function collection()
    {
        $tz = 'Africa/Nairobi';

        $query = Attendance::with('employee')
            ->whereBetween('created_at', [
                $this->startDate . ' 00:00:00',
                $this->endDate   . ' 23:59:59',
            ]);

        if ($this->employeeName) {
            $name = $this->employeeName;
            $query->whereHas('employee', function ($q) use ($name) {
                $q->where('name', 'ILIKE', "%{$name}%")
                  ->orWhere('email', 'ILIKE', "%{$name}%")
                  ->orWhere('department', 'ILIKE', "%{$name}%");
            });
        }

        $rows = $query->orderBy('created_at', 'desc')->get();

        return $rows->map(function ($att) use ($tz) {

            $date        = Carbon::parse($att->created_at, $tz)->toDateString();
            $officeStart = Carbon::parse($date . ' 08:00:00', $tz);
            $officeEnd   = Carbon::parse($date . ' 17:00:00', $tz);

            $lateMinutes     = 0;
            $overtimeMinutes = 0;

            if ($att->check_in) {
                $checkIn = Carbon::parse($date . ' ' . $att->check_in, $tz);
                if ($checkIn->gt($officeStart)) {
                    $lateMinutes = $checkIn->diffInMinutes($officeStart);
                }
            }

            if ($att->check_out) {
                $checkOut = Carbon::parse($date . ' ' . $att->check_out, $tz);
                if ($checkOut->gt($officeEnd)) {
                    $overtimeMinutes = $checkOut->diffInMinutes($officeEnd);
                }
            }

            return [
                'Employee Name' => $att->employee->name ?? '',
                'Department'    => $att->employee->department ?? '',
                'Date'          => $date,
                'Check In'      => $att->check_in,
                'Check Out'     => $att->check_out,
                'Status'        => $att->status,
                'Late (minutes)'     => $lateMinutes,
                'Overtime (minutes)' => $overtimeMinutes,
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Employee Name',
            'Department',
            'Date',
            'Check In',
            'Check Out',
            'Status',
            'Late (minutes)',
            'Overtime (minutes)',
        ];
    }
}
