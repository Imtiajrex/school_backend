<?php

namespace App\Console\Commands;

use App\Jobs\SendSMSJob;
use App\Models\ClassHasStudents;
use App\Models\Employee;
use App\Models\EmployeeAttendance;
use App\Models\StudentsAttendance;
use Carbon\Carbon;
use DateTime;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use PDO;

class FetchAttendance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'attendance:fetch';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch Attendance From Stellar API';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $school_info = DB::table("institute_info")->first();
        $employee_prefix = "T";
        $student_prefix = "STD";
        $prefix = $school_info->institute_shortform;
        $sms_template = DB::table("sms_template")->where("title", "student_attendance_in")->first();
        $default_sms = "{{student_name}} entered school at {{access_time}}.\n{{institute_name}}";
        $student_attendance_in = $sms_template ? $sms_template->template : $default_sms;

        $sms_template = DB::table("sms_template")->where("title", "student_attendance_out")->first();
        $default_sms = "{{student_name}} left school at {{access_time}}.\n{{institute_name}}";
        $student_attendance_out = $sms_template ? $sms_template->template : $default_sms;

        $data = array(
            "operation" => "fetch_log",
            "auth_user" =>  env("ATTENDANCE_DEVICE_USER"),
            "auth_code" => env("ATTENDANCE_DEVICE_TOKEN"),
            "device_id" =>  $school_info->attendance_device,
            "start_date" => date('Y-m-d'),
            "end_date" => date('Y-m-d'),
            "start_time" => "00:00:00",
            "end_time" => "23:59:59"
        );



        $datapayload = json_encode($data);
        $api_request = curl_init(env("ATTENDANCE_DEVICE_URL"));
        curl_setopt($api_request, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($api_request, CURLINFO_HEADER_OUT, true);
        curl_setopt($api_request, CURLOPT_POST, true);
        curl_setopt($api_request, CURLOPT_POSTFIELDS, $datapayload);
        curl_setopt($api_request, CURLOPT_HTTPHEADER, array('Content-Type:application/json', 'Content-Length: ' . strlen($datapayload)));
        $result = curl_exec($api_request);
        $replace_syntax = str_replace('{"log":', "", $result);

        $replace_syntax = str_replace('}]}', "}]", $replace_syntax);
        $replace_syntax = str_replace(']}', "]", $replace_syntax);

        $result_arr = json_decode($replace_syntax, true);
        $employee_ids = [];
        $student_ids = [];
        $employee_attendance_time = [];
        $student_attendance_time = [];
        if ($result_arr != null) {
            foreach ($result_arr as $data) {
                $id = str_replace($prefix, "", $data["registration_id"]);
                if (str_contains($id, $employee_prefix)) {

                    array_push($employee_ids, $id);
                    $employee_attendance_time[$id] = ["access_time" => $data["access_time"], "date" => $data["access_date"]];
                } else {
                    array_push($student_ids, $id);
                    $student_attendance_time[$id] = ["access_time" => $data["access_time"], "date" => $data["access_date"]];
                }
            }

            $employee_attendance = [];
            $student_attendance = [];
            $sms_data = [];

            if (count($employee_ids) > 0) {
                $employees = Db::table('employee')->whereIn("employee.employee_id", $employee_ids)->leftJoin('employee_attendance', function ($join) {
                    $join->on('employee_attendance.employee_id', '=', 'employee.id')->whereBetween("date", [date('Y-m-d'), date('Y-m-d')]);
                })->groupBy("employee_attendance.employee_id")->selectRaw('employee.id,employee.employee_id, max(access_time) as access_time')->get();


                foreach ($employees as $employee) {
                    $attendance_time = $employee_attendance_time[$employee->employee_id]["access_time"];
                    $attendance_time = explode(":", $attendance_time);
                    $hour = $attendance_time[0] < 10 ? "0" . $attendance_time[0] : $attendance_time[0];
                    $attendance_time = $hour . ":" . $attendance_time[1] . ":" . $attendance_time[2];

                    if ($employee->access_time == null) {
                        array_push($employee_attendance, [
                            "employee_id" => $employee->id,
                            "access_time" => $attendance_time,
                            "date" => $employee_attendance_time[$employee->employee_id]["date"]
                        ]);
                    } else {
                        $time1 = new DateTime(date('Y-m-d') . " " . $employee->access_time);
                        $time2 = new DateTime(date("Y-m-d") . " " . $attendance_time);
                        $interval = $time1->diff($time2);
                        if ($interval->invert == 0 && $interval->h > 0) {
                            array_push($employee_attendance, [
                                "employee_id" => $employee->id,
                                "access_time" => $attendance_time,
                                "date" => $employee_attendance_time[$employee->employee_id]["date"]
                            ]);
                        }
                    }
                }

                if (count($employee_attendance) > 0) {
                    EmployeeAttendance::insert($employee_attendance);
                }
            } else {
                $students = ClassHasStudents::whereIn("student_identifier", $student_ids)->leftJoin("students_attendance", function ($join) {
                    $join->on("students_attendance.student_id", "=", "class_has_students.id")->whereBetween("date", [date('Y-m-d'), date('Y-m-d')]);
                })->leftJoin("students", function ($join) {
                    $join->on("students.id", "=", "class_has_students.student_id");
                })->groupBy("students_attendance.student_id")->selectRaw('count(students_attendance.student_id) as attendance_count,class_has_students.id,class_has_students.student_identifier,max(students_attendance.access_time) as access_time,students.primary_phone,students.student_name')->get();

                foreach ($students as $student) {
                    if ($student_attendance_time[$student->student_identifier] != null) {

                        $attendance_time = $student_attendance_time[$student->student_identifier]["access_time"];
                        $attendance_time = explode(":", $attendance_time);
                        $hour = $attendance_time[0] < 10 ? "0" . $attendance_time[0] : $attendance_time[0];
                        $attendance_time = $hour . ":" . $attendance_time[1] . ":" . $attendance_time[2];

                        if ($student->access_time == null) {
                            $message = str_replace("{{student_name}}", $student->student_name, $student_attendance_in);
                            $message = str_replace("{{access_time}}", $attendance_time, $message);
                            $message = str_replace("{{institute_name}}", $school_info->institute_name, $message);
                            $message = rawurlencode($message);

                            array_push($sms_data, ["to" => $student->primary_phone, "message" => $message]);
                            array_push($student_attendance, [
                                "student_id" => $student->id,
                                "access_time" => $attendance_time,
                                "date" => $student_attendance_time[$student->student_identifier]["date"]
                            ]);
                        } else {
                            $time1 = new DateTime(date('Y-m-d') . " " . $student->access_time);
                            $time2 = new DateTime(date("Y-m-d") . " " . $attendance_time);
                            $interval = $time1->diff($time2);
                            if ($interval->invert == 0 && $interval->h > 0) {
                                array_push($student_attendance, [
                                    "student_id" => $student->id,
                                    "access_time" => $attendance_time,
                                    "date" => $student_attendance_time[$student->student_identifier]["date"]
                                ]);
                                if ($student->attendance_count < 2) {

                                    $message = str_replace("{{student_name}}", $student->student_name, $student_attendance_out);
                                    $message = str_replace("{{access_time}}", $attendance_time, $message);
                                    $message = str_replace("{{institute_name}}", $school_info->institute_name, $message);
                                    $message = rawurlencode($message);

                                    array_push($sms_data, ["to" => $student->primary_phone, "message" => $message]);
                                }
                            }
                        }
                    }
                }
                if (count($student_attendance) > 0) {
                    StudentsAttendance::insert($student_attendance);
                }
            }
            if (count($sms_data) > 0)
                SendSMSJob::dispatch($sms_data)->delay(now()->addSeconds(5));
        }
    }
}
