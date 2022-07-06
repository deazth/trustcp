<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

$api = app('Dingo\Api\Routing\Router');



// $api = app('Dingo\Api\Routing\Router');

$api->version('v1', function ($api) {

  $api->get('/',            ['as' => 'api.home',   'uses' => 'App\Api\V1\Controllers\Controller@home']);
  $api->get('/pg',            ['as' => 'api.pg',   'uses' => 'App\Api\V1\Controllers\Controller@playground']);

  // BATCH

  //SMILE BATCH
  $api->get('/loadHappyReason',  ['as' => 'era.load.happyreason', 'uses' => 'App\Api\V1\Controllers\BatchController@getHappyReason']);
  $api->get('/loadHappyType',  ['as' => 'era.load.happytype', 'uses' => 'App\Api\V1\Controllers\BatchController@getHappyType']);
  $api->get('/rdustat',  ['as' => 'era.load.duserstat', 'uses' => 'App\Api\V1\Controllers\BatchController@ReloadDailyUserStat']);

  // mobile login
  $api->post('/UserLogin',  ['as' => 'user.login', 'uses' => 'App\Api\V1\Controllers\LoginController@doLogin']);
  $api->post('/UserJustLogin',  ['as' => 'user.j.login', 'uses' => 'App\Api\V1\Controllers\LoginController@justLogin']);

  $api->get('/ao/getFloorLayout',  ['as' => 'api.ao.getFloorLayout', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@getFloorLayout']);
  $api->get('/ao/getSectionLayout',  ['as' => 'api.ao.getSectionLayout', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@getSectionLayout']);

});

$api->version('v1', [
  'middleware' => 'auth:api',
  'prefix' => 'api/t'
], function ($api) {
  $api->post('/pg',  ['as' => 'api.pg', 'uses' => 'App\Api\V1\Controllers\Controller@playground']);
  $api->post('/ValidateToken',  ['as' => 'api.validtoken', 'uses' => 'App\Api\V1\Controllers\UserController@validateToken']);


  // agile office
  $api->post('/ao/locationUpdate',  ['as' => 'api.ao.locationupdate', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@locationUpdate']);
  $api->post('/ao/reverseGeo',  ['as' => 'api.ao.reverseGeo', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@reverseGeo']);
  $api->post('/ao/getCurrentCheckins',  ['as' => 'api.ao.getCurrentCheckins', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@getCurrentCheckins']);
  $api->post('/ao/getCurrentReservations',  ['as' => 'api.ao.getCurrentReservations', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@getCurrentReservations']);
  $api->post('/ao/getSeatStatus',  ['as' => 'api.ao.getSeatStatus', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@getSeatStatus']);
  $api->post('/ao/getEventStatus',  ['as' => 'api.ao.getEventStatus', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@getEventStatus']);
  $api->post('/ao/doSeatCheckin',  ['as' => 'api.ao.doSeatCheckin', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@doSeatCheckin']);
  $api->post('/ao/doEventCheckin',  ['as' => 'api.ao.doEventCheckin', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@doEventCheckin']);
  $api->post('/ao/doCheckout',  ['as' => 'api.ao.doCheckout', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@doCheckout']);

  $api->post('/ao/getBuildingList',  ['as' => 'api.ao.getBuildingList', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@getBuildingList']);
  $api->post('/ao/getFloorList',  ['as' => 'api.ao.getFloorList', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@getFloorList']);
  $api->post('/ao/getSectionList',  ['as' => 'api.ao.getSectionList', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@getSectionList']);
  $api->post('/ao/searchAvailableSeat',  ['as' => 'api.ao.searchAvailableSeat', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@searchAvailableSeat']);
  $api->post('/ao/doSeatReserve',  ['as' => 'api.ao.doSeatReserve', 'uses' => 'App\Api\V1\Controllers\AgileOfficeController@doSeatReserve']);

  // locations
  $api->post('/loc/GetCurrentStatus',  ['as' => 'api.loc.GetCurrentStatus', 'uses' => 'App\Api\V1\Controllers\WorkAnywhereController@GetCurrentStatus']);
  $api->post('/loc/CoordToAddr',  ['as' => 'api.loc.CoordToAddr', 'uses' => 'App\Api\V1\Controllers\WorkAnywhereController@CoordToAddr']);
  $api->post('/loc/CheckInCoord',  ['as' => 'api.loc.CheckInCoord', 'uses' => 'App\Api\V1\Controllers\WorkAnywhereController@CheckInCoord']);
  $api->post('/loc/UpdateCoord',  ['as' => 'api.loc.UpdateCoord', 'uses' => 'App\Api\V1\Controllers\WorkAnywhereController@UpdateCoord']);
  $api->post('/loc/CheckOutCoord',  ['as' => 'api.loc.CheckOutCoord', 'uses' => 'App\Api\V1\Controllers\WorkAnywhereController@CheckOutCoord']);

  // diary
  $api->post('/diary/GetMonCalendar',  ['as' => 'diary.GetMonCalendar', 'uses' => 'App\Api\V1\Controllers\DiaryController@GetMonCalendar']);
  $api->post('/diary/GetGwdEntries',  ['as' => 'diary.GetGwdEntries', 'uses' => 'App\Api\V1\Controllers\DiaryController@GetGwdEntries']);
  $api->post('/diary/AddGwd',  ['as' => 'diary.AddGwd', 'uses' => 'App\Api\V1\Controllers\DiaryController@AddGwd']);
  $api->post('/diary/EditGwd',  ['as' => 'diary.EditGwd', 'uses' => 'App\Api\V1\Controllers\DiaryController@EditGwd']);
  $api->post('/diary/DelGwd',  ['as' => 'diary.DelGwd', 'uses' => 'App\Api\V1\Controllers\DiaryController@DelGwd']);
  $api->post('/diary/GwdDetail',  ['as' => 'diary.GwdDetail', 'uses' => 'App\Api\V1\Controllers\DiaryController@GwdDetail']);
  $api->post('/diary/GetActTag',  ['as' => 'diary.GetActTag', 'uses' => 'App\Api\V1\Controllers\DiaryController@GetActTag']);
  $api->post('/diary/GetActType',  ['as' => 'diary.GetActType', 'uses' => 'App\Api\V1\Controllers\DiaryController@GetActType']);
  $api->post('/diary/GetActSubType',  ['as' => 'diary.GetActSubType', 'uses' => 'App\Api\V1\Controllers\DiaryController@GetActSubType']);
  $api->post('/diary/GetTribeList',  ['as' => 'diary.GetTribeList', 'uses' => 'App\Api\V1\Controllers\DiaryController@GetTribeList']);


});


$api->version('v1', [
    'middleware' => 'auth:api',
    'prefix' => 'api/tribe'
  ], function ($api) {
    $api->post('/staffno',  ['as' => 'api.tribe.getDetails', 'uses' => 'App\Api\V1\Controllers\Tribe\UserController@getDetail']);
    $api->post('/userbyskillset',  ['as' => 'api.tribe.userbyskillset', 'uses' => 'App\Api\V1\Controllers\Tribe\SkillController@getUsersBySkills']);
    $api->post('/userbyskillset2',  ['as' => 'api.tribe.userbyskillset2', 'uses' => 'App\Api\V1\Controllers\Tribe\SkillController@getUsersBySkills2']);
    $api->post('/userskills',  ['as' => 'api.tribe.userskill', 'uses' => 'App\Api\V1\Controllers\Tribe\SkillController@getUserSkills']);


  });

  $api->version('v1', ['prefix' => 'api/tribe',], function ($api) {
    $api->get('/vt',  ['as' => 'api.tribe.vt', 'uses' => 'App\Api\V1\Controllers\Tribe\UserController@validateToken']);
    $api->get('/skillset',  ['as' => 'api.tribe.skillset', 'uses' => 'App\Api\V1\Controllers\Tribe\SkillController@getSkills']);

  });

  $api->version('v1', ['prefix' => 'api/smile',], function ($api) {
    $api->get('/reason',  ['as' => 'api.smile.reason', 'uses' => 'App\Api\V1\Controllers\Smile\SmileController@getReason']);
    $api->get('/reason/{id}',  ['as' => 'api.smile.reasonbyid', 'uses' => 'App\Api\V1\Controllers\Smile\SmileController@getReasonById']);
    $api->get('/reasonByTypeID/{type_id}',  ['as' => 'api.smile.reasonByTypeID', 'uses' => 'App\Api\V1\Controllers\Smile\SmileController@getReasonByTypeID']);
  });

  $api->version('v1', ['middleware' => 'auth:api','prefix' => 'api/smile',], function ($api) {
      $api->post('/happyMeter',  ['as' => 'api.smile.happyMeter', 'uses' => 'App\Api\V1\Controllers\Smile\SmileController@happyMeter']);
    });

  //FIXIT
  $api->version('v1', ['prefix' => 'api/fixit',], function ($api) {
  $api->get('/reg',  ['as' => 'api.fixit.reg', 'uses' => 'App\Api\V1\Controllers\FixitController@fixitResponse']);
  $api->get('/getQR/{seatID}',  ['as' => 'api.fixit.reg', 'uses' => 'App\Api\V1\Controllers\FixitController@getQRbySeat']);  
});
