<?php

namespace App\Http\Requests\Laboratory;

use Illuminate\Foundation\Http\FormRequest;

class AddCommentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->role_id === 4; // Laboratory role
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'comment' => 'required|string|max:1000',
            'case_id' => 'required|exists:case_patients,id'
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'comment.required' => 'Comment is required.',
            'comment.max' => 'Comment must not exceed 1000 characters.',
            'case_id.required' => 'Case ID is required.',
            'case_id.exists' => 'The selected case does not exist.'
        ];
    }
}
