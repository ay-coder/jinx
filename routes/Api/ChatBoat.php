<?php
Route::group(['namespace' => 'Api'], function()
{
    Route::any('get-chatboat', 'APIChatBoatController@index')->name('chatboat.index');
    
    Route::post('answer-chatboat', 'APIChatBoatController@answer')->name('chatboat.answer');

    Route::post('chatboat/create', 'APIChatBoatController@create')->name('chatboat.create');
    Route::post('chatboat/edit', 'APIChatBoatController@edit')->name('chatboat.edit');
    Route::post('chatboat/show', 'APIChatBoatController@show')->name('chatboat.show');
    Route::post('chatboat/delete', 'APIChatBoatController@delete')->name('chatboat.delete');
});
?>