<?php

Route::group([
    "namespace"  => "UserInterests",
], function () {
    /*
     * Admin UserInterests Controller
     */

    // Route for Ajax DataTable
    Route::get("userinterests/get", "AdminUserInterestsController@getTableData")->name("userinterests.get-list-data");

    Route::resource("userinterests", "AdminUserInterestsController");
});