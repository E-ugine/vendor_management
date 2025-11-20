<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreVendorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'address' => ['required', 'string'],
            'category' => ['required', 'string', 'in:Supplier,Service Provider,Contractor'],
            'documents.*' => ['nullable', 'string', 'max:255'], // Assuming documents are file names or paths for simplicity
        ];
    }

    public function messages(): array
    {
        return [
            'category.in' => 'Please select a valid category.',
        ];
    }
}