<?php namespace Pbs\Campaign\Components;

use Cms\Classes\ComponentBase;
use Pbs\Campaign\Models\Campaign;
use Pbs\Campaign\Enums\CampaignStatus;

class Campaigns extends ComponentBase
{
    public function componentDetails(): array
    {
        return [
            'name'        => 'Campaigns List',
            'description' => 'Renders a list of Campaign newsletters.'
        ];
    }

    // seems that newsletters / campaign need to have language / locale to display on the frontend
    public function onRun()
    {
        // not sure about this design decision of subject being defined on the campaign
        // and needing to get the campaign to get the subject..
        $campaigns = Campaign::where('status', CampaignStatus::Sent)->get();        

        $this->page['campaigns'] = $campaigns;
    }
}
