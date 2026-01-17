<?php

namespace Tests\Feature\Admin\Attendance;

use App\Models\Admin;
use App\Models\Attendance;
use App\Models\AttendanceBreak;
use App\Models\AttendanceBreakCorrect;
use App\Models\AttendanceCorrectRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Tests\AdminTestCase;

class ListOfCorrectionAndApprovedTest extends AdminTestCase
{
    protected Admin $admin;
    protected Collection $users;
    protected Collection $pendingAttendances;
    protected Collection $approvedAttendances;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = $this->loginAdmin();


        $this->users = User::factory()->count(2)->create();
        $this->pendingAttendances = new Collection();
        $this->approvedAttendances = new Collection();

        foreach ($this->users as $user) {

            //  pending用の修正前の勤怠登録
            Carbon::setTestNow(Carbon::create(2026, 1, 6, 8, 0, 0));
            $pendingDate = Carbon::now();
            $pendingAttendance = Attendance::factory()->worked($pendingDate)->for($user)->create();
            $clockInTime = Carbon::parse($pendingAttendance->clock_in);
            $breakStart = $clockInTime->copy()->addHour(4);
            $breakEnd = $breakStart->copy()->addHour(1);

            AttendanceBreak::factory()
                ->forAttendance($pendingAttendance)
                ->create([
                    'break_start' => $breakStart->toDateTimeString(),
                    'break_end' => $breakEnd->toDateTimeString(),
                ]);

            $this->pendingAttendances->put($user->id, $pendingAttendance);

            // 承認待ちの勤怠データ作成(status => pending)
            $pendingCorrection = AttendanceCorrectRequest::factory()->for($user)->create([
                'attendance_id' => $pendingAttendance->id,
                'correct_clock_out' => $pendingAttendance->clock_out,
            ]);
            $pendingBreakCorrection = $pendingAttendance->attendanceBreaks->first();
            AttendanceBreakCorrect::create([
                'attendance_correct_request_id' => $pendingCorrection->id,
                'attendance_break_id' => $pendingBreakCorrection->id,
                'correct_break_start' => $pendingBreakCorrection->break_start,
                'correct_break_end' => $pendingBreakCorrection->break_end,
            ]);

            // approved用の修正前の勤怠登録
            $approvedDate = Carbon::now()->addDay(5);
            $approvedAttendance = Attendance::factory()->worked($approvedDate)->for($user)->create();
            $clockInTime = Carbon::parse($approvedAttendance->clock_in);
            $breakStart = $clockInTime->copy()->addHour(4);
            $breakEnd = $breakStart->copy()->addHour(1);

            AttendanceBreak::factory()
                ->forAttendance($approvedAttendance)
                ->create([
                    'break_start' => $breakStart->toDateTimeString(),
                    'break_end' => $breakEnd->toDateTimeString(),
                ]);

            $this->approvedAttendances->put($user->id, $approvedAttendance);

            // 承認済みの勤怠データ作成(status => apprpved)
            $approvedCorrection = AttendanceCorrectRequest::factory()->for($user)->approved()->create([
                'attendance_id' => $approvedAttendance->id,
                'correct_clock_out' => $approvedAttendance->clock_out,
            ]);
            $approvedBreakCorrection = $approvedAttendance->attendanceBreaks->first();
            AttendanceBreakCorrect::create([
                'attendance_correct_request_id' => $approvedCorrection->id,
                'attendance_break_id' => $approvedBreakCorrection->id,
                'correct_break_start' => $approvedBreakCorrection->break_start,
                'correct_break_end' => $approvedBreakCorrection->break_end,
            ]);
        }
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    // 承認待ちの修正申請がすべて表示されているか
    public function test_to_confirm_that_all_amendment_requests_pending_approval_are_displayed()
    {
        $response = $this->get(route('stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee('承認待ち');

        $pendingCorrections = AttendanceCorrectRequest::where('status', 'pending')->where('edited_by_admin', false)->get();
        foreach ($pendingCorrections as $correction) {
            $correction->status_text = AttendanceCorrectRequest::STATUS[$correction->status];

            $response->assertSee($correction->status_text);
            $response->assertSee($correction->user->name);
            $response->assertSee($correction->attendance->work_date->isoFormat('YYYY/MM/DD'));
            $response->assertSee($correction->remarks);
            $response->assertSee($correction->request_date->isoFormat('YYYY/MM/DD'));
        }
    }

    // 承認済みの修正申請がすべて表示されているか
    public function test_to_ensure_that_all_approved_requests_are_displayed()
    {

        $response = $this->get(route('stamp_correction_request.list', ['tab' => 'approved']));

        $response->assertStatus(200);
        $response->assertSee('承認済み');

        $approvedCorrections = AttendanceCorrectRequest::where('status', 'approved')->where('edited_by_admin', false)->get();
        foreach ($approvedCorrections as $correction) {
            $correction->status_text = AttendanceCorrectRequest::STATUS[$correction->status];

            $response->assertSee($correction->status_text);
            $response->assertSee($correction->user->name);
            $response->assertSee($correction->attendance->work_date->isoFormat('YYYY/MM/DD'));
            $response->assertSee($correction->remarks);
            $response->assertSee($correction->request_date->isoFormat('YYYY/MM/DD'));
        }
    }

    // 修正申請の詳細内容が正しく表示されているか
    public function test_to_confirm_that_the_details_of_the_correction_request_are_displayed_correctly()
    {
        $response = $this->get(route('stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee('承認待ち');

        $pendingCorrection = AttendanceCorrectRequest::where('status', 'pending')->where('edited_by_admin', false)->first();
        $pendingBreakCorrection = $pendingCorrection->attendanceBreakCorrects->first();

        $response = $this->get(route('admin.stamp_correction_request.approval', ['attendance_correct_request_id' => $pendingCorrection->id]));

        $response->assertStatus(200);
        $response->assertSee($pendingCorrection->user->name);
        $response->assertSee($pendingCorrection->attendance->work_date->format('Y年'));
        $response->assertSee($pendingCorrection->attendance->work_date->format('m月d日'));
        $response->assertSee($pendingCorrection->correct_clock_in->format('H:i'));
        $response->assertSee($pendingCorrection->correct_clock_out->format('H:i'));
        $response->assertSee($pendingBreakCorrection->correct_break_start->format('H:i'));
        $response->assertSee($pendingBreakCorrection->correct_break_end->format('H:i'));
        $response->assertSee($pendingCorrection->remarks);
    }

    // 修正申請の承認処理が正しく処理されるか
    public function test_to_confirm_that_amendment_request_approvals_are_processed_correctly()
    {
        Carbon::setTestNow(Carbon::create(2026, 1, 17, 21, 0, 0));
        $response = $this->get(route('stamp_correction_request.list'));

        $response->assertStatus(200);
        $response->assertSee('承認待ち');

        $pendingCorrection = AttendanceCorrectRequest::where('status', 'pending')->where('edited_by_admin', false)->first();
        $pendingBreakCorrection = $pendingCorrection->attendanceBreakCorrects->first();

        $response = $this->get(route('admin.stamp_correction_request.approval', ['attendance_correct_request_id' => $pendingCorrection->id]));

        $response->assertStatus(200);
        $response->assertSee('承認');

        $response = $this->post(route('admin.stamp_correction_request.store_approval', ['attendance_correct_request_id' => $pendingCorrection->id]));

        $response->assertRedirect(route('stamp_correction_request.list', ['tab' => 'approved']));
        $response->assertSessionHas('message', '承認しました');

        $this->assertDatabaseHas('attendance_approvals', [
            'admin_id' => $this->admin->id,
            'attendance_correct_request_id' => $pendingCorrection->id,
            'approved_date' => Carbon::now()->toDateString(),
        ]);
        $this->assertDatabaseHas('attendance_correct_requests', [
            'id' => $pendingCorrection->id,
            'status' => 'approved',
        ]);

        $updateAtttendance = $pendingCorrection->attendance->fresh();
        $this->assertEquals($pendingCorrection->correct_clock_in, $updateAtttendance->clock_in);
        $this->assertEquals($pendingCorrection->correct_clock_out, $updateAtttendance->clock_out);

        $updateAtttendanceBreak = $pendingBreakCorrection->attendanceBreak->first();
        $this->assertEquals($pendingBreakCorrection->correct_break_start, $updateAtttendanceBreak->break_start);
        $this->assertEquals($pendingBreakCorrection->correct_break_end, $updateAtttendanceBreak->break_end);
    }
}
