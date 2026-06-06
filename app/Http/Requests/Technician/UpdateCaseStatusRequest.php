<?php

namespace App\Http\Requests\Technician;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCaseStatusRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role_id === 3; // Technician role
    }

    public function rules()
    {
        return [
            'status' => 'required|in:pending,approval,rejected,in_planning,in_production,shipped'
        ];
    }

    public function messages()
    {
        return [
            'status.required' => 'Status is required',
            'status.in' => 'Invalid status selected'
        ];
    }
}

