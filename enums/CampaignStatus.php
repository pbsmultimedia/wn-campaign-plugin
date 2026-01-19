<?php namespace Pbs\Campaign\Enums;

class CampaignStatus
{
    const Draft = 'draft';
    const Sending = 'sending';
    const Sent = 'sent';
    const Cancelled = 'cancelled';

    public static function values(): array
    {
        return [
            self::Draft,
            self::Sending,
            self::Sent,
            self::Cancelled,
        ];
    }
}
