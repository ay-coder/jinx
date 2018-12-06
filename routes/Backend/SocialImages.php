<?php

Route::group([
    "namespace"  => "SocialImages",
], function () {
    /*
     * Admin SocialImages Controller
     */

    // Route for Ajax DataTable
    Route::get("socialimages/get", "AdminSocialImagesController@getTableData")->name("socialimages.get-list-data");

    Route::resource("socialimages", "AdminSocialImagesController");
});