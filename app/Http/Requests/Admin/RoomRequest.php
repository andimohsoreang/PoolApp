<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RoomRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $rules = [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('rooms', 'name'),
            ],
            'type' => [
                'required',
                'in:regular,vip,vvip',
            ],
            'description' => [
                'nullable',
                'string',
            ],
            'status' => [
                'sometimes',
                'boolean',
            ],
        ];

        // Jika update, tambahkan pengecualian untuk unique constraint
        if ($this->isMethod('PUT') || $this->isMethod('PATCH')) {
            $rules['name'][3] = Rule::unique('rooms', 'name')->ignore($this->room);
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama ruangan harus diisi.',
            'name.string' => 'Nama ruangan harus berupa teks.',
            'name.max' => 'Nama ruangan maksimal 255 karakter.',
            'name.unique' => 'Nama ruangan sudah digunakan.',
            'type.required' => 'Tipe ruangan harus dipilih.',
            'type.in' => 'Tipe ruangan tidak valid.',
            'description.string' => 'Deskripsi harus berupa teks.',
            'status.boolean' => 'Status harus berupa nilai boolean.',
        ];
    }

    /**
     * Prepare the data for validation.
     *
     * @return void
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'status' => $this->status ? 1 : 0,
        ]);
    }
}
