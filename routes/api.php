<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\API\Settings\UserController;
use App\Http\Controllers\API\Settings\DepartmentController;
use App\Http\Controllers\API\Settings\GpaController;
use App\Http\Controllers\API\Settings\GradeController;
use App\Http\Controllers\API\Settings\InstituteInfoController;
use App\Http\Controllers\API\Settings\PaymentCategoryController;
use App\Http\Controllers\API\Settings\SubjectController;
use App\Http\Controllers\API\Settings\PermissionController;
use App\Http\Controllers\API\Settings\RoleController;
use App\Http\Controllers\API\Settings\SessionController;
use App\Http\Controllers\API\Settings\ClassHasSubjectsController;
use App\Http\Controllers\API\Settings\ReligionController;
use App\Http\Controllers\API\Settings\SchoolClassController;
use App\Http\Controllers\API\Settings\StudentsExtendedInfoController;
use App\Http\Controllers\API\Settings\TeachersExtendedInfoController;

use App\Http\Controllers\API\Students\StudentController;
use App\Http\Controllers\API\Students\StudentAssignmentController;
use App\Http\Controllers\API\Students\StudentPaymentInfoController;
use App\Http\Controllers\API\Students\StudentsAttendanceController;

use App\Http\Controllers\API\Employee\EmployeeController;
use App\Http\Controllers\API\Employee\EmployeeAttendanceController;
use App\Http\Controllers\API\Employee\EmployeePostController;
use App\Http\Controllers\API\Employee\EmployeeTypeController;

use App\Http\Controllers\API\Exams\ExamController;
use App\Http\Controllers\API\Exams\MarksController;

use App\Http\Controllers\API\Library\BooksController;
use App\Http\Controllers\API\Library\IssuedBooksController;
use App\Http\Controllers\API\Library\SellBooksController;

use App\Http\Controllers\API\Accounts\AccountsController;
use App\Http\Controllers\API\Library\BooksCategoryController;
use App\Http\Controllers\API\Messages\EmployeeMessageController;
use App\Http\Controllers\API\Messages\SMSController;
use App\Http\Controllers\API\Messages\StudentMessageController;
use App\Http\Controllers\API\Products\ProductsController;
use App\Http\Controllers\API\Products\SellProductsController;

use App\Http\Controllers\API\Results\ResultController;
use App\Http\Controllers\API\Results\ResultPublishingController;

use App\Http\Controllers\API\Payments\StudentsPaymentController;
use App\Http\Controllers\API\Payments\StudentsPaymentReceiptController;
use App\Http\Controllers\API\Payments\DueController;
use App\Http\Controllers\API\Settings\EmployeeAttendanceTimeController;
use App\Http\Controllers\API\Settings\StudentsAttendanceTimeController;
use App\Http\Controllers\API\Settings\WeekdaysController;
use App\Http\Controllers\API\WebsiteSettings\AboutSchoolController;
use App\Http\Controllers\API\WebsiteSettings\AlbumController;
use App\Http\Controllers\API\WebsiteSettings\FigureController;
use App\Http\Controllers\API\WebsiteSettings\GalleryController;
use App\Http\Controllers\API\WebsiteSettings\HomepageController;
use App\Http\Controllers\API\WebsiteSettings\NotificationController;
use App\Http\Controllers\API\WebsiteSettings\PageController;
use App\Http\Controllers\API\WebsiteSettings\SchoolSpecialtyController;
use App\Http\Controllers\API\WebsiteSettings\SlideshowController;
use App\Http\Controllers\API\WebsiteSettings\SubPageController;
use App\Http\Controllers\API\WebsiteSettings\TestimonialController;
use App\Models\StudentAttendanceTime;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('login', [UserController::class, 'login']);

Route::get("homepage/institute_info",[InstituteInfoController::class,"index"]);
Route::get("homepage/pages",[PageController::class,"index"]);
Route::get("homepage/sub_pages",[SubPageController::class,"index"]);
Route::get("homepage/slideshow",[SlideshowController::class,"index"]);
Route::get("homepage/homepage",[HomepageController::class,"index"]);
Route::get("homepage/notifications",[NotificationController::class,"index"]);

Route::get("homepage/testimonial",[TestimonialController::class,"index"]);
Route::get("homepage/school_specialty",[SchoolSpecialtyController::class,"index"]);
Route::get("homepage/about_school",[AboutSchoolController::class,"index"]);
Route::get("homepage/employee",[EmployeeController::class,"index"]);
Route::get("homepage/figure",[FigureController::class,"index"]);
Route::get("homepage/albums",[AlbumController::class,"index"]);
Route::get("homepage/gallery",[GalleryController::class,"index"]);


Route::middleware("auth:sanctum")->group(function () {
    Route::get('logout', [UserController::class, 'logout']);

    Route::prefix('settings')->group(function () {

        Route::resource('user', UserController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('class', SchoolClassController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('department', DepartmentController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('gpa', GpaController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('grade', GradeController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('institute_info', InstituteInfoController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('payment_category', PaymentCategoryController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('religion', ReligionController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('subject', SubjectController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('weekdays', WeekdaysController::class)->only([
            'index', 'store', 'destroy'
        ]);
        Route::resource('session', SessionController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('role', RoleController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::get('permission', [PermissionController::class, "index"]);

        Route::get('assign_subject', [ClassHasSubjectsController::class, "index"]);
        Route::post('assign_subject', [ClassHasSubjectsController::class, "assign"]);
        Route::delete('assign_subject/{subject_id}', [ClassHasSubjectsController::class, "destroy"]);

        Route::resource("students_extended_info", StudentsExtendedInfoController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource("employees_extended_info", TeachersExtendedInfoController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource("student_attendance_time", StudentsAttendanceTimeController::class)->only([
            'index', 'store', 'destroy'
        ]);
        Route::resource("employee_attendance_time", EmployeeAttendanceTimeController::class)->only([
            'index', 'update'
        ]);
    });


    Route::prefix('students')->group(function () {

        Route::resource('student', StudentController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);

        Route::resource('student_assignment', StudentAssignmentController::class)->only([
            'index', 'store','update', 'destroy'
        ]);

        Route::resource('assign_fees', StudentPaymentInfoController::class)->only([
            'index', 'store', 'destroy'
        ]);
        Route::resource('student_attendance', StudentsAttendanceController::class)->only([
            'index', 'store', 'destroy'
        ]);
        Route::get('mark_attendance', [StudentsAttendanceController::class,'getManualAttendance']);
        Route::post('mark_attendance', [StudentsAttendanceController::class,'markAttendance']);
        Route::get("student_monthly_attendance", [StudentsAttendanceController::class, "getMonthlyAttendance"]);
    });


    Route::prefix('employees')->group(function () {

        Route::resource('employee', EmployeeController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('employee_attendance', EmployeeAttendanceController::class)->only([
            'index', 'store', 'destroy'
        ]);
        Route::get("employee_monthly_attendance", [EmployeeAttendanceController::class, "getMonthlyAttendance"]);
        Route::resource('employee_post', EmployeePostController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('employee_type', EmployeeTypeController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);

        Route::get('mark_attendance', [EmployeeAttendanceController::class,'getManualAttendance']);
        Route::post('mark_attendance', [EmployeeAttendanceController::class,'markAttendance']);
    });
    Route::prefix('exams')->group(function () {

        Route::resource('exam', ExamController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::get("exam_subjects", [ExamController::class, "getExamSubjects"]);
        Route::get("mark_structure", [MarksController::class, "getMarkStructure"]);
        Route::resource('marks', MarksController::class)->only([
            'index', 'store', 'destroy'
        ]);
        Route::get("student_marks", [MarksController::class, "getMarks"]);
    });
    Route::prefix('messages')->group(function () {

        Route::resource('student_message', StudentMessageController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('employee_message', EmployeeMessageController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);

        Route::post('quick_sms', [SMSController::class,"quickSms"]);
        Route::post('student_sms', [SMSController::class,"studentSms"]);
        Route::post('employee_sms', [SMSController::class,"employeeSms"]);
        
        Route::put('sms_account', [SMSController::class,"updateSMSAccount"]);
        Route::get('sms_account', [SMSController::class,"getSMSAccount"]);
    });
    Route::prefix('results')->group(function () {

        Route::resource('result', ResultController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::get("get_result",[ResultController::class,"getResult"]);
        Route::resource("result_publishing", ResultPublishingController::class)->only(["index", 'store']);
    });
    Route::prefix('accounts')->group(function () {

        Route::resource('/account', AccountsController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::get("/account_balance", [AccountsController::class, "getAccountBalance"]);
        Route::put("/account_balance/{id}", [AccountsController::class, "editAccountBalance"]);
    });
    Route::prefix('payments')->group(function () {

        Route::resource('/student_payment', StudentsPaymentController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('/student_payment_receipt', StudentsPaymentReceiptController::class)->only([
            'index', 'store', 'destroy'
        ]);
        Route::resource('/student_due', DueController::class)->only([
            'index', 'update', 'destroy'
        ]);
        Route::post('/student_due/pay', [DueController::class, "pay_due"]);
    });

    Route::prefix('library')->group(function () {

        Route::resource('/books', BooksController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('/books_category', BooksCategoryController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('/issue_books', IssuedBooksController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('/sell_books', SellBooksController::class)->only([
            'index', 'store', 'destroy'
        ]);
    });
    Route::prefix('products')->group(function () {

        Route::resource('/product', ProductsController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('/sell_products', SellProductsController::class)->only([
            'index', 'store', 'destroy'
        ]);
    });
    Route::prefix('website_settings')->group(function () {

        Route::resource('/gallery', GalleryController::class)->only([
            'index', "update", 'store', 'destroy'
        ]);
        Route::resource('/pages', PageController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('/notifications', NotificationController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('/homepage', HomepageController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('/slideshow', SlideshowController::class)->only([
            'index', 'store', 'destroy'
        ]);
        Route::resource('/sub_pages', SubPageController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('/albums', AlbumController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('/about_school', AboutSchoolController::class)->only([
            'index', 'update'
        ]);
        Route::resource('/school_specialty', SchoolSpecialtyController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('/testimonial', TestimonialController::class)->only([
            'index', 'store', 'destroy'
        ]);
        Route::resource('/figure', FigureController::class)->only([
            'index', 'update'
        ]);
    });
});
