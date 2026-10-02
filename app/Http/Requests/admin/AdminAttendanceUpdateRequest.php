<?php

namespace App\Http\Requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class AdminAttendanceUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function rules(): array
    {
        return [
            'new_clock_in' => [
                'required',
                'date_format:H:i',
            ],

            'new_clock_out' => [
                'required',
                'date_format:H:i',
            ],

            'new_break_in.*' => [
                'nullable',
                'date_format:H:i',
            ],

            'new_break_out.*' => [
                'nullable',
                'date_format:H:i',
            ],

            'comment' => [
                'required',
                'string',
            ],
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $clockIn = $this->input('new_clock_in');
            $clockOut = $this->input('new_clock_out');

            // 出勤 < 退勤
            if ($clockIn && $clockOut && $clockIn >= $clockOut) {

                $validator->errors()->add(
                    'new_clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );

                return;
            }

            $breakIns = $this->input('new_break_in', []);
            $breakOuts = $this->input('new_break_out', []);

            foreach ($breakIns as $index => $breakIn) {

                $breakOut = $breakOuts[$index] ?? null;

                // 両方空ならチェックしない
                if (empty($breakIn) && empty($breakOut)) {
                    continue;
                }

                // 片方のみ入力
                if (empty($breakIn) || empty($breakOut)) {
                    $validator->errors()->add(
                        "new_break_in.$index",
                        '休憩時間が不適切な値です'
                    );

                    continue;
                }

                // 休憩開始 >= 休憩終了
                if ($breakIn >= $breakOut) {
                    $validator->errors()->add(
                        "new_break_in.$index",
                        '休憩時間が不適切な値です'
                    );
                }

                // 出勤前または退勤後
                if (
                    ($clockIn && $breakIn < $clockIn) ||
                    ($clockOut && $breakOut > $clockOut)
                ) {
                    $validator->errors()->add(
                        "new_break_in.$index",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'comment.required' => '備考を記入してください',
        ];
    }
}
