<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            /*
             * 勤怠ID
             */
            'attendance_record_id' => [
                'required',
                'exists:attendance_records,id',
            ],

            /*
             * 出勤
             */
            'new_clock_in' => [
                'required',
                'date_format:H:i',
            ],

            /*
             * 退勤
             */
            'new_clock_out' => [
                'required',
                'date_format:H:i',
            ],

            /*
             * 休憩開始
             */
            'new_break_in.*' => [
                'nullable',
                'date_format:H:i',
            ],

            /*
             * 休憩終了
             */
            'new_break_out.*' => [
                'nullable',
                'date_format:H:i',
            ],

            /*
             * 備考
             */
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

            /*
             * 出勤 > 退勤
             */
            if (
                $clockIn &&
                $clockOut &&
                $clockIn >= $clockOut
            ) {

                $validator->errors()->add(
                    'new_clock_in',
                    '出勤時間もしくは退勤時間が不適切な値です'
                );

                return;
            }

            $breakIns = $this->input(
                'new_break_in',
                []
            );

            $breakOuts = $this->input(
                'new_break_out',
                []
            );

            foreach ($breakIns as $index => $breakIn) {

                $breakOut =
                    $breakOuts[$index]
                    ?? null;

                /*
                 * 両方空
                 */
                if (
                    empty($breakIn) &&
                    empty($breakOut)
                ) {
                    continue;
                }

                /*
                 * 片方だけ入力
                 */
                if (
                    empty($breakIn) ||
                    empty($breakOut)
                ) {

                    $validator->errors()->add(
                        "new_break_in.$index",
                        '休憩時間が不適切な値です'
                    );

                    continue;
                }

                /*
                 * 休憩開始 >= 休憩終了
                 */
                if (
                    $breakIn >= $breakOut
                ) {

                    $validator->errors()->add(
                        "new_break_in.$index",
                        '休憩時間が不適切な値です'
                    );
                }

                /*
                 * 出勤前の休憩
                 */
                if (
                    $clockIn &&
                    $breakIn < $clockIn
                ) {

                    $validator->errors()->add(
                        "new_break_in.$index",
                        '休憩時間が不適切な値です'
                    );
                }

                /*
                 * 退勤後の休憩
                 */
                if (
                    $clockOut &&
                    $breakOut > $clockOut
                ) {

                    $validator->errors()->add(
                        "new_break_out.$index",
                        '休憩時間もしくは退勤時間が不適切な値です'
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'attendance_record_id.required' => '勤怠データが存在しません',

            'attendance_record_id.exists' => '勤怠データが存在しません',

            'new_clock_in.required' => '出勤時間を入力してください',

            'new_clock_out.required' => '退勤時間を入力してください',

            'comment.required' => '備考を記入してください',
        ];
    }
}
