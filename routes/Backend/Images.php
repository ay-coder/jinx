<?php

Route::group([
    "namespace"  => "Images",
], function () {
    /*
     * Admin Images Controller
     */

    // Route for Ajax DataTable
    Route::get("images/get", "AdminImagesController@getTableData")->name("images.get-list-data");

    Route::resource("images", "AdminImagesController");
});