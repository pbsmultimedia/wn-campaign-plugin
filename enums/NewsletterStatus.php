<?php namespace Pbs\Campaign\Enums;

enum NewsletterStatus: string
{
    use EnumValuesTrait;

    case Draft = 'draft';
    case Finished = 'finished';
}
