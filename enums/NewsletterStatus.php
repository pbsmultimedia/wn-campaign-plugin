<?php namespace Pbs\Campaign\Enums;

class NewsletterStatus
{
    const Draft = 'draft';
    const Finished = 'finished';

    public static function values(): array
    {
        return [
            self::Draft,
            self::Finished,
        ];
    }
}
