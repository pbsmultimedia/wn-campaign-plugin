<?php namespace Pbs\Campaign\Enums;

enum CampaignRecipientStatus: string
{
    use EnumValuesTrait;

    case Pending = 'pending';
    case Sending = 'sending';
    case Sent = 'sent';
    case Failed = 'failed';
}
