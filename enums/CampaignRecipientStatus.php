<?php namespace Pbs\Campaign\Enums;

class CampaignRecipientStatus
{
    const Pending = 'pending';
    const Sending = 'sending';
    const Sent = 'sent';
    const Failed = 'failed';

    public static function values(): array
    {
        return [
            self::Pending,
            self::Sending,
            self::Sent,
            self::Failed,
        ];
    }
}
