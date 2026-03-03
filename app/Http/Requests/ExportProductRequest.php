<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExportProductRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules()
    {
        return [
            'format' => 'required|in:csv,excel',
            'search' => 'nullable|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'status' => 'nullable|in:active,deleted,all',
        ];
    }

    public function messages()
    {
        return [
            'format.required' => 'Export format is required',
            'format.in' => 'Export format must be either "csv" or "excel"',
            'category_id.exists' => 'Selected category does not exist',
        ];
    }
}
