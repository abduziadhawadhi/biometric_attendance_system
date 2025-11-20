<?php

namespace App\Exports;

use App\Models\Employee;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class EmployeeExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Employee::select('id','name','email','department','position','created_at')->get()
            ->map(function($e){
                return [
                    $e->id, $e->name, $e->email, $e->department, $e->position, $e->created_at ? $e->created_at->toDateString() : null
                ];
            });
    }

    public function headings(): array
    {
        return ['ID','Name','Email','Department','Position','Created At'];
    }
}
