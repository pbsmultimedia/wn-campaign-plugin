<?php

// it seems that plugin routes override the CMS routes..!
/*
Route::get('newsletter/{id}', function ($id) {
    return $id;
});
*/

// guess that routes should be prefixed with plugin name
// had issue with the cookie.. one hour + spent..
/*
Gemini says:
"The issue is that your tracking route generates an unencrypted cookie (because it lacks middleware), but the destination page expects an encrypted one (standard behavior)."
*/

Route::group(['middleware' => 'web'], function () {
    Route::get('campaign/link/{link_hash}/{recipient_hash}', 'Pbs\Campaign\Controllers\Links@track');
});

// nice to have
// Route::post('campaign/bounce', 'Pbs\Campaign\Controllers\Subscribers@bounce');

// Sometimes cache may fool you..
Route::get('test-exception', function () {
    // return 'test-exception';
    throw new \Exception('This is a test exception.');
});

// track opens
Route::get('campaign/open/{campaignId}/{hash}', 
    'Pbs\Campaign\Controllers\Trackings@trackOpen'
);

// unsubscribe is defined on plugin boot because of .. (don't remember..? throttling?)
