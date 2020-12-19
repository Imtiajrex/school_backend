<?php

use App\Http\Controllers\API\Accounts\AccountsController;
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
use App\Http\Controllers\API\Settings\ClassHasDepartmentController;
use App\Http\Controllers\API\Settings\ClassHasSubjectsController;
use App\Http\Controllers\API\Settings\ReligionController;
use App\Http\Controllers\API\Settings\SchoolClassController;

use App\Http\Controllers\API\Students\StudentController;
use App\Http\Controllers\API\Students\StudentAssignmentController;
use App\Http\Controllers\API\Students\StudentPaymentInfoController;
use App\Http\Controllers\API\Students\StudentsAttendanceController;

use App\Http\Controllers\API\Employee\EmployeeController;
use App\Http\Controllers\API\Employee\EmployeeAttendanceController;
use App\Http\Controllers\API\Employee\EmployeePostController;

use App\Http\Controllers\API\Exams\ExamController;
use App\Http\Controllers\API\Exams\MarksController;

use App\Http\Controllers\API\Library\BooksController;
use App\Http\Controllers\API\Library\IssuedBooksController;
use App\Http\Controllers\API\Library\SellBooksController;


use App\Http\Controllers\API\Products\ProductsController;
use App\Http\Controllers\API\Products\SellProductsController;

use App\Http\Controllers\API\Results\ResultController;

use App\Http\Controllers\API\Payments\StudentsPaymentController;
use App\Http\Controllers\API\Payments\StudentsPaymentReceiptController;
use App\Http\Controllers\API\WebsiteSettings\GalleryController;

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
        Route::resource('session', SessionController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('role', RoleController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::get('permission', [PermissionController::class, "index"]);

        Route::get('assign_subject', [ClassHasSubjectsController::class, "index"]);
        Route::post('assign_subject', [ClassHasSubjectsController::class, "assign"]);

        Route::get('assign_department', [ClassHasDepartmentController::class, "index"]);
        Route::post('assign_department', [ClassHasDepartmentController::class, "assign"]);
    });


    Route::prefix('students')->group(function () {

        Route::resource('student', StudentController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);

        Route::resource('student_assignment', StudentAssignmentController::class)->only([
            'index', 'store', 'destroy'
        ]);

        Route::resource('assign_fees', StudentPaymentInfoController::class)->only([
            'index', 'store', 'destroy'
        ]);
        Route::resource('student_attendance', StudentsAttendanceController::class)->only([
            'index', 'store', 'destroy'
        ]);
    });


    Route::prefix('employees')->group(function () {

        Route::resource('employee', EmployeeController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('employee_attendance', EmployeeAttendanceController::class)->only([
            'index', 'store', 'destroy'
        ]);
        Route::resource('employee_post', EmployeePostController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
    });
    Route::prefix('exams')->group(function () {

        Route::resource('exam', ExamController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::resource('marks', MarksController::class)->only([
            'index', 'store', 'destroy'
        ]);
    });
    Route::prefix('results')->group(function () {

        Route::resource('result', ResultController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
        Route::get("result_has_exams", [ResultController::class, "getResultExams"]);
    });
    Route::prefix('accounts')->group(function () {

        Route::resource('/account', AccountsController::class)->only([
            'index', 'store', 'update', 'destroy'
        ]);
    });
    Route::prefix('payments')->group(function () {

        Route::resource('/student_payment', StudentsPaymentController::class)->only([
            'index', 'store', 'destroy'
        ]);
        Route::resource('/student_payment_receipt', StudentsPaymentReceiptController::class)->only([
            'index', 'store', 'destroy'
        ]);
    });

    Route::prefix('library')->group(function () {

        Route::resource('/books', BooksController::class)->only([
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
            'index', 'store', 'destroy'
        ]);
    });
});
