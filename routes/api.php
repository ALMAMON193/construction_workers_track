<?php

// routes/api.php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\FAQController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\ExpenseController;
use App\Http\Controllers\API\RattingController;
use App\Http\Controllers\API\Auth\LoginController;
use App\Http\Controllers\API\CheckInOutController;
use App\Http\Controllers\API\SocialAuthController;
use App\Http\Controllers\API\Auth\LogoutController;
use App\Http\Controllers\API\DailyUpdateController;
use App\Http\Controllers\API\ReportScreenController;
use App\Http\Controllers\API\User\ProfileController;
use App\Http\Controllers\API\Auth\RegisterController;
use App\Http\Controllers\API\FacingProblemController;
use App\Http\Controllers\API\Auth\ResetPasswordController;

//register
Route::post('register', [RegisterController::class, 'register']);
Route::post('/verify-email', [RegisterController::class, 'VerifyEmail']);
Route::post('/resend-otp', [RegisterController::class, 'ResendOtp']);
//login
Route::post('login', [LoginController::class, 'login']);
//forgot password
Route::post('/forget-password', [ResetPasswordController::class, 'forgotPassword']);
Route::post('/verify-otp', [ResetPasswordController::class, 'VerifyOTP']);
Route::post('/reset-password', [ResetPasswordController::class, 'ResetPassword']);
 //social login
Route::post('/social-login', [SocialAuthController::class, 'SocialLogin']);

Route::group(['middleware' => 'auth:sanctum'], static function () {
    Route::get('/refresh-token', [LoginController::class, 'refreshToken']);
    Route::post('/logout', [LogoutController::class, 'logout']);

    /*======================User profile update start ===========================*/
    Route::get('/view-profile', [ProfileController::class, 'viewProfile']);
    Route::post('/update-profile', [ProfileController::class, 'updateProfile']);
    Route::post('/upload-avatar', [ProfileController::class, 'uploadAvatar']);
    Route::post('/update-password', [ProfileController::class, 'updatePassword']);
    Route::post('/delete-avatar', [ProfileController::class, 'deleteAvatar']);
    Route::post('/delete-account', [ProfileController::class, 'deleteAccount']);

    /*======================end Profile section ================================*/

    /*==========================FAQ Section ===================================*/
    Route::get('/faq', [FAQController::class, 'index']);
    /*==========================end FAQ section ================================*/
    /*==========================Daily Update Tastk Section ================================*/

    /*==========================Daily Update Tastk Section ================================*/
    Route::get('/daily-update-task', [DailyUpdateController::class, 'index']);
    Route::post('/create-daily-update-task', [DailyUpdateController::class, 'store']);
    Route::post('/details-daily-update-task/{dailyTask}', [DailyUpdateController::class, 'update']);
    Route::post('/delete-task-description/{dailyTask}/{descriptionId}', [DailyUpdateController::class, 'deleteTaskDescription']);
    /*==========================end Daily Update Tastk Section ================================*/

    /*==========================User Location Section ===================================*/
    Route::get('/user-location', [UserController::class, 'UserLocation']);
    Route::post('/user-location/store', [UserController::class, 'UserLocationStore']);
    Route::post('/user-location/update/{id}', [UserController::class, 'UserLocationUpdate']);
    /*==========================end User Location section ================================*/
    /*===========================Check in and Checkout Controller =========================*/
    Route::post('/attendance', [CheckInOutController::class, 'storeAttendance']);
    //today check in and check out session
    Route::get('/attendance/today', [CheckInOutController::class, 'todayAttendance']);
    //check in and check out history
    Route::get('/checking/history', [CheckInOutController::class, 'checkingHistory']);
    /*==========================end Check in and Checkout Controller =========================*/

    /*===========================Facing Problem Section =========================*/
    Route::get('/facing-problems', [FacingProblemController::class, 'index']);
    Route::post('/facing-problem', [FacingProblemController::class, 'store']);
    Route::get('/facing-problem/{id}', [FacingProblemController::class, 'view']);
    /*==========================end Facing Problem Section =========================*/

    /*==========================Expanse Section start ================================*/
    Route::post('/expanse-money', [ExpenseController::class, 'store']);
    Route::get('/expanses-list', [ExpenseController::class, 'index']);
    Route::get('/expanse-details/{id}', [ExpenseController::class, 'view']);
    /*==========================end Expanse Section ================================*/

    /**==========================Ratting Section ================================ */
    Route::post('/ratting', [RattingController::class, 'ratting']);

    /**==========================Report Section ================================ */
    Route::get('/report-screen', [ReportScreenController::class, 'reportScreen']);
    Route::get('/paychecks/{id}', [ReportScreenController::class, 'paychecks']);


});
