<?php

Route::group([
    "namespace"  => "ChatBoat",
], function () {
    /*
     * Admin ChatBoat Controller
     */

    // Route for Ajax DataTable
    Route::get("chatboat/get", "AdminChatBoatController@getTableData")->name("chatboat.get-list-data");

    Route::resource("chatboat", "AdminChatBoatController");
});