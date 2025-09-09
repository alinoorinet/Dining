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

Route::get('/',                                     'WelcomeController@index')->name('login');
Route::get('/fetching',                             'WelcomeController@fetching');
Route::get('/force-logout',                         'WelcomeController@force_logout');
Route::get('/sso-force-logout/{token}/{username}',  'WelcomeController@sso_force_logout');

Route::group(['prefix'=>'/','namespace'=>'Auth'],function () {
//    Route::post('login',    'LoginController@login');
    Route::post('logout',  'LoginController@logout');
});

Route::get('/home',                         'HomeController@index')->name('home');
Route::get('/home/ping',                    'HomeController@ping');
Route::post('/home/check-device-status',    'HomeController@check_device_status');
Route::post('/home/callback',               'Cms\ReservationController@callback');
Route::get('/home/users-compare',          'HomeController@users_compare');
Route::get('/home/test-webservice',          'HomeController@test_webservice');


Route::group(['prefix'=>'/home/collection','namespace'=>'Cms'],function () {
    Route::get('/',            'CollectionController@index');
    Route::get('/edit/{id}',   'CollectionController@edit');
    Route::get('/delete/{id}', 'CollectionController@delete');
    Route::post('/store',      'CollectionController@store');
    Route::post('/update',     'CollectionController@update');
});
Route::group(['prefix'=>'/home/rest','namespace'=>'Cms'],function () {
    Route::get('/',            'RestController@index');
    Route::get('/edit/{id}',   'RestController@edit');
    Route::get('/delete/{id}', 'RestController@delete');
    Route::get('/info/{id}',   'RestController@info');
    Route::get('/info/delete/{id}', 'RestController@info_delete');
    Route::post('/info/store', 'RestController@info_store');
    Route::post('/store',      'RestController@store');
    Route::post('/update',     'RestController@update');
});
Route::group(['prefix'=>'/home/user-group','namespace'=>'Cms'],function () {
    Route::get('/',            'UserGroupController@index');
    Route::get('/edit/{id}',   'UserGroupController@edit');
    Route::get('/delete/{id}', 'UserGroupController@delete');
    Route::get('/user-group-users/delete/{id}', 'UserGroupController@delete_user_group_users');
    Route::post('/store',      'UserGroupController@store');
    Route::post('/update',     'UserGroupController@update');
    Route::post('/add-user',   'UserGroupController@add_user');
    Route::post('/search',     'UserGroupController@search');
});
Route::group(['prefix'=>'/home/store','namespace'=>'Cms'],function () {
    Route::get('/',            'StoreController@index');
    Route::get('/edit/{id}',   'StoreController@edit');
    Route::get('/delete/{id}', 'StoreController@delete');
    Route::post('/store',      'StoreController@store');
    Route::post('/update',     'StoreController@update');
    Route::get('/goods/details/{id}',           'StoreController@goods_index');
    Route::post('/goods/details/sync-reserves', 'StoreController@goods_sync_reserves');
    Route::post('/goods/store',                 'StoreController@goods_store');
    Route::get('/goods/inventory/details/{id}', 'StoreController@inventory_index');
    Route::post('/goods/inventory/store',       'StoreController@inventory_store');
});
Route::group(['prefix'=>'/home/event','namespace'=>'Cms'],function () {
    Route::get('/',               'EventController@index');
    Route::get('/edit/{id}',      'EventController@edit');
    Route::get('/delete/{id}',    'EventController@delete');
    Route::get('/de-active/{id}', 'EventController@de_active');
    Route::get('/confirm/{id}',   'EventController@confirm');
    Route::get('/details/{id}',   'EventController@details');
    Route::post('/store',         'EventController@store');
    Route::post('/update',        'EventController@update');
});

Route::group(['prefix'=>'/home/fingerprint','namespace'=>'Cms'],function () {
    Route::get('/enroll',   'FingerPrintController@enroll_index');
    Route::post('/init',    'FingerPrintController@init');
    Route::post('/enroll',  'FingerPrintController@enroll');
    Route::post('/create-session', 'FingerPrintController@create_session');
    Route::post('/auto-capture', 'FingerPrintController@auto_capture');
});
Route::group(['prefix'=>'/home/dorm','namespace'=>'Cms'],function () {
    Route::get('/dorm-exception',            'DormController@dorm_exception_index');
    Route::post('/dorm-exception/store',     'DormController@dorm_exception_store');
    Route::get('/dorm-exception/delete/{id}','DormController@dorm_exception_delete');
});
Route::group(['prefix'=>'/home/users','namespace'=>'Cms'],function () {
    Route::get('/',          'UserController@index');
    Route::get('/repeat',          'UserController@repeatTran');
    Route::get('/repeatPhone',          'UserController@repeatPhone');
    Route::get('/double',          'UserController@doubleRes');
    Route::get('/{id}/{p}',  'UserController@change_role');
    Route::get('/add',       'UserController@add');
    Route::post('/store',    'UserController@store');
    Route::get('/srch',      'UserController@search_form');
    Route::post('/srch',     'UserController@search');
    Route::post('/de-active','UserController@de_active');
});
Route::group(['prefix'=>'/home/roles','namespace'=>'Cms'],function () {
    Route::get('/'                          ,'RoleController@index');
    Route::get('/add'                       ,'RoleController@add');
    Route::get('/roles-actions'             ,'RoleController@roles_actions');
    Route::post('/store'                    ,'RoleController@store');
    Route::post('/roles-actions/get-actions','RoleController@get_actions');
    Route::post('/roles-actions/set-actions','RoleController@set_actions');
});
Route::group(['prefix'=>'/home/modules','namespace'=>'Cms'],function () {
    Route::get('/',       'ModuleController@index');
    Route::get('/add',    'ModuleController@add');
    Route::post('/store', 'ModuleController@store');
});

Route::group(['prefix'=>'/home/luhe','namespace'=>'Cms'],function () {
    Route::get('/',               'LimitUHEController@index');
    Route::get('/de-active/{id}', 'LimitUHEController@de_active');
    Route::get('/delete/{id}',    'LimitUHEController@delete');
    Route::post('/store',         'LimitUHEController@store');
});
Route::group(['prefix'=>'/home/setting','namespace'=>'Cms'],function () {
    Route::get('/',        'SettingController@index');
    Route::post('/update', 'SettingController@update');
    Route::get('/delete-rte/{id}', 'SettingController@delete_rte');
});
Route::group(['prefix'=>'/home/actions','namespace'=>'Cms'],function () {
    Route::get('/',            'ActionController@index');
    Route::get('/add',        'ActionController@add');
    Route::post('/store',     'ActionController@store');
    Route::post('/update',    'ActionController@update');
    Route::get('/delete/{id}','ActionController@delete');
    Route::get('/edit/{id}',  'ActionController@edit');
});
Route::group(['prefix'=>'/home/reservation','namespace'=>'Cms','middleware' => ['auth']],function () {
    Route::post('/set',             'ReservationController@set');
    Route::post('/cancel',          'ReservationController@cancel');
    Route::post('/select-date',     'ReservationController@select_date');
    Route::post('/pay',             'ReservationController@pay');
    Route::post('/week-play',       'ReservationController@week_play');
    Route::post('/make-order-modal','ReservationController@make_order_modal');
    Route::post('/change-user',     'ReservationController@change_current_user');
    Route::post('/self-change',     'ReservationController@self_change');
});
Route::group(['prefix'=>'/home/reserves-report','namespace'=>'Cms'],function () {
    Route::get('/',                                 'ReservesController@index');
    Route::get('/guests',                           'ReservesController@guests');
    Route::post('/guests',                          'ReservesController@guests_data');
    Route::get('/mode-2',                           'ReservesController@mode_2');
    Route::post('/mode-2-data',                     'ReservesController@mode_2_data');
    Route::get('/manual-check',                     'ReservesController@manual_check');
    Route::post('/total',                           'ReservesController@total');
    Route::post('/total-dorm',                      'ReservesController@total_dorm');
    Route::post('/check-reserve',                   'ReservesController@check_reserve');
    Route::post('/mark-as-eaten',                   'ReservesController@mark_as_eaten');
    Route::get('/fe-male-count',                    'ReservesController@fe_male_count');
    Route::post('/fe-male-count-by-date',           'ReservesController@fe_male_count_by_date');
    Route::get('/fe-male-count/print',              'ReservesController@fe_male_count_print');
    Route::post('/home-reserve-count',              'ReservesController@home_reserve_count');
    Route::get('/statistics',                       'ReservesController@statistics');
    Route::post('/free-statistics',                 'ReservesController@free_statistics');
    Route::post('/load-more-free',                  'ReservesController@load_more_free');
    Route::get('/statistics/free-reserves/{date}',  'ReservesController@free_statistics_reserves_excel');
    Route::get('/active-users',                     'ReservesController@active_users');
    Route::post('/active-users',                    'ReservesController@active_users_calculate');
    Route::get('/pay-back',                         'ReservesController@pay_back');
    Route::post('/pay-back-res',                    'ReservesController@pay_back_res');
    Route::get('/edit-reserve-name',                'ReservesController@edit_reserve_name');
    Route::post('/edit-reserve-name/get_names',     'ReservesController@edit_reserve_name_get_names');
    Route::post('/edit-reserve-name',               'ReservesController@update_reserve_name');
});
Route::group(['prefix'=>'/home/foods','namespace'=>'Cms'],function () {
    Route::get('/',            'FoodController@index');
    Route::get('/delete/{id}', 'FoodController@delete');
    Route::post('/edit',       'FoodController@edit');
    Route::post('/store',      'FoodController@store');
    Route::post('/price/store','FoodController@price_store');
    Route::post('/price/edit', 'FoodController@price_edit');
    Route::post('/price/get-rest-price', 'FoodController@get_rest_price');
    Route::post('/search',     'FoodController@search');
});
Route::group(['prefix'=>'/home/transactions','namespace'=>'Cms','middleware'=>['auth',]],function (){
    Route::get('/verify',           'TransactionsController@verify');
    Route::post('/inquiry',         'TransactionsController@inquiry');
    Route::get('/pay-gate',         'TransactionsController@pay_gate');
    Route::get('/pay-manual',       'TransactionsController@pay_manual');
    Route::get('/print-all',        'TransactionsController@print_all');
    Route::post('/get-by-date',     'TransactionsController@get_by_date');
    Route::post('/load-more-free',  'TransactionsController@load_more_free');
});
Route::group(['prefix'=>'/home/wallet','namespace'=>'Cms',],function (){
    Route::get('/', 'WalletController@index');
});
Route::group(['prefix'=>'/home/inventory','namespace'=>'Cms'],function (){
    Route::get('/',                    'InventoryController@index');
    Route::post('/search',             'InventoryController@search');
    Route::post('/add-wallet-amount',  'InventoryController@add_wallet_amount');
    Route::post('/sub-wallet-amount',  'InventoryController@sub_wallet_amount');
});
Route::group(['prefix'=>'/home/define-day-food','namespace'=>'Cms'],function () {
    Route::get('/add',        'MenuController@add');
    Route::post('/store',     'MenuController@store');
    Route::get('/delete/{id}','MenuController@delete');
    Route::post('/get-prices','MenuController@get_prices');
    Route::post('/get-menus', 'MenuController@get_menus');
    Route::post('/next-week', 'MenuController@next_week');
    Route::post('/curr-week', 'MenuController@curr_week');
    Route::post('/prev-week', 'MenuController@prev_week');
    Route::post('/cancel-menu','MenuController@cancel_menu');
    Route::post('/common-setting','MenuController@common_setting');
});
Route::group(['prefix'=>'/home/activitys','namespace'=>'Cms'],function (){
    Route::get('/', 'ActivityController@index');
});

Route::group(['prefix'=>'/home/notification','namespace'=>'Cms'],function (){
    Route::get('/',                         'NotificationController@index');
    Route::get('/add',                      'NotificationController@add');
    Route::post('/store',                   'NotificationController@store');
    Route::get('/edit/{id}',                'NotificationController@edit');
    Route::get('/delete/{id}',              'NotificationController@delete');
    Route::post('/update',                  'NotificationController@update');
    Route::post('/allow-this',              'NotificationController@allow_this');
    Route::post('/disallow-this',           'NotificationController@disallow_this');
    Route::get('/delete',                   'NotificationController@delete');
});

Route::group(['prefix'=>'/home/card','namespace'=>'Cms'],function (){
    Route::get('/',                         'CardController@index');
    Route::get('/delete/{id}',              'CardController@delete');
    Route::post('/define',                  'CardController@define');
    Route::post('/search',                  'CardController@search');
    Route::post('/write',                   'CardController@write');
    Route::post('/checking-card',           'CardController@checking');
    Route::post('/check-reserve',           'CardController@check_reserve');
    Route::post('/free-check-reserve',      'CardController@free_check_reserve');
    Route::post('/eatened-counter',         'CardController@eatened_counter');
    Route::post('/free-eatened-counter',    'CardController@free_eatened_counter');
    Route::post('/write-barcode',           'CardController@write_barcode');
    Route::post('/check-reserve-barcode',   'CardController@check_reserve_barcode');
});
Route::group(['prefix'=>'/home/queue','namespace'=>'Cms'],function (){
    Route::get('/choose-check',             'QueueController@choose_check');
    Route::get('/check/{qName}',            'QueueController@check_index');
    Route::get('/choose-prepared',          'QueueController@choose_prepared');
    Route::get('/prepared/{qName}',         'QueueController@prepared_index');
    Route::post('/set-prepared',            'QueueController@set_prepared');
    Route::post('/get-queue',               'QueueController@get_queue');
    Route::post('/get-prepared-queue',      'QueueController@get_prepared_queue');
});

// Route::post('/contact-us', 'Cms\ContactController@store_from_out')->name('contactus');
Route::group(['prefix'=>'/home/contact-us','namespace'=>'Cms','middleware'=>['auth','Filter']],function (){
    Route::get('/',                         'ContactController@index');
    Route::get('/show',                     'ContactController@show');
    Route::get('/delete/{id}',              'ContactController@delete');
    Route::post('/readed',                  'ContactController@readed');
    Route::post('/store',                   'ContactController@store');
    Route::post('/response',                'ContactController@response');
});

Route::group(['prefix'=>'/home/feedback','namespace'=>'Cms'],function (){
    Route::get('/',                         'FeedbackController@index');
    Route::post('/send',                    'FeedbackController@send');
    Route::post('/checked',                 'FeedbackController@checked');
    Route::post('/delete',                  'FeedbackController@delete');
});

Route::group(['prefix'=>'/home/poll','namespace'=>'Cms'],function (){
    Route::get('/',                         'PollController@index');
    Route::get('/add',                      'PollController@add');
    Route::post('/create',                  'PollController@create');
    Route::post('/get-current-poll',        'PollController@current_poll');
    Route::get('/active/{id}',              'PollController@active');
    Route::get('/deactive',                 'PollController@deactive');
    Route::get('/delete/{id}',              'PollController@delete');
    Route::get('/edit/{id}',                'PollController@edit');
    Route::post('/update',                  'PollController@update');
    Route::post('/vote',                    'PollController@vote');
    Route::get('/records/{id}',             'PollController@records');
    Route::get('/test',                     'PollController@test');
});
