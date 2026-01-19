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
// TODO: handle this on a controller or model?
Route::get('campaign/open/{hash}/{campaign_id}', function ($campaign_id, $hash) {
    // validate hash / subscriber
    $subscriber = \Pbs\Campaign\Models\Subscriber::where('hash', $hash)->first();

    // validate campaign
    $campaign = \Pbs\Campaign\Models\Campaign::find($campaign_id);

    if ($campaign && $subscriber) {
        // track open
        $visualization = new \Pbs\Campaign\Models\Visualization();
        $visualization->subscriber_id = $subscriber->id;
        $visualization->campaign_id = $campaign->id;
        $visualization->user_agent = request()->userAgent();
        $visualization->save();
    }

    // Return a 1×1 transparent PNG
    $png = base64_decode(
        "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9YMWZyAAAAAASUVORK5CYII="
    );

    return response($png)
        ->header('Content-Type', 'image/png')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
});

// unsubscribe is defined on plugin boot because of .. (don't remember..?)
