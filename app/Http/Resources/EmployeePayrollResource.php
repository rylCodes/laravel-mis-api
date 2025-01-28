<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeePayrollResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->staff->firstname . ' ' . $this->staff->lastname,
            'present_day' => $this->present_day,
            'total_salary' => $this->total_salary,
            'overtime' => $this->over_time,
            'yearly_bonus' => $this->yearly_bonus,
            'sales_comission' => $this->sales_comission,
            'incentives' => $this->incentives,
            'sss' => $this->sss,
            'pag_ibig' => $this->pag_ibig,
            'philhealth' => $this->philhealth,
            'net_income' => $this->net_income,
            'total_deductions' => $this->total_deductions,
            'final_salary' => $this->final_salary,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'pay_date' => $this->pay_date

        ];
    }
}
