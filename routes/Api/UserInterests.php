<?php
Route::group(['namespace' => 'Api'], function()
{
    Route::get('userinterests', 'APIUserInterestsController@index')->name('userinterests.index');

    
    Route::post('user-interest/create', 'APIUserInterestsController@create')->name('userinterests.create');

    Route::post('userinterests/edit', 'APIUserInterestsController@edit')->name('userinterests.edit');
    Route::post('userinterests/show', 'APIUserInterestsController@show')->name('userinterests.show');
    Route::post('userinterests/delete', 'APIUserInterestsController@delete')->name('userinterests.delete');
});
?>