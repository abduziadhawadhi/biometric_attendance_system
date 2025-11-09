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

    public function __construct($employeeName, $startDate, $endDate)
    {
        $this->employeeName = $employeeName;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        $query = Attendance::with('employee');

        if ($this->employeeName) {
            $query->whereHas('employee', function($q) {
                $q->where('name', 'LIKE', '%' . $this->employeeName . '%');
            });
        }

        if ($this->startDate && $this->endDate) {
            $query->whereBetween('created_at', [$this->startDate, $this->endDate]);
        }

        return $query->orderBy('created_at', 'desc')->get()->map(function ($item) {
            return [
                'Employee Name' => $item->employee->name,
                'Department'    => $item->employee->department,
                'Date'          => $item->created_at->format('d M Y'),
                'Check In'      => $item->check_in ?? '-',
                'Check Out'     => $item->check_out ?? '-',
                'Status'        => ucfirst($item->status),
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
            'Status'
        ];
    }
}

