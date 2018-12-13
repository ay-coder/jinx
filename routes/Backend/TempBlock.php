<?php

Route::group([
    "namespace"  => "TempBlock",
], function () {
    /*
     * Admin TempBlock Controller
     */

    // Route for Ajax DataTable
    Route::get("tempblock/get", "AdminTempBlockController@getTableData")->name("tempblock.get-list-data");

    Route::resource("tempblock", "AdminTempBlockController");
});