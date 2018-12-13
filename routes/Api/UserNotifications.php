<?php
Route::group(['namespace' => 'Api'], function()
{
    Route::post('user-notifications', 'APIUserNotificationsController@index')->name('usernotifications.index');

    Route::post('user-notifications/read-all', 'APIUserNotificationsController@readAll')->name('usernotifications.read-all');

    Route::post('user-notifications/create', 'APIUserNotificationsController@create')->name('usernotifications.create');

    Route::post('user-notifications/edit', 'APIUserNotificationsController@edit')->name('usernotifications.edit');

    Route::post('user-notifications/show', 'APIUserNotificationsController@show')->name('usernotifications.show');

    Route::post('user-notifications/delete', 'APIUserNotificationsController@delete')->name('usernotifications.delete');
});
?>