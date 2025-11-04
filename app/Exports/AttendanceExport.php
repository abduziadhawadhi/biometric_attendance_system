<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class AttendanceExport implements FromCollection, WithHeadings
{
    protected $employeeName;
    protected $startDate;
    protected $endDate;

    public function __construct($employeeName = null, $startDate = null, $endDate = null)
    {
        $this->employeeName = $employeeName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Attendance::with('employee');

        if ($this->employeeName) {
            $query->whereHas('employee', function ($q) {
                $q->where('name', 'like', "%{$this->employeeName}%");
            });
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('attendance_date', [$this->startDate, $this->endDate]);
        }

        $records = $query->get();

        return $records->map(function ($attendance) {
            return [
                'Employee Name' => $attendance->employee->name ?? 'N/A',
                'Department'    => $attendance->employee->department ?? 'N/A',
                'Date'          => $attendance->attendance_date,
                'Check In'      => $attendance->check_in ?? '-',
                'Check Out'     => $attendance->check_out ?? '-',
                'Status'        => $attendance->check_in ? 'Present' : 'Absent',
            ];
        });
    }

    public function headings(): array
    {
        return ['Employee Name', 'Department', 'Date', 'Check In', 'Check Out', 'Status'];
    }
}
