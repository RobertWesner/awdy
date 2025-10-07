<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Template\Templates;

use RobertWesner\AWDY\Template\Border;
use RobertWesner\AWDY\Template\TemplateInterface;

/**
 * Throws all logs into the void, never to be seen again.
 */
class NullTemplate implements TemplateInterface
{

    public function getBorder(): Border
    {
        return Border::create();
    }

    public function getAreas(): array
    {
        return [];
    }

    public function handleEcho(string $echo): void {}

    public function handleProgress(float $progress, int $current = 0, int $total = 0): void {}
}
