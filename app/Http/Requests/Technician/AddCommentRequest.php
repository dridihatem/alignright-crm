<?php

namespace App\Http\Requests\Technician;

use Illuminate\Foundation\Http\FormRequest;

class AddCommentRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->role_id === 3; // Technician role
    }

    public function rules()
    {
        return [
            'comment' => 'required|string|max:1000',
            'case_id' => 'required|exists:case_patients,id',
            'user_id' => 'nullable|integer' // Optional, not used but sent by frontend
        ];
    }

    public function messages()
    {
        return [
            'comment.required' => 'Comment is required',
            'comment.max' => 'Comment cannot exceed 1000 characters',
            'case_id.required' => 'Case ID is required',
            'case_id.exists' => 'Invalid case ID'
        ];
    }
}
