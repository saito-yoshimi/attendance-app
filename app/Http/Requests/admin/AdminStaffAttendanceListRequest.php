<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AdminStaffAttendanceListRequest extends FormRequest
{
    /**
     * 認可
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * バリデーション
     */
    public function rules(): array
    {
        return [
            'date' => ['nullable', 'date'],
        ];
    }

    public function messages(): array
    {
        return [
            'date.date' => '年月が不正です。',
        ];
    }
}
