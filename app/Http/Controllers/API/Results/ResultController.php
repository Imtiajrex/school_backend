<?php

namespace App\Http\Controllers\API\Results;

use App\Http\Controllers\Controller;
use App\Http\Controllers\ResponseMessage;
use App\Models\ClassHasStudents;
use App\Models\Department;
use App\Models\Exam;
use App\Models\Marks;
use App\Models\ResultHasExam;
use App\Models\Results;
use App\Models\SchoolClass;
use App\Models\Session;
use App\Models\StudentResultReport;
use App\Models\Students;
use App\Models\Subjects;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ResultController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $permission = "View Result";
        if ($user->can($permission) || ($user->user_type == "student" && $user->username == $request->student_identifier)) {
            if ($request->student_identifier && $request->student) {
                $student_id = ClassHasStudents::where("student_identifier", $request->student_identifier)->first();
                $student_id = $student_id ?? $student_id->id;

                return StudentResultReport::where(["student_id" => $student_id, "result_status" => true])->leftJoin("results", "results.id", "=", "student_result_report.result_id")->get(["result_name", "results.id", "student_id"]);
            }
            $results = [];
            $query = [];
            if ($request->class_id != null && $request->department_id != null && $request->session_id != null)
                $results = Results::where(["results.class_id" => $request->class_id, "results.department_id" => $request->department_id, "results.session_id" => $request->session_id]);
            else if ($request->session_id != null)
                $query["results.session_id"] =  $request->session_id;
            else if ($request->class_id != null)
                $query["results.class_id"] =  $request->class_id;

            if ($query > 0 && $results == []) {
                $results = Results::where($query);
            }
            if ($request->result) {
                return $results->get(["results.result_name as text", "results.id as value", "results.class_id", "results.session_id", "results.department_id"]);
            }
            if ($results != []) {

                $results = $results->leftJoin('class', "class.id", '=', 'results.class_id');
                $results = $results->leftJoin('department', "department.id", '=', 'results.department_id');
                $results = $results->leftJoin('session', "session.id", '=', 'results.session_id');


                $results = $results->get(['results.*', 'session.session', 'department.name as department', 'class.name as class']);
            }
            foreach ($results as $result) {
                $exams = ResultHasExam::where("result_id", $result->id)->leftJoin("exam", "exam.id", "=", 'result_has_exam.exam_id')->get(['exam.exam_name', 'result_has_exam.exam_percentage']);
                $result["exams"] = "";
                foreach ($exams as $exam) {
                    $result['exams'] = $result['exams'] . "Exam: " . $exam->exam_name . " | Percentage: " . $exam->exam_percentage . '
';
                }
            }
            return $results;
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function getResultExams(Request $request)
    {
        $user = $request->user();
        $permission = "View Result";
        if ($user->can($permission) || ($user->user_type == "student" && $user->username == $request->student_identifier)) {
            $request->validate([
                "result_id" => "required|numeric"
            ]);
            return ResultHasExam::where("result_id", $request->result_id)->get();
        } else {
            return ResponseMessage::unauthorized($permission);
        }
    }

    public function getResult(Request $request)
    {
        $user = $request->user();
        $permission = "View Result";
        if ($user->can($permission) || ($user->user_type == "student" && $user->username == $request->student_id)) {

            if ($request->result_id != null) {

                $results = StudentResultReport::where("result_id", $request->result_id);
                if ($request->published) {
                    $results = $results->where("result_status", 1);
                }
                $results = $results->leftJoin("results", "results.id", "=", "student_result_report.result_id")->leftJoin("class", "class.id", "=", "results.class_id")->leftJoin("department", "department.id", "=", "results.department_id")->leftJoin("session", "session.id", "=", "results.session_id")->leftJoin("class_has_students", function ($join) {
                    $join->on("class_has_students.id", '=', "student_result_report.student_id");
                    $join->on("class_has_students.session_id", '=', "results.session_id");
                    $join->on("class_has_students.class_id", '=', "results.class_id");
                    $join->on("class_has_students.department_id", '=', "results.department_id");
                })->leftJoin("students", "students.id", "=", "class_has_students.student_id");
                $results = $results->orderBy("class_has_students.role", "asc")->get(["results.result_name", "student_result_report.student_id", "students.student_name", "class_has_students.student_identifier", "class.name as class", "department.name as department", "session.session", "student_image", "class_has_students.role"]);
            } else {
                return [];
            }
            foreach ($results as $result) {
                $result["exams"] = ResultHasExam::where("result_id", $request->result_id)->leftJoin("exam", "exam.id", '=', 'result_has_exam.exam_id')->get();
                $exam_ids = [];
                foreach ($result["exams"] as $exam) {
                    $exam["subjects"] = Subjects::whereIn("id", json_decode($exam["subjects"]))->get();
                    foreach ($exam["subjects"] as $subject) {
                        $subject["marks_structure"] = DB::table("marks_structure")->where("exam_id", $exam->id)->where("subject_id", $subject->id)->first();
                    }
                    array_push($exam_ids, $exam->id);
                }
                $result["student_marks"] = Marks::whereIn("exam_id", $exam_ids)->where(["student_id" => $result->student_id])->groupBy(['student_id', 'subject_id'])->get();;
            }

            return $results;
        }
    }

    public function store(Request $request)
    {
        $user = $request->user();
        $permission = "Create Result";
        if ($user->can($permission)) {
            $request->validate([
                "result_name" => "required|string",
                "exams" => "required",
                "class_id" => "required|numeric",
                "department_id" => "required|numeric",
                "session_id" => "required|numeric",
            ]);

            if (SchoolClass::find($request->class_id) == null)
                return ResponseMessage::fail("Class Doesn't Exist!");

            if (Department::find($request->department_id) == null)
                return ResponseMessage::fail("Department Doesn't Exist!");

            if (Session::find($request->session_id) == null)
                return ResponseMessage::fail("Session Doesn't Exist!");




            $result = new Results;
            $result->result_name = $request->result_name;
            $result->class_id = $request->class_id;
            $result->department_id = $request->department_id;
            $result->session_id = $request->session_id;

            if ($result->save()) {
                $exams = $request->exams;
                $result_has_exams = [];
                foreach ($exams as $exam) {
                    if ($exam["exam_id"] == null || $exam["exam_percentage"] == null)
                        return ResponseMessage::fail("Not Valid Exam Data");
                    $exam_query = Exam::find($exam["exam_id"]);
                    if ($exam_query == null) {
                        if ($exam_query->class_id != $request->class_id && $exam_query->department_id != $request->department_id && $exam_query->session_id != $request->session_id)
                            return ResponseMessage::fail("Some Exams Don't Exist!");
                    }
                    array_push($result_has_exams, ["result_id" => $result->id, "exam_id" => $exam["exam_id"], "exam_percentage" => $exam["exam_percentage"]]);
                }

                $students = ClassHasStudents::select('id')->where(["session_id" => $request->session_id, "class_id" => $request->class_id, "department_id" => $request->department_id])->get();
                $student_result_report = [];
                foreach ($students as $student) {
                    array_push($student_result_report, ["result_id" => $result->id, "student_id" => $student->id, "result_status" => 0, "result_remarks" => ""]);
                }
                if (ResultHasExam::insert($result_has_exams)) {
                    if (StudentResultReport::insert($student_result_report))
                        return ResponseMessage::success("Result Added Successfully!");
                    else
                        return ResponseMessage::success("Failed To Add Student Result Report!");
                } else
                    return ResponseMessage::success("Failed To Add Exam To Result!");
            } else {
                return ResponseMessage::fail("Failed To Add Result!");
            }
        }
    }


    public function destroy($id, Request $request)
    {
        $user = $request->user();
        $permission = "Delete Result";
        if ($user->can($permission)) {
            $result = Results::find($id);
            if ($result != null) {
                if ($result->delete()) {
                    return ResponseMessage::success("Result Deleted!");
                } else {
                    return ResponseMessage::fail("Couldn't Delete Result!");
                }
            } else {
                return ResponseMessage::fail("Result Doesn't Exist!");
            }
        } else {
            ResponseMessage::unauthorized($permission);
        }
    }
}
