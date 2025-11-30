<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceCorrectionFormRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'correct_clock_in' => 'nullable|date_format:H:i|before:correct_clock_out',
            'correct_clock_out' => 'nullable|date_format:H:i|after:correct_clock_in',
            'correct_break_start.*.start' => 'nullable|date_format:H:i|after:correct_clock_in|before:correct_clock_out',
            'correct_break_end.*.end' => 'nullable|date_format:H:i|before:correct_clock_out',
            'remarks' => 'required|max:255',
        ];
    }


    public function messages()
    {
        return [
            'correct_clock_in.date_format' => '修正時刻は00:00という形式で入力してください',
            'correct_clock_in.before' => '出勤時間もしくは退勤時間が不適切な値です',
            'correct_clock_out.date_format' => '修正時刻は00:00という形式で入力してください',
            'correct_clock_out.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'correct_break_start.*.start.date_format' => '修正時刻は00:00という形式で入力してください',
            'correct_break_start.*.start.after' => '休憩時間が不適切な値です',
            'correct_break_start.*.start.before' => '休憩時間が不適切な値です',
            'correct_break_end.*.end.date_format' => '修正時刻は00:00という形式で入力してください',
            'correct_break_end.*.end.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'remarks.required' => '備考を記入してください',
            'remarks.max' => '255文字以内で入力してください',
        ];
    }
}
