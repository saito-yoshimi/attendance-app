<?php

namespace App\Http\Requests;

use Carbon\Carbon;
use Illuminate\Foundation\Http\FormRequest;

class StampCorrectionRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attendance_record_id' => ['required', 'exists:attendance_records,id'],

            'new_clock_in' => ['required', 'date_format:H:i'],
            'new_clock_out' => ['nullable', 'date_format:H:i'],

            // ★ 修正ポイント：配列そのものは必須にする
            'new_break_in' => ['required', 'array'],
            'new_break_in.*' => ['nullable', 'date_format:H:i'],

            'new_break_out' => ['required', 'array'],
            'new_break_out.*' => ['nullable', 'date_format:H:i'],

            'comment' => ['required', 'string'],
        ];
    }

    public function messages(): array
    {
        return [
            'attendance_record_id.required' => '勤怠IDがありません',
            'new_clock_in.required' => '出勤時間を入力してください',
            'comment.required' => '備考を記入してください',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn = $this->input('new_clock_in');
            $clockOut = $this->input('new_clock_out');

            if (! $clockIn || ! $clockOut) {
                return;
            }

            $clockInTime = Carbon::createFromFormat('H:i', $clockIn);
            $clockOutTime = Carbon::createFromFormat('H:i', $clockOut);

            /*
            |--------------------------------------------------------------------------
            | 1. 出勤時間・退勤時間
            |--------------------------------------------------------------------------
            */
            if ($clockInTime->gte($clockOutTime)) {
                $validator->errors()->add(
                    'new_clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | 2. 休憩開始時間
            |--------------------------------------------------------------------------
            */
            foreach ($this->input('new_break_in', []) as $index => $breakIn) {

                if (empty($breakIn)) {
                    continue;
                }

                $breakInTime = Carbon::createFromFormat('H:i', $breakIn);

                if (
                    $breakInTime->lt($clockInTime) ||
                    $breakInTime->gt($clockOutTime)
                ) {
                    $validator->errors()->add(
                        "new_break_in.$index",
                        '休憩時間が不適切な値です'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | 3. 休憩終了時間
            |--------------------------------------------------------------------------
            */
            foreach ($this->input('new_break_out', []) as $index => $breakOut) {

                if (empty($breakOut)) {
                    continue;
                }

                $breakOutTime = Carbon::createFromFormat('H:i', $breakOut);

                if ($breakOutTime->gt($clockOutTime)) {
                    $validator->errors()->add(
                        "new_break_out.$index",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }
}
