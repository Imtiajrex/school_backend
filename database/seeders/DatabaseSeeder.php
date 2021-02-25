<?php

namespace Database\Seeders;

use AccountBalance;
use App\Models\AboutSchool;
use App\Models\EmployeeAttendanceTime;
use App\Models\Figure;
use App\Models\Religion;
use App\Models\Session;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        if (DB::table('institute_info')->first() == null) {
            $numbers = '01624*******, 01591*******';
            $address = 'Chittagong, Bangladesh';
            DB::table('institute_info')->insert(["institute_name" => "Test School", "institute_motto" => "To Leave A Legacy", "institute_shortform" => "TS", "institute_phonenumbers" => $numbers, "institute_address" => $address, "institute_email" => "imtiajrex@gmail.com", "institute_logo" => "logo.png", "attendance_device" => "1"]);
        }
        $data = [];
        $settings = [["name" => "View Role", "parent_controller" => "Role", "parent_group" => "Settings"], ["name" => "Create Role", "parent_controller" => "Role", "parent_group" => "Settings"], ["name" => "Delete Role", "parent_controller" => "Role", "parent_group" => "Settings"], ["name" => "Update Role", "parent_controller" => "Role", "parent_group" => "Settings"], ["name" => "View User", "parent_controller" => "User", "parent_group" => "Settings"], ["name" => "Create User", "parent_controller" => "User", "parent_group" => "Settings"], ["name" => "Delete user", "parent_controller" => "User", "parent_group" => "Settings"], ["name" => "Update User", "parent_controller" => "User", "parent_group" => "Settings"], ["name" => "View Religion", "parent_controller" => "Religion", "parent_group" => "Settings"], ["name" => "Create Religion", "parent_controller" => "Religion", "parent_group" => "Settings"], ["name" => "Update Religion", "parent_controller" => "Religion", "parent_group" => "Settings"], ["name" => "Delete Religion", "parent_controller" => "Religion", "parent_group" => "Settings"], ["name" => "View Session", "parent_controller" => "Session", "parent_group" => "Settings"], ["name" => "Create Session", "parent_controller" => "Session", "parent_group" => "Settings"], ["name" => "Update Session", "parent_controller" => "Session", "parent_group" => "Settings"], ["name" => "Delete Session", "parent_controller" => "Session", "parent_group" => "Settings"], ["name" => "View Department", "parent_controller" => "Department", "parent_group" => "Settings"], ["name" => "Create Department", "parent_controller" => "Department", "parent_group" => "Settings"], ["name" => "Update Department", "parent_controller" => "Department", "parent_group" => "Settings"], ["name" => "Delete Department", "parent_controller" => "Department", "parent_group" => "Settings"], ["name" => "View GPA", "parent_controller" => "GPA", "parent_group" => "Settings"], ["name" => "Create GPA", "parent_controller" => "GPA", "parent_group" => "Settings"], ["name" => "Update GPA", "parent_controller" => "GPA", "parent_group" => "Settings"], ["name" => "Delete GPA", "parent_controller" => "GPA", "parent_group" => "Settings"], ["name" => "View Grade", "parent_controller" => "Grade", "parent_group" => "Settings"], ["name" => "Create Grade", "parent_controller" => "Grade", "parent_group" => "Settings"], ["name" => "Update Grade", "parent_controller" => "Grade", "parent_group" => "Settings"], ["name" => "Delete Grade", "parent_controller" => "Grade", "parent_group" => "Settings"], ["name" => "View Subject", "parent_controller" => "Subject", "parent_group" => "Settings"], ["name" => "Create Subject", "parent_controller" => "Subject", "parent_group" => "Settings"], ["name" => "Update Subject", "parent_controller" => "Subject", "parent_group" => "Settings"], ["name" => "Delete Subject", "parent_controller" => "Subject", "parent_group" => "Settings"], ["name" => "View Class", "parent_controller" => "Class", "parent_group" => "Settings"], ["name" => "Create Class", "parent_controller" => "Class", "parent_group" => "Settings"], ["name" => "Update Class", "parent_controller" => "Class", "parent_group" => "Settings"], ["name" => "Delete Class", "parent_controller" => "Class", "parent_group" => "Settings"], ["name" => "View PaymentCategory", "parent_controller" => "PaymentCategory", "parent_group" => "Settings"], ["name" => "Create PaymentCategory", "parent_controller" => "PaymentCategory", "parent_group" => "Settings"], ["name" => "Update PaymentCategory", "parent_controller" => "PaymentCategory", "parent_group" => "Settings"], ["name" => "Delete PaymentCategory", "parent_controller" => "PaymentCategory", "parent_group" => "Settings"], ["name" => "View InstituteInfo", "parent_controller" => "InstituteInfo", "parent_group" => "Settings"], ["name" => "Update InstituteInfo", "parent_controller" => "InstituteInfo", "parent_group" => "Settings"], ["name" => "View Permission", "parent_controller" => "Permission", "parent_group" => "Settings"], ["name" => "View Assigned Subject", "parent_controller" => "Subject Assignment", "parent_group" => "Settings"], ["name" => "Assign Subject", "parent_controller" => "Subject Assignment", "parent_group" => "Settings"], ["name" => "Delete Assigned Subject", "parent_controller" => "Subject Assignment", "parent_group" => "Settings"], ["name" => "View Assigned Department", "parent_controller" => "Department Assignment", "parent_group" => "Settings"], ["name" => "Assign Department", "parent_controller" => "Department Assignment", "parent_group" => "Settings"], ["name" => "Assign Department", "parent_controller" => "Department Assignment", "parent_group" => "Settings"], ["name" => "View Students Extended Info", "parent_controller" => "Students Extended Info", "parent_group" => "Settings"], ["name" => "Create Students Extended Info", "parent_controller" => "Students Extended Info", "parent_group" => "Settings"], ["name" => "Delete Students Extended Info", "parent_controller" => "Students Extended Info", "parent_group" => "Settings"], ["name" => "Update Students Extended Info", "parent_controller" => "Students Extended Info", "parent_group" => "Settings"], ["name" => "View Teachers Extended Info", "parent_controller" => "Teachers Extended Info", "parent_group" => "Settings"], ["name" => "Create Teachers Extended Info", "parent_controller" => "Teachers Extended Info", "parent_group" => "Settings"], ["name" => "Delete Teachers Extended Info", "parent_controller" => "Teachers Extended Info", "parent_group" => "Settings"], ["name" => "Update Teachers Extended Info", "parent_controller" => "Teachers Extended Info", "parent_group" => "Settings"]];


        $students = [["name" => "View Students", "parent_controller" => "Students", "parent_group" => "Students"], ["name" => "Create Students", "parent_controller" => "Students", "parent_group" => "Students"], ["name" => "Delete Students", "parent_controller" => "Students", "parent_group" => "Students"], ["name" => "Update Students", "parent_controller" => "Students", "parent_group" => "Students"], ["name" => "View ClassHasStudents", "parent_controller" => "ClassHasStudents", "parent_group" => "Students"], ["name" => "Create ClassHasStudents", "parent_controller" => "ClassHasStudents", "parent_group" => "Students"], ["name" => "Delete ClassHasStudents", "parent_controller" => "ClassHasStudents", "parent_group" => "Students"], ["name" => "Update ClassHasStudents", "parent_controller" => "ClassHasStudents", "parent_group" => "Students"], ["name" => "View Student Payment Info", "parent_controller" => "Student Payment Info", "parent_group" => "Students"], ["name" => "Delete Student Payment Info", "parent_controller" => "Student Payment Info", "parent_group" => "Students"], ["name" => "Assign Student Payment Info", "parent_controller" => "Student Payment Info", "parent_group" => "Students"], ["name" => "View Student Attendance", "parent_controller" => "Student Attendance", "parent_group" => "Students"], ["name" => "Delete Student Attendance", "parent_controller" => "Student Attendance", "parent_group" => "Students"], ["name" => "Assign Student Attendance", "parent_controller" => "Student Attendance", "parent_group" => "Students"]];

        $employees = [["name" => "View Employees", "parent_controller" => "Employees", "parent_group" => "Employees"], ["name" => "Create Employees", "parent_controller" => "Employees", "parent_group" => "Employees"], ["name" => "Delete Employees", "parent_controller" => "Employees", "parent_group" => "Employees"], ["name" => "Update Employees", "parent_controller" => "Employees", "parent_group" => "Employees"], ["name" => "View Employee Attendance", "parent_controller" => "Employee Attendance", "parent_group" => "Employees"], ["name" => "Delete Employee Attendance", "parent_controller" => "Employee Attendance", "parent_group" => "Employees"], ["name" => "Assign Employee Attendance", "parent_controller" => "Employee Attendance", "parent_group" => "Employees"], ["name" => "View Employee Post", "parent_controller" => "Employees", "parent_group" => "Employees"], ["name" => "Create Employee Post", "parent_controller" => "Employees", "parent_group" => "Employees"], ["name" => "Delete Employee Post", "parent_controller" => "Employees", "parent_group" => "Employees"], ["name" => "Update Employee Post", "parent_controller" => "Employees", "parent_group" => "Employees"], ["name" => "View Employee Type", "parent_controller" => "Employees", "parent_group" => "Employees"], ["name" => "Create Employee Type", "parent_controller" => "Employees", "parent_group" => "Employees"], ["name" => "Delete Employee Type", "parent_controller" => "Employees", "parent_group" => "Employees"], ["name" => "Update Employee Type", "parent_controller" => "Employees", "parent_group" => "Employees"],];

        $website_settings = [["name" => "Create Album", "parent_controller" => "Album", "parent_group" => "Website Settings"], ["name" => "Delete Album", "parent_controller" => "Album", "parent_group" => "Website Settings"], ["name" => "Update Album", "parent_controller" => "Album", "parent_group" => "Website Settings"], ["name" => "Upload Image", "parent_controller" => "Image", "parent_group" => "Website Settings"], ["name" => "Update Image", "parent_controller" => "Image", "parent_group" => "Website Settings"], ["name" => "Delete Image", "parent_controller" => "Image", "parent_group" => "Website Settings"], ["name" => "Create Notifications", "parent_controller" => "Notifications", "parent_group" => "Website Settings"], ["name" => "Delete Notifications", "parent_controller" => "Notifications", "parent_group" => "Website Settings"], ["name" => "Update Notifications", "parent_controller" => "Notifications", "parent_group" => "Website Settings"], ["name" => "Create Notifications", "parent_controller" => "Notifications", "parent_group" => "Website Settings"], ["name" => "Delete Notifications", "parent_controller" => "Notifications", "parent_group" => "Website Settings"], ["name" => "Update Notifications", "parent_controller" => "Notifications", "parent_group" => "Website Settings"], ["name" => "Create Pages", "parent_controller" => "Pages", "parent_group" => "Website Settings"], ["name" => "Delete Pages", "parent_controller" => "Pages", "parent_group" => "Website Settings"], ["name" => "Update Pages", "parent_controller" => "Pages", "parent_group" => "Website Settings"], ["name" => "Create HomePage", "parent_controller" => "HomePage", "parent_group" => "Website Settings"], ["name" => "Delete HomePage", "parent_controller" => "HomePage", "parent_group" => "Website Settings"], ["name" => "Update HomePage", "parent_controller" => "HomePage", "parent_group" => "Website Settings"]];

        $products = [["name" => "View Products", "parent_controller" => "Products", "parent_group" => "Products"], ["name" => "Create Products", "parent_controller" => "Products", "parent_group" => "Products"], ["name" => "Delete Products", "parent_controller" => "Products", "parent_group" => "Products"], ["name" => "Update Products", "parent_controller" => "Products", "parent_group" => "Products"], ["name" => "View ProductsSold", "parent_controller" => "ProductsSold", "parent_group" => "Products"], ["name" => "Create ProductsSold", "parent_controller" => "ProductsSold", "parent_group" => "Products"], ["name" => "Delete ProductsSold", "parent_controller" => "ProductsSold", "parent_group" => "Products"], ["name" => "Update ProductsSold", "parent_controller" => "ProductsSold", "parent_group" => "Products"]];


        $results = [["name" => "View Result", "parent_controller" => "Result", "parent_group" => "Result"], ["name" => "Create Result", "parent_controller" => "Result", "parent_group" => "Result"], ["name" => "Delete Result", "parent_controller" => "Result", "parent_group" => "Result"], ["name" => "Update Result", "parent_controller" => "Result", "parent_group" => "Result"], ["name" => "View Result Publication Status", "parent_controller" => "Result Publication Status", "parent_group" => "Result"], ["name" => "Create Result Publication Status", "parent_controller" => "Result Publication Status", "parent_group" => "Result"], ["name" => "Delete Result Publication Status", "parent_controller" => "Result Publication Status", "parent_group" => "Result"], ["name" => "Update Result Publication Status", "parent_controller" => "Result Publication Status", "parent_group" => "Result"]];

        $messages = [["name" => "View Employee Message", "parent_controller" => "Employee Message", "parent_group" => "Messages"], ["name" => "Create Employee Message", "parent_controller" => "Employee Message", "parent_group" => "Messages"], ["name" => "Delete Employee Message", "parent_controller" => "Employee Message", "parent_group" => "Messages"], ["name" => "Update Employee Message", "parent_controller" => "Employee Message", "parent_group" => "Messages"], ["name" => "View Student Message", "parent_controller" => "Student Message", "parent_group" => "Messages"], ["name" => "Create Student Message", "parent_controller" => "Student Message", "parent_group" => "Messages"], ["name" => "Delete Student Message", "parent_controller" => "Student Message", "parent_group" => "Messages"], ["name" => "Update Student Message", "parent_controller" => "Student Message", "parent_group" => "Messages"], ["name" => "Send SMS", "parent_controller" => "SMS", "parent_group" => "Messages"]];

        $books = [["name" => "View Books", "parent_controller" => "Books", "parent_group" => "Library"], ["name" => "Create Books", "parent_controller" => "Books", "parent_group" => "Library"], ["name" => "Delete Books", "parent_controller" => "Books", "parent_group" => "Library"], ["name" => "Update Books", "parent_controller" => "Books", "parent_group" => "Library"], ["name" => "View BooksSold", "parent_controller" => "BooksSold", "parent_group" => "Library"], ["name" => "Create BooksSold", "parent_controller" => "BooksSold", "parent_group" => "Library"], ["name" => "Delete BooksSold", "parent_controller" => "BooksSold", "parent_group" => "Library"], ["name" => "Update BooksSold", "parent_controller" => "BooksSold", "parent_group" => "Library"], ["name" => "View Book Category", "parent_controller" => "Book Category", "parent_group" => "Library"], ["name" => "Create Book Category", "parent_controller" => "Book Category", "parent_group" => "Library"], ["name" => "Delete Book Category", "parent_controller" => "Book Category", "parent_group" => "Library"], ["name" => "Update Book Category", "parent_controller" => "Book Category", "parent_group" => "Library"], ["name" => "View BooksIssued", "parent_controller" => "BooksIssued", "parent_group" => "Library"], ["name" => "Create BooksIssued", "parent_controller" => "BooksIssued", "parent_group" => "Library"], ["name" => "Delete BooksIssued", "parent_controller" => "BooksIssued", "parent_group" => "Library"], ["name" => "Update BooksIssued", "parent_controller" => "BooksIssued", "parent_group" => "Library"]];


        $exams = [["name" => "View Exam", "parent_controller" => "Exam", "parent_group" => "Exams"], ["name" => "Create Exam", "parent_controller" => "Exam", "parent_group" => "Exams"], ["name" => "Delete Exam", "parent_controller" => "Exam", "parent_group" => "Exams"], ["name" => "Update Exam", "parent_controller" => "Exam", "parent_group" => "Exams"], ["name" => "View Marks", "parent_controller" => "Marks", "parent_group" => "Exams"], ["name" => "Create Marks", "parent_controller" => "Marks", "parent_group" => "Exams"], ["name" => "Delete Marks", "parent_controller" => "Marks", "parent_group" => "Exams"], ["name" => "Update Marks", "parent_controller" => "Marks", "parent_group" => "Exams"]];

        $payments = [["name" => "View Payment", "parent_controller" => "Payment", "parent_group" => "Payments"], ["name" => "Create Payment", "parent_controller" => "Payment", "parent_group" => "Payments"], ["name" => "Delete Payment", "parent_controller" => "Payment", "parent_group" => "Payments"], ["name" => "Update Payment", "parent_controller" => "Payment", "parent_group" => "Payments"], ["name" => "View Payment Receipt", "parent_controller" => "Payment Receipt", "parent_group" => "Payments"], ["name" => "Create Payment Receipt", "parent_controller" => "Payment Receipt", "parent_group" => "Payments"], ["name" => "Delete Payment Receipt", "parent_controller" => "Payment Receipt", "parent_group" => "Payments"], ["name" => "Update Payment Receipt", "parent_controller" => "Payment Receipt", "parent_group" => "Payments"], ["name" => "Re", "parent_controller" => "Due", "parent_group" => "Payments"], ["name" => "Pay Due Record", "parent_controller" => "Due", "parent_group" => "Payments"], ["name" => "Update Due Record", "parent_controller" => "Due", "parent_group" => "Payments"], ["name" => "Delete Due Record", "parent_controller" => "Due", "parent_group" => "Payments"],];

        $accounts = [["name" => "View Accounts", "parent_controller" => "Accounts", "parent_group" => "Accounts"], ["name" => "Create Accounts", "parent_controller" => "Accounts", "parent_group" => "Accounts"], ["name" => "Delete Accounts", "parent_controller" => "Accounts", "parent_group" => "Accounts"], ["name" => "Update Accounts", "parent_controller" => "Accounts", "parent_group" => "Accounts"], ["name" => "View Account Balance", "parent_controller" => "Accounts", "parent_group" => "Accounts"], ["name" => "Edit Account Balance", "parent_controller" => "Accounts", "parent_group" => "Accounts"],];

        array_push($data, ...$settings);
        array_push($data, ...$students);
        array_push($data, ...$employees);
        array_push($data, ...$website_settings);
        array_push($data, ...$products);
        array_push($data, ...$results);
        array_push($data, ...$messages);
        array_push($data, ...$books);
        array_push($data, ...$exams);
        array_push($data, ...$payments);
        array_push($data, ...$accounts);


        foreach ($data as $permission) {
            if (Permission::where("name", $permission["name"])->first() != null) {
                continue;
            }
            Permission::create([
                "name" => $permission["name"],
                "parent_controller" => $permission["parent_controller"],
                "parent_group" => $permission["parent_group"],
                "guard_name" => "web"
            ]);
        }

        if (Role::where("name", 'Super Admin')->first() == null) {
            Role::create(["name" => "Super Admin"]);
        }

        if (User::where("username", 'imtiajrex')->first() == null) {
            User::create([
                "name" => "Imtiaj",
                "username" => "imtiajrex",
                "user_type" => "admin",
                "password" => Hash::make(123456)
            ]);
            $user = User::where("username", 'imtiajrex')->first();
            $user->assignRole("Super Admin");
        }
        if (EmployeeAttendanceTime::count() == 0) {
            EmployeeAttendanceTime::insert(["start_time" => "08:00:00", "end_time" => "14:00:00"]);
        }
        if (AboutSchool::count() == 0) {
            AboutSchool::insert(["image" => "", "title" => "About School", "content" => "Lorem Ipsum Dolores!"]);
        }
        if (Figure::count() == 0) {
            Figure::insert(["students" => "0", "teachers" => "0", "result" => "0", "parent_satisfaction" => "0"]);
        }
        if (DB::table('account_balance')->count() == 0) {
            DB::table('account_balance')->insert(["cash" => 0, "bank" => 0]);
        }
        if (DB::table('sms_account')->count() == 0) {
            DB::table('sms_account')->insert(["balance" => 0, "rate" => 0, "total_sent_sms" => 0]);
        }
        if (Religion::count() == 0) {
            Religion::insert([["religion_name" => "Islam"], ["religion_name" => "Hinduism"], ["religion_name" => "Christianity"], ["religion_name" => "Buddhism"]]);
        }
        if (Session::count() == 0) {
            Session::insert(["session" => date('Y')]);
        }
        $in_sms_template = "{{student_name}} entered school at {{access_time}}.\n{{institute_name}}";
        $out_sms_template = "{{student_name}} left school at {{access_time}}.\n{{institute_name}}";
        if (DB::table('sms_template')->count() == 0) {
            DB::table('sms_template')->insert([["title" => "student_attendance_in", "template" => $in_sms_template], ["title" => "student_attendance_out", "template" => $out_sms_template]]);
        }
        if (DB::table("employee_types")->count() == 0) {
            DB::table("employee_types")->insert([["employee_type" => "Teacher"], ["employee_type" => "Staff"], ["employee_type" => "Administrator"]]);
        }
    }
}
