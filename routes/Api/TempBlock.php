<?php
Route::group(['namespace' => 'Api'], function()
{
    Route::get('temp-block', 'APITempBlockController@index')->name('tempblock.index');

    Route::post('temp-block/add', 'APITempBlockController@create')->name('tempblock.create');

    
    Route::post('tempblock/edit', 'APITempBlockController@edit')->name('tempblock.edit');
    Route::post('tempblock/show', 'APITempBlockController@show')->name('tempblock.show');
    Route::post('tempblock/delete', 'APITempBlockController@delete')->name('tempblock.delete');
});
?>