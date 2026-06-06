<?php

namespace App\Http\Requests\Technician;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEstimatedCompletionRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role_id === 3; // Technician role
    }

    public function rules()
    {
        return [
            'estimated_completion_date' => 'required|date|after_or_equal:today'
        ];
    }

    public function messages()
    {
        return [
            'estimated_completion_date.required' => 'Estimated completion date is required',
            'estimated_completion_date.date' => 'Please provide a valid date',
            'estimated_completion_date.after_or_equal' => 'Estimated completion date must be today or in the future'
        ];
    }
}
