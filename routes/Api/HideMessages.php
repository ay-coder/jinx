<?php
Route::group(['namespace' => 'Api'], function()
{
    Route::get('hidemessages', 'APIHideMessagesController@index')->name('hidemessages.index');
    Route::post('hidemessages/create', 'APIHideMessagesController@create')->name('hidemessages.create');
    Route::post('hidemessages/edit', 'APIHideMessagesController@edit')->name('hidemessages.edit');
    Route::post('hidemessages/show', 'APIHideMessagesController@show')->name('hidemessages.show');
    Route::post('hidemessages/delete', 'APIHideMessagesController@delete')->name('hidemessages.delete');
});
?>