<?php
Route::group(['namespace' => 'Api'], function()
{
    Route::get('adminmessages', 'APIAdminMessagesController@index')->name('adminmessages.index');
    Route::post('adminmessages/create', 'APIAdminMessagesController@create')->name('adminmessages.create');
    Route::post('adminmessages/edit', 'APIAdminMessagesController@edit')->name('adminmessages.edit');
    Route::post('adminmessages/show', 'APIAdminMessagesController@show')->name('adminmessages.show');
    Route::post('adminmessages/delete', 'APIAdminMessagesController@delete')->name('adminmessages.delete');
});
?>