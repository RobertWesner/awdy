<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Template\Templates;

use RobertWesner\AWDY\Template\Area;
use RobertWesner\AWDY\Template\Border;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Template\BufferLogger;
use RobertWesner\AWDY\Template\Connection;
use RobertWesner\AWDY\Template\Facing;
use RobertWesner\AWDY\Template\TemplateInterface;

class SimpleTemplate implements TemplateInterface
{
    private Area $logArea;
    private Area $progressArea;

    private float $progress = 0;
    private int $current = 0;
    private int $total = 0;
    private BufferLogger $logger;

    public function __construct()
    {
        $this->logger = new BufferLogger();

        $this->logArea = Area::create(2, 3, -3, -2, function (Buffer $buffer) {
            $this->logger->renderTo($buffer);
        });

        $this->progressArea = Area::create(2, 1, -3, 1, function (Buffer $buffer) {
            $counter = '';
            if ($this->total !== 0) {
                $counter = str_pad((string)$this->current, strlen((string)$this->total), ' ', STR_PAD_LEFT)
                    . '/' . $this->total;
                $buffer->draw(0, 0, $counter);
            }

            $buffer->draw(strlen($counter) + 1, 0, '[');
            $progressBarWidth = $buffer->getWidth() - strlen($counter) - 2;
            $progress = $progressBarWidth * $this->progress;
            $buffer->draw(strlen($counter) + 2, 0, str_repeat('=', (int)$progress) . ($this->progress < 1 ? '>' : ''));
            $buffer->draw(-1, 0, ']');
        });
    }

    public function getBorder(): Border
    {
        return Border::create()
            ->horizontal('-')
            ->vertical('|')
            ->corners('.', '.', '\'', '\'')
            ->connectFacing('+', '+', '+', '+', '+')
            ->connections([
                Connection::horizontal()
                    ->begin(0, 2, Facing::RIGHT)
                    ->end(-1, 2, Facing::LEFT),
            ]);
    }

    public function getAreas(): array
    {
        return [
            $this->logArea,
            $this->progressArea,
        ];
    }

    public function handleEcho(string $echo): void
    {
        $this->logger->append($echo);
        $this->logArea->dirty();
    }

    public function handleProgress(float $progress, int $current = 0, int $total = 0): void
    {
        $this->progress = $progress;
        $this->current = $current;
        $this->total = $total;
        $this->progressArea->dirty();
    }
}
