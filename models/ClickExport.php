<?php namespace Pbs\Campaign\Models;

use Backend\Models\ExportModel;

class ClickExport extends ExportModel
{
    public function exportData($columns, $sessionKey = null)
    {
        $clicks = Click::with(['link.campaign', 'subscriber'])->get();
        $clicks->each(function($click) {
            $click->setAttribute('campaign_subject', $click->link?->campaign?->subject);
            $click->setAttribute('subscriber_email', $click->subscriber?->email);
            $click->setAttribute('url', $click->link?->original_url);
        });
        return $clicks->toArray();
    }
}
