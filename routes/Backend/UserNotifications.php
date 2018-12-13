<?php

Route::group([
    "namespace"  => "UserNotifications",
], function () {
    /*
     * Admin UserNotifications Controller
     */

    // Route for Ajax DataTable
    Route::get("usernotifications/get", "AdminUserNotificationsController@getTableData")->name("usernotifications.get-list-data");

    Route::resource("usernotifications", "AdminUserNotificationsController");
});