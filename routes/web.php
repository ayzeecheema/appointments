<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CommonController;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('dashboard');
});
Route::get('appointments', [CommonController::class, 'dashboard'])->name('dashboard');
Route::post('image_upload', [CommonController::class, 'ImageUpload'])->name('upload');
Route::get('images_list', [CommonController::class, 'imagesList'])->name('images_list');
