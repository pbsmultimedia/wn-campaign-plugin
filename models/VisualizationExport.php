<?php namespace Pbs\Campaign\Models;

use Backend\Models\ExportModel;

class VisualizationExport extends ExportModel
{
    public function exportData($columns, $sessionKey = null)
    {
        $visualizations = Visualization::with(['campaign', 'subscriber'])->get();
        $visualizations->each(function($visualization) {
            $visualization->setAttribute('campaign_subject', $visualization->campaign?->subject);
            $visualization->setAttribute('subscriber_email', $visualization->subscriber?->email);
        });
        return $visualizations->toArray();
    }
}
