<?php namespace Pbs\Campaign\Controllers;

use Backend\Classes\Controller;
use Pbs\Campaign\Models\Campaign;
use Pbs\Campaign\Models\Subscriber;
use Pbs\Campaign\Models\Visualization;

class Trackings extends Controller
{
    /**
     * Track email opens via pixel beacon
     */
    public function trackOpen(\Illuminate\Http\Request $request)
    {
        $hash = $request->route('hash');
        $campaignId = $request->route('campaignId');
        $userAgent = $request->header('User-Agent') ?? '';        

        // Return pixel immediately for proxies/bots
        if ($this->isProxy($userAgent)) {
            return $this->pixelResponse();
        }

        // Track the open
        $this->recordVisualization($hash, $campaignId, $userAgent);

        return $this->pixelResponse();
    }

    /**
     * Check if request is from an email proxy/bot
     */
    private function isProxy($userAgent)
    {
        // TODO: move to plugin settings
        $proxyPatterns = [
            // Gmail
            'ggpht.com',
            'googleimageproxy',
            
            // Apple Mail
            'apple-mail-protocol',            
            
            // Outlook / Microsoft
            'outlookwebanywhere',
            'outlook-android',
            'outlook-ios',
            'office365',
            
            // Yahoo Mail
            'YahooMailProxy',
        ];

        foreach ($proxyPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    /**
     * Record visualization
     */
    private function recordVisualization($hash, $campaignId, $userAgent)
    {
        try {
            $subscriber = Subscriber::where('hash', $hash)->first();
            $campaign = Campaign::find($campaignId);

            if (!$subscriber || !$campaign) {                
                return;
            }

            Visualization::create([
                'subscriber_id' => $subscriber->id,
                'campaign_id' => $campaign->id,
                'user_agent' => $userAgent,
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to record visualization', [
                'hash' => $hash,
                'campaign_id' => $campaignId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Return 1x1 transparent PNG pixel
     */
    private function pixelResponse()
    {
        $png = base64_decode("iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9YMWZyAAAAAASUVORK5CYII=");
        
        return response($png)
            ->header('Content-Type', 'image/png')
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }
}