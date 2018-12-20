<?php

Route::group([
    "namespace"  => "AdminMessages",
], function () {
    /*
     * Admin AdminMessages Controller
     */

    // Route for Ajax DataTable
    Route::get("adminmessages/get", "AdminAdminMessagesController@getTableData")->name("adminmessages.get-list-data");

    Route::resource("adminmessages", "AdminAdminMessagesController");
});