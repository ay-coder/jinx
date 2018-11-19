<?php

Route::group([
    "namespace"  => "Settings",
], function () {
    /*
     * Admin Settings Controller
     */

    // Route for Ajax DataTable
    Route::get("settings/get", "AdminSettingsController@getTableData")->name("settings.get-list-data");

    Route::resource("settings", "AdminSettingsController");
});