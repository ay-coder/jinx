<?php

Route::group([
    "namespace"  => "HideMessages",
], function () {
    /*
     * Admin HideMessages Controller
     */

    // Route for Ajax DataTable
    Route::get("hidemessages/get", "AdminHideMessagesController@getTableData")->name("hidemessages.get-list-data");

    Route::resource("hidemessages", "AdminHideMessagesController");
});