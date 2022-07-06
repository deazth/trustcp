<?php

// --------------------------
// Custom Backpack Routes
// --------------------------
// This route file is loaded automatically by Backpack\Base.
// Routes you generate using Backpack\Generators will be placed here.

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () {
    Route::get('login', 'Auth\LoginController@showLoginForm')->name('backpack.auth.login');
    Route::post('login', 'Auth\LoginController@login');
    Route::get('logout', 'Auth\LoginController@logout')->name('backpack.auth.logout');
    Route::post('logout', 'Auth\LoginController@logout');
});

// ios download
Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin') . '/app',
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web')
    ),
    'namespace'  => 'App\Http\Controllers',
], function () {
    Route::get('/get/trust.ipa', 'AppDownloadController@getipa')->name('app.ios');
    Route::get('/get/trust.plist', 'AppDownloadController@getplist')->name('app.ios.plist');

    Route::get('/guides', 'UserGuideController@index')->name('uguide.index');
    Route::get('/guides/dl', 'UserGuideController@downloadGuide')->name('uguide.download');
});

// Web APIs
Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin') . '/webapi',
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers',
], function () {
    Route::get('finduser', 'WebApiController@FindUsers')->name('wa.finduser');
    Route::get('findunit', 'WebApiController@FindUnits')->name('wa.findunit');
    Route::get('getacttype', 'WebApiController@GetActType')->name('wa.getacttype');
    Route::get('getactsubtype', 'WebApiController@GetActSubType')->name('wa.getactsubtype');
    Route::get('getbauexps', 'WebApiController@GetBauExps')->name('wa.getbauexps');
    Route::get('getbauroles', 'WebApiController@GetBauRoles')->name('wa.getbauroles');
    Route::get('getaddress', 'WebApiController@reverseGeo')->name('wa.reversegeo');
    Route::get('getskilltype', 'WebApiController@getSkillType')->name('wa.skilltype');
    Route::get('getSkillSet', 'WebApiController@getSkillSet')->name('wa.skillset');

    Route::get('getFloorList', 'WebApiController@getFloorList')->name('wa.getFloorList');
    Route::get('getFloorSectionList', 'WebApiController@getFloorSectionList')->name('wa.getFloorSectionList');

});

// non CRUD controllers here
Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers',
], function () {
    Route::get('pg', 'HomeController@playground')->name('pg');
    Route::get('ldapraw', 'HomeController@ldapRaw')->name('ldapraw');
    Route::get('ldapret', 'HomeController@ldapRet')->name('ldapret');
    Route::get('pushnoti', 'HomeController@testSendEmail')->name('pushnoti');
    Route::get('updateseatrefs', 'HomeController@updateseatrefs')->name('updateseatrefs');
    Route::get('charts/sample', 'Admin\Charts\SampleChartController@response')->name('charts.sample.index');

    // notification related
    Route::get('notify/clear', 'NotifyController@MarkAllAsRead')->name('notify.clear');
    Route::get('notify/read/{id}', 'NotifyController@ReadNotify')->name('notify.read');

    // user inventory related
    Route::get('inventory/count', 'InventoryController@showBuildingCount')->name('inventory.seat.showbuilding');
    Route::get('inventory/count/{t}/{id}', 'InventoryController@showCount')->name('inventory.seat.showcount');
    Route::get('area/calendar', 'InventoryController@showAreaCalendar')->name('inventory.area.calendar');
    Route::get('charts/inv-usage/{param1}/{param2}', 'Admin\Charts\InvUsageChartController@response')->name('charts.inv-usage.seat.index');
    Route::get('inventory/getfloorlayout/{id}', 'InventoryController@getfloorlayout')->name('inventory.floor.getlayout');
    Route::get('inventory/getfclayout/{id}', 'InventoryController@getfclayout')->name('inventory.fc.getlayout');

    Route::get('ao/checkin/{qr}', 'InventoryController@doWebCheckIn')->name('inv.seat.docheckin');
    Route::post('ao/checkin', 'InventoryController@reallyDoWebCheckin')->name('inv.seat.realdocheckin');
    Route::post('ao/checkin/event', 'InventoryController@reallyDoEventCheckin')->name('inv.seat.realdoevcheckin');
    Route::get('event/checkin/{qr}', 'InventoryController@doEventCheckIn')->name('inv.event.docheckin');
    Route::get('event/{id}/info', 'InventoryController@getEventInfo')->name('inv.event.info');
    Route::get('ao/workspace', 'InventoryController@workspaceLandingPage')->name('inv.landing');
    Route::get('ao/checkin', 'InventoryController@webCheckInForm')->name('inv.seat.checkinform');
    Route::get('ao/checkout/{id}', 'InventoryController@doWebCheckOut')->name('inv.seat.docheckout');
    Route::get('ao/checkout', 'InventoryController@doWebCheckOutAll')->name('inv.seat.docheckoutall');


    // personal skillset and experience
    Route::get('/user/skillset', 'PersonalSSController@list')->name('ps.list');
    Route::post('/user/skillset/add', 'PersonalSSController@updatev2')->name('ps.update');

    // diary related
    Route::get('/diaryoverview', 'DiaryController@overview')->name('diary.overview');
    Route::post('/user/activity/reset', 'DiaryController@resetDailyPerf')->name('staff.df.reset');
    Route::get('/user/diary/perfByDate/{uid}/{dt}', 'DiaryController@perfByDate')->name('diary.perfByDate');


    // news related
    Route::get('/user/news', 'NewsController@overview')->name('news.overview');
    Route::get('/user/news/carousel', 'NewsController@carousel')->name('news.carousel');
    Route::get('/user/news/{newsId}', 'NewsController@newsById')->name('news.byid');

    // sb dashboard
    Route::get('/sbdash', 'SbDashboardController@index')->name('sbdash.index');
    Route::get('/sbdash/rpt_ao_b_f_daily', 'SbDashboardController@BuildFloorUtil')->name('sbdash.rpt_ao_b_f_daily');
    Route::get('/sbdash/rpt_ao_b_dur_daily', 'SbDashboardController@BuildDurUtil')->name('sbdash.rpt_ao_b_dur_daily');
    Route::get('/sbdash/rpt_ao_b_f_monthly', 'SbDashboardController@BuildFloorMonthlyUtil')->name('sbdash.rpt_ao_b_f_monthly');
    Route::get('/sbdash/rpt_ao_f_fs_daily', 'SbDashboardController@FloorFsUtil')->name('sbdash.rpt_ao_f_fs_daily');
    Route::get('/sbdash/rpt_ao_fs_daily', 'SbDashboardController@FsDetailUtil')->name('sbdash.rpt_ao_fs_daily');
    Route::get('/sbdash/rpt_ao_area_daily', 'SbDashboardController@BuildAreaUtil')->name('sbdash.rpt_ao_area_daily');
    Route::get('/sbdash/rpt_ao_area_monthly', 'SbDashboardController@BuildAreaMonth')->name('sbdash.rpt_ao_area_monthly');
    Route::get('charts/sbd-build-util/{param1}/{param2}', 'Admin\Charts\SbdBuildUtilChartController@response')->name('charts.sbd-build-util.index');
    Route::get('charts/sbd-floor-util/{param1}/{param2}', 'Admin\Charts\SbdFloorUtilChartController@response')->name('charts.sbd-floor-util.index');
    Route::get('charts/sbd-fs-intv-util/{param1}/{param2}', 'Admin\Charts\SbdFsIntvUtilChartController@response')->name('charts.sbd-fs-intv-util.index');
    Route::get('charts/sb-build-intv/{param1}/{param2}', 'Admin\Charts\SbBuildIntvChartController@response')->name('charts.sb-build-intv.index');
    Route::get('charts/sb-floor-intv/{param1}/{param2}', 'Admin\Charts\SbFloorIntvChartController@response')->name('charts.sb-floor-intv.index');
    Route::get('charts/sb-area-monthly/{param1}/{param2}', 'Admin\Charts\SbAreaMonthlyChartController@response')->name('charts.sb-area-monthly.index');
    Route::get('charts/sb-area-weekly-intv/{param1}/{param2}', 'Admin\Charts\SbAreaWeeklyIntvChartController@response')->name('charts.sb-area-weekly-intv.index');
    Route::get('charts/sbd-build-month-intv/{param1}/{param2}', 'Admin\Charts\SbdBuildMonthIntvChartController@response')->name('charts.sbd-build-month-intv.index');
    Route::get('charts/sbd-build-month-util/{param1}/{param2}', 'Admin\Charts\SbdBuildMonthUtilChartController@response')->name('charts.sbd-build-month-util.index');
    Route::get('charts/sbd-daily-bld-wfall/{param1}/{param2}', 'Admin\Charts\SbdDailyBldWfallChartController@response')->name('charts.sbd-daily-bld-wfall.index');
    Route::get('charts/sbd-build-dur-util/{param1}/{param2}/{param3}', 'Admin\Charts\SbdBuildDurUtilChartController@response')->name('charts.sbd-build-dur-util.index');

    Route::get('/userstat', 'DashController@index')->name('userstat.index');
    Route::get('/userstat/monthlyutil', 'DashController@userStat')->name('userstat.monthlyuserstat');
    Route::get('charts/sbd-user-stat/{param1}/{param2}', 'Admin\Charts\SbdUserStatChartController@response')->name('charts.sbd-user-stat.index');
    Route::get('/userstat/dailyutil', 'DashController@dailyuserstat')->name('userstat.dailyuserstat');
    Route::get('charts/daily-user-stats/{param1}/{param2}', 'Admin\Charts\DailyUserStatsChartController@response')->name('charts.daily-user-stats.index');
    Route::get('charts/user-stats-dur-lob/{param1}/{param2}', 'Admin\Charts\UserStatsDurLobChartController@response')->name('charts.user-stats-dur-lob.index');

    Route::get('charts/sb-area-intv/{param1}/{param2}', 'Admin\Charts\SbAreaIntvChartController@response')->name('charts.sb-area-intv.index');
    Route::get('charts/sb-area-daily/{param1}/{param2}', 'Admin\Charts\SbAreaDailyChartController@response')->name('charts.sb-area-daily.index');

    Route::get('dashboard', 'AdminController@dashboard')->name('backpack.dashboard');
    Route::get('/', 'AdminController@redirect')->name('backpack');

    // TRIBE
    Route::get('/tribe/view', 'TribeController@view')->name('tribe.view');
    Route::get('/tribe/vt', 'TribeController@vt')->name('tribe.vt');

    Route::get('/tribe/index', 'TribeController@index')->name('tribe.index');
    Route::get('/detect/index', 'TribeController@detect')->name('tribe.detect');


    //Staff Finder
    Route::get('/staff/finder', 'StaffFinderCrontroller@staffFinder')->name('staff.finder');
    Route::get('/staff/profile/{uid}', 'StaffFinderCrontroller@userDetail')->name('staff.detail');

    // individual skills
    Route::get('/ind/jobcat', 'IndiSkillController@MyJobCatForm')->name('ind.jobcatform');
    Route::post('/ind/submitjobcat', 'IndiSkillController@MyJobCatSubmit')->name('ind.jobcatsubmit');

    //smile
    Route::get('/smile', 'SmileController@index')->name('smile');
    Route::get('/smile/form', 'SmileController@form')->name('smile.form');
    Route::post('/smile/submit', 'SmileController@submit')->name('smile.submit');

    // samples
    Route::get('/sample/amer', 'SampleController@amer')->name('sample.amer');
    Route::get('/sample/mun', 'SampleController@mun')->name('sample.mun');
    Route::get('/sample/nana', 'SampleController@nana')->name('sample.nana');


    //home dashboard
    Route::get('/dash/checkin', 'DashController@checkin')->name('dash.checkin');
    Route::get('/dash/diary', 'DashController@diaryChart')->name('dash.diary');
    Route::get('charts/diari/{param1}', 'Admin\Charts\DiaryChartController@response')->name('charts.diari');

    // app download
    // mobile app installers
    Route::get('/download', 'AppDownloadController@list')->name('app.list');
    Route::post('/app/upload', 'AppDownloadController@upload')->name('app.up');
    Route::get('/app/get', 'AppDownloadController@download')->name('app.down');
    Route::get('/app/delete', 'AppDownloadController@delete')->name('app.del');

    // team
    Route::get('/team/list', 'TeamController@index')->name('team.index');
    Route::get('/team/lastloc', 'TeamController@lastKnownLoc')->name('team.lastloc');
    Route::get('/team/diaryperf', 'TeamController@diaryperf')->name('team.diaryperf');
    Route::get('/team/checkinout', 'TeamController@checkinout')->name('team.checkinout');

    // super admin stuff
    Route::get('/suppa', 'SuperPowerController@index')->name('suppa.index');
    Route::post('/suppa/runjob', 'SuperPowerController@runjob')->name('suppa.runjob');
    Route::get('/suppa/findldap', 'SuperPowerController@LdapDataScan')->name('suppa.findldap');

    Route::post('diaryadmin/dobulkreset', 'DiaryAdminController@doBulkReset')->name('diaryadmin.dobulkreset');
    Route::get('diaryadmin/bulkreset', 'DiaryAdminController@showResetPage')->name('diaryadmin.bulkreset');

    Route::get('/skilladmin/invstats', 'SkillAdminController@InvolveStats')->name('sa.inv.stats');
    Route::get('/skilladmin/invlist', 'SkillAdminController@ListInvolveData')->name('sa.inv.list');

});

Route::group([
    'prefix'     => config('backpack.base.route_prefix', 'admin'),
    'middleware' => array_merge(
        (array) config('backpack.base.web_middleware', 'web'),
        (array) config('backpack.base.middleware_key', 'admin')
    ),
    'namespace'  => 'App\Http\Controllers\Admin',
], function () { // custom admin routes

    // Route::crud('office', 'OfficeCrudController');
    Route::crud('building', 'BuildingCrudController');

    Route::crud('seat', 'SeatCrudController');
    Route::get('seat/{id}/getqr', 'SeatCrudController@getqr')->name('inv.seat.qr');
    Route::get('seat/bulkadd', 'SeatCrudController@bulkaddform')->name('inv.seat.bulkaddform');
    Route::post('seat/dobulkadd', 'SeatCrudController@doBulkAdd')->name('inv.seat.dobulkadd');

    Route::crud('floor', 'FloorCrudController');

    // the rest
    Route::crud('commonconfig', 'CommonConfigCrudController');
    Route::crud('announcement', 'AnnouncementCrudController');

    Route::crud('taskcategory', 'TaskCategoryCrudController');
    Route::crud('activitytype', 'ActivityTypeCrudController');

    Route::crud('leavetype', 'LeaveTypeCrudController');
    Route::crud('skillcategory', 'SkillCategoryCrudController');
    Route::crud('skilltype', 'SkillTypeCrudController');

    Route::crud('compgroup', 'CompGroupCrudController');
    Route::crud('floorsection', 'FloorSectionCrudController');
    Route::get('floorsection/{fcid}/getAllQr', 'FloorSectionCrudController@getAllQr')->name('floorsection.getAllQr');
    Route::crud('meetingarea', 'MeetingAreaCrudController');
    Route::crud('equipmenttype', 'EquipmentTypeCrudController');
    Route::crud('areabooking', 'AreaBookingCrudController');
    Route::crud('seatcheckin', 'SeatCheckinCrudController');
    Route::crud('eventattendance', 'EventAttendanceCrudController');
    Route::crud('seatbooking', 'SeatBookingCrudController');

    // normal user - meeting area booking
    Route::crud('userareabooking', 'UserAreaBookingCrudController');
    Route::get('userareabooking/areafinder', 'UserAreaBookingCrudController@areafinder')->name('userareabooking.finder');
    Route::get('userareabooking/bookform', 'UserAreaBookingCrudController@bookform')->name('userareabooking.bookform');
    Route::post('userareabooking/areafinder', 'UserAreaBookingCrudController@areafinderresult')->name('userareabooking.searchresult');
    Route::post('userareabooking/dobooking', 'UserAreaBookingCrudController@dobooking')->name('userareabooking.dobooking');
    Route::get('userareabooking/{id}/getqr', 'UserAreaBookingCrudController@getqr')->name('area.booking.qr');

    // Seat booking
    Route::crud('reserveseat', 'UserSeatBookingCrudController');
    Route::get('reserveseat/finder', 'UserSeatBookingCrudController@seatfinder')->name('userseatbook.finder');
    Route::post('reserveseat/finder', 'UserSeatBookingCrudController@seatSearchResult')->name('userseatbook.searchresult');
    Route::post('reserveseat/book', 'UserSeatBookingCrudController@doSeatBooking')->name('userseatbook.dobooking');
    Route::crud('commonskillset', 'CommonSkillsetCrudController');


    // caretaker
    Route::crud('caretaker', 'CaretakerCrudController');
    Route::crud('ctactsubtype', 'ActSubTypeCrudController');
    Route::crud('ctacttype', 'CtakerActTypeCrudController');
    Route::crud('ct-user-manage', 'CtUserManageCrudController');

    Route::crud('gwdactivity', 'GwdActivityCrudController');
    Route::crud('unit', 'UnitCrudController');
    Route::crud('subunit', 'SubUnitCrudController');
    Route::crud('lovgp', 'LovgpCrudController');

    // User stuff
    Route::crud('personalskillset', 'PersonalSkillsetCrudController');
    Route::crud('appreciatecard', 'AppreciateCardCrudController');
    Route::get('appreciatecard/{cid}/preview', 'AppreciateCardCrudController@previewCard')->name('appreciatecard.preview');
    Route::crud('receivedcard', 'ReceivedCardCrudController');
    Route::crud('dailyperformance', 'DailyPerformanceCrudController');
    Route::get('dailyperformance/reset', 'DailyPerformanceCrudController@resetDailyPerf')->name('dailyperformance.resetdf');
    Route::crud('publicholiday', 'PublicHolidayCrudController');
    Route::crud('locationhistory', 'LocationHistoryCrudController');
    Route::get('checkinloc', 'LocationHistoryCrudController@checkinloc')->name('locationhistory.checkinloc');
    Route::post('checkinloc', 'LocationHistoryCrudController@docheckinloc')->name('locationhistory.docheckinloc');
    Route::crud('staffleave', 'StaffLeaveCrudController');
    Route::crud('news', 'NewsCrudController');
    Route::crud('coordmapping', 'CoordMappingCrudController');
    Route::crud('attendance', 'AttendanceCrudController');
    Route::crud('user-team-history', 'UserTeamHistoryCrudController');

    Route::crud('push-noti-history', 'PushNotiHistoryCrudController');
    Route::crud('batch-diary-report', 'BatchDiaryReportCrudController');
    Route::get('batch-diary-report/{id}/download', 'BatchDiaryReportCrudController@download')->name('bdr.rpt.download');

    Route::get('gwd/list/{uid}/{dt}/', 'GwdActivityCrudController@listbydate')->name('gwd.listbydate');
    Route::crud('guide', 'GuideCrudController');
    Route::crud('leave-information', 'LeaveInformationCrudController');

    Route::crud('ideabox', 'IdeaCrudController');
    Route::get('ideabox/{id}/togglelike/', 'IdeaCrudController@togglelike')->name('ideabox.togglelike');
    Route::crud('idea-category', 'IdeaCategoryCrudController');
    Route::crud('neo-wsr-history', 'NeoWsrHistoryCrudController');


    Route::crud('user-stat-history', 'UserStatHistoryCrudController');
    Route::crud('assistant', 'AssistantCrudController');

    Route::crud('pers-job-type', 'PersJobTypeCrudController');

    Route::crud('bau-exp-group', 'BauExpGroupCrudController');
    Route::crud('bau-exp-type', 'BauExpTypeCrudController');
    Route::crud('jobscope', 'JobscopeCrudController');
    Route::crud('bau-experience', 'BauExperienceCrudController');
    Route::crud('involvement', 'InvolvementCrudController');
    Route::get('charts/involve-stat', 'Charts\InvolveStatChartController@response')->name('charts.involve-stat.index');
}); // this should be the absolute last line of this file
