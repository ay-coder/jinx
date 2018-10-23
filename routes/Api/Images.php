<?php
Route::group(['namespace' => 'Api'], function()
{
    Route::get('images', 'APIImagesController@index')->name('images.index');
    Route::post('images/create', 'APIImagesController@create')->name('images.create');
    Route::post('images/edit', 'APIImagesController@edit')->name('images.edit');
    Route::post('images/show', 'APIImagesController@show')->name('images.show');
    Route::post('images/delete', 'APIImagesController@delete')->name('images.delete');
});
?>