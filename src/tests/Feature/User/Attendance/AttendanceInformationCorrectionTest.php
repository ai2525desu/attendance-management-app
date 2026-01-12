<?php

namespace Tests\Feature\User\Attendance;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceCorrectRequest;
use App\Models\User;
use Carbon\Carbon;
use Tests\UserTestCase;

class AttendanceInformationCorrectionTest extends UserTestCase
{
    protected User $user;
    protected Carbon $now;
    protected Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = $this->loginUser();
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));
        $this->now = Carbon::now();

        $this->attendance = Attendance::factory()->worked()->for($this->user)->create();
        $clockInTime = Carbon::parse($this->attendance->clock_in);
        $breakStart = $clockInTime->copy()->addHour(4);
        $breakEnd = $breakStart->copy()->addHour(1);
        AttendanceBreak::factory()
            ->forAttendance($this->attendance)
            ->create([
                'break_start' => $breakStart->toDateTimeString(),
                'break_end' => $breakEnd->toDateTimeString(),
            ]);
    }

    // 出勤時間が退勤時間よりも後になっている場合のバリデーションの確認
    public function test_error_message_when_clock_in_time_is_later_than_clock_out_time()
    {
        $response = $this->get(route('user.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);

        $clockOut = Carbon::parse($this->attendance->clock_out);
        $this->post(route('user.attendance.storeCorrection', ['id' => $this->attendance->id]), [
            'attendance_id' => $this->attendance->id,
            'work_date' => $this->attendance->work_date->format('Y-m-d'),
            'correct_clock_in' => $clockOut->copy()->addMinute(30)->format('H:i'),
            'correct_clock_out' => $clockOut->format('H:i'),
            'remarks' => 'テスト用備考',
        ]);

        $response->assertSessionHasErrors(['correct_clock_in']);
        $errors = session('errors')->getBag('default');
        $this->assertEquals('出勤時間もしくは退勤時間が不適切な値です', $errors->first('correct_clock_in'));
    }

    // 休憩開始時間が退勤時間よりも後になっている場合のバリデーション確認
    public function test_error_message_when_break_start_time_is_later_than_clock_out_time()
    {
        $response = $this->get(route('user.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);

        $clockIn = Carbon::parse($this->attendance->clock_in);
        $clockOut = Carbon::parse($this->attendance->clock_out);
        $break = $this->attendance->attendanceBreaks->first();
        $breakEnd = Carbon::parse($break->break_end);

        $this->post(route('user.attendance.storeCorrection', ['id' => $this->attendance->id]), [
            'attendance_id' => $this->attendance->id,
            'work_date' => $this->attendance->work_date->format('Y-m-d'),
            'correct_clock_in' => $clockIn->format('H:i'),
            'correct_clock_out' => $clockOut->format('H:i'),
            'correct_break_start' => [
                0 => ['start' => $clockOut->copy()->addMinute(30)->format('H:i')],
            ],
            'correct_break_end' => [
                0 => ['end' => $breakEnd->format('H:i')],
            ],
            'remarks' => 'テスト用備考',
        ]);

        $response->assertSessionHasErrors(['correct_break_start.0.start']);
        $errors = session('errors')->getBag('default');
        $this->assertEquals('休憩時間が不適切な値です', $errors->first('correct_break_start.0.start'));
    }

    // 休憩終了時間が退勤時間よりも後になっている場合のバリデーション確認
    public function test_error_message_when_break_end_time_is_later_than_clock_out_time()
    {
        $response = $this->get(route('user.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);

        $clockIn = Carbon::parse($this->attendance->clock_in);
        $clockOut = Carbon::parse($this->attendance->clock_out);
        $break = $this->attendance->attendanceBreaks->first();
        $breakStart = Carbon::parse($break->break_start);

        $this->post(route('user.attendance.storeCorrection', ['id' => $this->attendance->id]), [
            'attendance_id' => $this->attendance->id,
            'work_date' => $this->attendance->work_date->format('Y-m-d'),
            'correct_clock_in' => $clockIn->format('H:i'),
            'correct_clock_out' => $clockOut->format('H:i'),
            'correct_break_start' => [
                0 => ['start' => $breakStart->format('H:i')],
            ],
            'correct_break_end' => [
                0 => ['end' => $clockOut->copy()->addMinute(30)->format('H:i')],
            ],
            'remarks' => 'テスト用備考',
        ]);

        $response->assertSessionHasErrors(['correct_break_end.0.end']);
        $errors = session('errors')->getBag('default');
        $this->assertEquals('休憩時間もしくは退勤時間が不適切な値です', $errors->first('correct_break_end.0.end'));
    }

    // 備考欄が未入力の場合のバリデーション確認
    public function test_error_message_when_remarks_field_is_left_blank()
    {
        $response = $this->get(route('user.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);

        $clockIn = Carbon::parse($this->attendance->clock_in);
        $clockOut = Carbon::parse($this->attendance->clock_out);
        $break = $this->attendance->attendanceBreaks->first();
        $breakStart = Carbon::parse($break->break_start);
        $breakEnd = Carbon::parse($break->break_end);

        $this->post(route('user.attendance.storeCorrection', ['id' => $this->attendance->id]), [
            'attendance_id' => $this->attendance->id,
            'work_date' => $this->attendance->work_date->format('Y-m-d'),
            'correct_clock_in' => $clockIn->format('H:i'),
            'correct_clock_out' => $clockOut->format('H:i'),
            'correct_break_start' => [
                0 => ['start' => $breakStart->format('H:i')],
            ],
            'correct_break_end' => [
                0 => ['end' => $breakEnd->format('H:i')],
            ],
            'remarks' => '',
        ]);

        $response->assertSessionHasErrors(['remarks']);
        $errors = session('errors')->getBag('default');
        $this->assertEquals('備考を記入してください', $errors->first('remarks'));
    }

    // 修正申請処理が実行されたとき
    public function test_when_correction_request_processing_is_executed()
    {
        // 修正申請する日時を1日後として設定
        Carbon::setTestNow($this->now->addDay(1));
        $response = $this->get(route('user.attendance.detail', ['id' => $this->attendance->id]));
        $response->assertStatus(200);

        $clockIn = Carbon::parse($this->attendance->clock_in);
        $clockOut = Carbon::parse($this->attendance->clock_out);
        $break = $this->attendance->attendanceBreaks->first();
        $breakStart = Carbon::parse($break->break_start);
        $breakEnd = Carbon::parse($break->break_end);

        // 退勤時間を修正して保存処理
        $this->post(route('user.attendance.storeCorrection', ['id' => $this->attendance->id]), [
            'attendance_id' => $this->attendance->id,
            'work_date' => $this->attendance->work_date->format('Y-m-d'),
            'correct_clock_in' => $clockIn->format('H:i'),
            'correct_clock_out' => $clockOut->copy()->addHour(2)->format('H:i'),
            'correct_break_start' => [
                0 => ['start' => $breakStart->format('H:i')],
            ],
            'correct_break_end' => [
                0 => ['end' => $breakEnd->format('H:i')],
            ],
            'remarks' => '退勤時間を2時間後に修正',
        ]);

        $correctionRequestDate = Carbon::now()->format('Y-m-d');
        $workDate = $this->attendance->work_date->format('Y-m-d');
        $convertedClockIn = Carbon::parse("{$workDate} {$clockIn->format('H:i')}")->format('Y-m-d H:i:s');
        $convertedClockOut = Carbon::parse("{$workDate} {$clockOut->copy()->addHour(2)->format('H:i')}")->format('Y-m-d H:i:s');

        $this->assertDatabaseHas('attendance_correct_requests', [
            'user_id' => $this->user->id,
            'attendance_id' => $this->attendance->id,
            'request_date' => $correctionRequestDate,
            'correct_clock_in' => $convertedClockIn,
            'correct_clock_out' => $convertedClockOut,
            'remarks' => '退勤時間を2時間後に修正',
            'status' => 'pending',
            'edited_by_admin' => false,
        ]);

        $attendanceCorrection = AttendanceCorrectRequest::where('attendance_id', $this->attendance->id)->where('user_id', $this->user->id)->latest('id')->first();
        $attendanceBreak = $this->attendance->attendanceBreaks->first();
        $convertedBreakStart = Carbon::parse("{$workDate} {$breakStart->format('H:i')}")->format('Y-m-d H:i:s');
        $convertedBreakEnd = Carbon::parse("{$workDate} {$breakEnd->format('H:i')}")->format('Y-m-d H:i:s');
        $this->assertDatabaseHas('attendance_break_corrects', [
            'attendance_correct_request_id' => $attendanceCorrection->id,
            'attendance_break_id' => $attendanceBreak->id,
            'correct_break_start' => $convertedBreakStart,
            'correct_break_end' => $convertedBreakEnd,
        ]);

        // 管理者としてログイン
        $admin = Admin::factory()->fixed()->create();
        $this->actingAs($admin, 'admin');

        // 管理者の申請一覧画面にて修正申請内容が表示されているか
        $response = $this->get(route('stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee('退勤時間を2時間後に修正');

        // 管理者の承認画面で修正申請内容が表示されているか
        $response = $this->get(route('admin.stamp_correction_request.approval', ['attendance_correct_request_id' => $attendanceCorrection->id,]));

        $response->assertStatus(200);
        // 修正申請内容が画面に正しく表示されているか（代表値のみ確認）
        $response->assertSee($this->user->name);
        $response->assertSee($clockOut->copy()->addHour(2)->format('H:i'));
        $response->assertSee('退勤時間を2時間後に修正');
    }
}
