<?php
Route::group(['namespace' => 'Api'], function()
{
    Route::get('socialimages', 'APISocialImagesController@index')->name('socialimages.index');
    Route::post('socialimages/create', 'APISocialImagesController@create')->name('socialimages.create');
    Route::post('socialimages/edit', 'APISocialImagesController@edit')->name('socialimages.edit');
    Route::post('socialimages/show', 'APISocialImagesController@show')->name('socialimages.show');
    Route::post('socialimages/delete', 'APISocialImagesController@delete')->name('socialimages.delete');
});
?>