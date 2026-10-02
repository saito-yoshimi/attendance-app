<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class IndexAttendanceRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 認証不要（GET）
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer', 'exists:users,id'],
            'date' => ['nullable', 'date_format:Y-m-d'],
            'month' => ['nullable', 'date_format:Y-m'],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.integer' => 'user_id は整数で指定してください。',
            'date.date_format' => 'date は YYYY-MM-DD 形式で指定してください。',
            'month.date_format' => 'month は YYYY-MM 形式で指定してください。',
            'page.integer' => 'page は整数で指定してください。',
            'per_page.integer' => 'per_page は整数で指定してください。',
            'per_page.max' => 'per_page は最大 100 まで指定できます。',
            'user_id.exists' => '指定されたユーザーは存在しません。',
        ];
    }
}
