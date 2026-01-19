<?php namespace Pbs\Campaign\Enums;

trait EnumValuesTrait
{
    public static function values(): array
    {
        return array_map(fn($case) => $case->value, self::cases());
    }
}
