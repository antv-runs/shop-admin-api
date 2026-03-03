<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CategoryRequest extends FormRequest
{
    public function authorize()
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    public function rules()
    {
        $categoryId = $this->route('category');

        if ($this->isMethod('post')) {
            return [
                'name' => 'required|string|min:3|max:100|unique:categories,name',
                'description' => 'nullable|string',
            ];
        }

        // PUT/PATCH (update)
        return [
            'name' => 'required|string|min:3|max:100|unique:categories,name,' . ($categoryId ?? 'NULL'),
            'description' => 'nullable|string',
        ];
    }
}
