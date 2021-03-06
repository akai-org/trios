<?php

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

use App\Trio;
use Illuminate\Http\Request;


// To delete?
/*Route::get('/dev/playground', function() {
    $trio = Trio::inRandomOrder()->first();
    return view('dev.playground')->with('data', $trio);
});*/

Auth::routes();

// Socialite routes
Route::group(['prefix' => 'auth'], function() {
    Route::get('/email', 'Auth\EmailController@ask');
    Route::post('/email', 'Auth\EmailController@set');
    Route::get('/{provider}', 'Auth\SocialController@redirectToProvider');
    Route::get('/{provider}/callback', 'Auth\SocialController@handleProviderCallback');
});

Route::get('/', 'HomeController@index');

//Route::get('solve', 'SolveController@getRandom');

//Route::get('solve/{trio}', 'SolveController@show');

//Route::post('solve/{trio}', 'SolveController@check');

Route::get('user/{user}', 'UserController@show');

// Admin panel routes
Route::group(['prefix' => 'admin', 'middleware' => 'role:admin|mod'], function () {
    Route::get('/', 'AdminController@index');

    Route::get('/stats', 'StatsController@index');

    Route::post('/trios/{trio}/active', 'TriosController@active');
    Route::resource('/trios', 'TriosController');

    Route::get('reports', 'ReportController@index');
    Route::delete('reports/{report}', 'ReportController@destroy');

    /*Route::group(['prefix' => 'test'], function (){
        Route::get('email', function (){
            Mail::to('marcin@lawniczak.me')->sendNow(new \App\Mail\Test());
        });
    });*/

});

//New routes using API and AJAX
Route::get('/solve', function () {
    return view('pages.solveAjax');
});

//Static routes
Route::get('about', function () {
    return view('pages.about');
});