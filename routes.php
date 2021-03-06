<?php

use App\Routing\Route;

Route::group(['prefix' => 'api'], function () {

    Route::group(['prefix' => 'news'], function () {
        Route::post('', 'NewsController@index')
            ->name('news.list');

        Route::post('{id}', 'NewsController@show')
            ->where(['id' => '\d+']);
    });

    Route::group(['prefix' => 'session'], function () {
        Route::post('', 'SessionController@index')
            ->name('session.list');

        Route::post('{id}', 'SessionController@show')
            ->where(['id' => '\d+']);

        Route::post('subscribe/{id}', 'SessionController@subscribe')
            ->where(['id' => '\d+']);
    });

    Route::any('', function (){
        return 'Not Found';
    });
});

Route::any('', function (){
    return 'Not Found';
});