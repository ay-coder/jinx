<?php
Route::group(['namespace' => 'Api'], function()
{
    Route::get('trackmessages', 'APITrackMessagesController@index')->name('trackmessages.index');
    Route::post('trackmessages/create', 'APITrackMessagesController@create')->name('trackmessages.create');
    Route::post('trackmessages/edit', 'APITrackMessagesController@edit')->name('trackmessages.edit');
    Route::post('trackmessages/show', 'APITrackMessagesController@show')->name('trackmessages.show');
    Route::post('trackmessages/delete', 'APITrackMessagesController@delete')->name('trackmessages.delete');
});
?>