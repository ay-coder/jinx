<?php

Route::group([
    "namespace"  => "TrackMessages",
], function () {
    /*
     * Admin TrackMessages Controller
     */

    // Route for Ajax DataTable
    Route::get("trackmessages/get", "AdminTrackMessagesController@getTableData")->name("trackmessages.get-list-data");

    Route::resource("trackmessages", "AdminTrackMessagesController");
});