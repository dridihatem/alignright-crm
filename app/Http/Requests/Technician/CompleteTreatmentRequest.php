<?php

namespace App\Http\Requests\Technician;

use Illuminate\Foundation\Http\FormRequest;

class CompleteTreatmentRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role_id === 3; // Technician role
    }

    public function rules()
    {
        return [
            'wetransfer_link' => 'required|url|max:500',
            'completion_notes' => 'nullable|string|max:1000'
        ];
    }

    public function messages()
    {
        return [
            'wetransfer_link.required' => 'WeTransfer link is required',
            'wetransfer_link.url' => 'Please provide a valid URL',
            'wetransfer_link.max' => 'Link cannot exceed 500 characters',
            'completion_notes.max' => 'Notes cannot exceed 1000 characters'
        ];
    }
}

