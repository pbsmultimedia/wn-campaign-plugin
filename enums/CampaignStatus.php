<?php namespace Pbs\Campaign\Enums;

enum CampaignStatus: string
{
    use EnumValuesTrait;

    case Draft = 'draft';
    case Sending = 'sending';
    case Sent = 'sent';
    case Cancelled = 'cancelled';
}
