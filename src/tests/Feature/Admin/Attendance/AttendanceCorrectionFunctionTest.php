<?php

namespace Tests\Feature\Admin\Attendance;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\User;
use Carbon\Carbon;
use Tests\AdminTestCase;

class AttendanceCorrectionFunctionTest extends AdminTestCase
{
    protected Admin $admin;
    protected User $user;
    protected Attendance $attendance;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->loginAdmin();

        // 一般ユーザーの勤怠登録
        Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));

        $this->user = User::factory()->create();
        $today     = Carbon::today();
        $this->attendance = Attendance::factory()->worked($today)->for($this->user)->create();

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

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // 勤怠詳細画面に表示されるデータが選択したものになっているか
    public function test_to_check_whether_the_data_displayed_on_the_attendance_details_screen_is_the_selected_one()
    {
        $response = $this->get(route('admin.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);
        $response->assertSee($this->attendance->work_date->format('Y年'));
        $response->assertSee($this->attendance->work_date->format('m月d日'));
        $response->assertSee($this->attendance->clock_in->format('H:i'));
        $response->assertSee($this->attendance->clock_out->format('H:i'));

        $attendanceBreak = AttendanceBreak::where('attendance_id', $this->attendance->id)->first();

        $response->assertSee($attendanceBreak->break_start->format('H:i'));
        $response->assertSee($attendanceBreak->break_end->format('H:i'));
    }


    // 出勤時間が退勤時間よりも後になっている場合のバリデーションの確認
    public function test_error_message_when_clock_in_time_is_later_than_clock_out_time()
    {
        $response = $this->get(route('admin.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);

        $clockOut = Carbon::parse($this->attendance->clock_out);
        $response = $this->post(route('admin.attendance.storeCorrection', ['id' => $this->attendance->id]), [
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
        $response = $this->get(route('admin.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);

        $clockIn = Carbon::parse($this->attendance->clock_in);
        $clockOut = Carbon::parse($this->attendance->clock_out);
        $break = $this->attendance->attendanceBreaks->first();
        $breakEnd = Carbon::parse($break->break_end);

        $response = $this->post(route('admin.attendance.storeCorrection', ['id' => $this->attendance->id]), [
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
        $response = $this->get(route('admin.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);

        $clockIn = Carbon::parse($this->attendance->clock_in);
        $clockOut = Carbon::parse($this->attendance->clock_out);
        $break = $this->attendance->attendanceBreaks->first();
        $breakStart = Carbon::parse($break->break_start);

        $response = $this->post(route('admin.attendance.storeCorrection', ['id' => $this->attendance->id]), [
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
        $response = $this->get(route('admin.attendance.detail', ['id' => $this->attendance->id]));

        $response->assertStatus(200);
        $response->assertSee($this->user->name);

        $clockIn = Carbon::parse($this->attendance->clock_in);
        $clockOut = Carbon::parse($this->attendance->clock_out);
        $break = $this->attendance->attendanceBreaks->first();
        $breakStart = Carbon::parse($break->break_start);
        $breakEnd = Carbon::parse($break->break_end);

        $response = $this->post(route('admin.attendance.storeCorrection', ['id' => $this->attendance->id]), [
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
}
