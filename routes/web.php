<?php

use Illuminate\Support\Facades\Route;

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
/*
Route::get('/', function () {
    return redirect(route('backpack'));
});
*/
Route::get('/', function () {
    return redirect(route('backpack'));
});
Route::get('/home', function () {
    return redirect(route('backpack'));
});

Auth::routes();
//dev 51a914d12a9c24571c3652374e5c65de
Route::get('/v2/login/hell0k1tty/{uat}', [App\Http\Controllers\TempController::class, 'helloKitten'] )->name('04dac8afe0ca501587bad66f6b5ce5ad');
Route::post('/v2/login/hell0k1tty/', [App\Http\Controllers\TempController::class, 'login'])->name('login.offline');
Route::get('/v2/eralogin/{erakey}', [App\Http\Controllers\TempController::class, 'eraLogin'])->name('login.era');
// Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
// Route::get('/pg', [App\Http\Controllers\HomeController::class, 'playground'])->name('pg');
Route::get('/staff/image', [App\Http\Controllers\WebApiController::class, 'getImage'])->name('staff.image')->middleware('cache.headers:public;max_age=2628000;etag');
// Route::get('/geo/list', [App\Http\Controllers\OfficeController::class, 'list'])->name('geo.list');
// Route::get('/admin/build', [App\Http\Controllers\TAdminController::class, 'buildingIndex'])->name('admin.build');

// debug routes
Route::get('/d/ldaplogin', [App\Http\Controllers\DebugController::class, 'LdapLogin'])->name('d.ldaplogin');
Route::get('/d/ldapfetch', [App\Http\Controllers\DebugController::class, 'LdapFetch'])->name('d.ldapfetch');
Route::get('/d/userlogin', [App\Http\Controllers\DebugController::class, 'UserLogin'])->name('d.userlogin');
