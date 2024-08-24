<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Template\Templates;

use RobertWesner\AWDY\Template\Area;
use RobertWesner\AWDY\Template\Border;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Template\TemplateInterface;

/**
 * No logging. Instead, has time elapsed and memory usage.
 */
class JustProgressTemplate implements TemplateInterface
{
    private Area $progressArea;

    private int $beginTime;

    private float $progress = 0;
    private int $current = 0;
    private int $total = 0;

    public function __construct(int $progressBarWidth = 32)
    {
        $this->beginTime = time();

        $memoryLimit = 0;
        $limit = ini_get('memory_limit');
        if ($limit >= 0) {
            $units = ['K' => 1, 'M' => 2, 'G' => 3];
            $unit = strtoupper(substr($limit, -1));
            $memoryLimit = (int)substr($limit, 0, -1) * pow(1024, $units[$unit] ?? 0);
        }

        $this->progressArea = Area::create(0, 0, -1, 0, function (
            Buffer $buffer,
        ) use (
            $progressBarWidth,
            $memoryLimit,
        ) {
            $timePassed = date('H:i:s', time() - $this->beginTime);
            $buffer->draw(0, 0, $timePassed);

            $counter = '';
            if ($this->total !== 0) {
                $counter = str_pad((string)$this->current, strlen((string)$this->total), ' ', STR_PAD_LEFT)
                    . '/' . $this->total;
                $buffer->draw(strlen($timePassed) + 1, 0, $counter);
            }

            $progressX = strlen($timePassed) + 1;
            if ($counter !== '') {
                $progressX += strlen($counter) + 1;
            }

            $buffer->draw($progressX, 0, '[');
            $progress = $progressBarWidth * $this->progress;
            $buffer->draw($progressX + 1, 0, str_repeat('=', (int)$progress) . ($this->progress < 1 ? '>' : ''));
            $buffer->draw($progressX + $progressBarWidth + 2, 0, ']');

            $memoryInformation = '';
            if ($memoryLimit > 0) {
                $memoryUsage = memory_get_usage();
                $memoryInformation = sprintf(
                    'Memory[%05.2f%%]: %s/%d',
                    $memoryUsage / $memoryLimit,
                    str_pad((string)$memoryUsage, strlen((string)$memoryLimit), ' ', STR_PAD_LEFT),
                    $memoryLimit,
                );
            }
            $buffer->draw($progressX + $progressBarWidth + 4, 0, $memoryInformation);
        });
    }

    public function getBorder(): Border
    {
        return Border::create();
    }

    public function getAreas(): array
    {
        return [
            $this->progressArea,
        ];
    }

    public function handleEcho(string $echo): void
    {
    }

    public function handleProgress(float $progress, int $current = 0, int $total = 0): void
    {
        $this->progress = $progress;
        $this->current = $current;
        $this->total = $total;
        $this->progressArea->dirty();
    }
}
