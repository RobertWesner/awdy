<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Template;

trait AbsoluteCoordinateTrait
{
    protected function absoluteCoordinate(int $value, int $max): int
    {
        if ($value >= 0) {
            return $value;
        }

        return $max + $value;
    }
}
