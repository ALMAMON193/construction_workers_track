<?php

use Illuminate\Support\Env;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
     return redirect()->to(env('APP_URL') . '/dashboard/login');
});
