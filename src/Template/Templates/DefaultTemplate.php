<?php

declare(strict_types=1);

namespace RobertWesner\AWDY\Template\Templates;

use RobertWesner\AWDY\AnsiEscape;
use RobertWesner\AWDY\Template\Area;
use RobertWesner\AWDY\Template\Border;
use RobertWesner\AWDY\Template\Buffer;
use RobertWesner\AWDY\Template\BufferLogger;
use RobertWesner\AWDY\Template\Connection;
use RobertWesner\AWDY\Template\Facing;
use RobertWesner\AWDY\Template\TemplateInterface;

class DefaultTemplate implements TemplateInterface
{
    private Area $logArea;
    private Area $progressArea;

    private float $progress = 0;
    private BufferLogger $logger;

    public function __construct()
    {
        $this->logger = new BufferLogger();

        $this->logArea = Area::create(5, 3, -6, -10, function (Buffer $buffer) {
            $this->logger->renderTo($buffer);
        });

        $this->progressArea = Area::create(5, -6, -6, -4, function (Buffer $buffer) {
            $buffer->draw(1, 0, '.', AnsiEscape::fg(8));
            $buffer->draw(2, 0, str_repeat('-', $buffer->getWidth() - 4), AnsiEscape::fg(8));
            $buffer->draw(-2, 0, '.', AnsiEscape::fg(8));
            $buffer->draw(1, 2, '\'', AnsiEscape::fg(8));
            $buffer->draw(2, 2, str_repeat('-', $buffer->getWidth() - 4), AnsiEscape::fg(8));
            $buffer->draw(-2, 2, '\'', AnsiEscape::fg(8));

            $buffer->draw(1, 1, '|', AnsiEscape::fg(8));
            $progressBarWidth = $buffer->getWidth() - 4;
            $progress = $progressBarWidth * $this->progress;
            $buffer->draw(2, 1, str_repeat(' ', (int)$progress), AnsiEscape::bg(2));
            $buffer->draw(-2, 1, '|', AnsiEscape::fg(8));
        });
    }

    public function getBorder(): Border
    {
        return Border::create()
            ->horizontal(<<<'EOF'
            -
             
            -
            EOF)
            ->vertical(<<<'EOF'
            |   |
            EOF)
            ->corners(
                topLeft: <<<'EOF'
                .----
                |    
                |   .
                EOF,
                topRight: <<<'EOF'
                ----. 
                    |
                .   |
                EOF,
                bottomLeft: <<<'EOF'
                |   '
                |    
                '----
                EOF,
                bottomRight: <<<'EOF'
                '   |
                    |
                ----'
                EOF,
            )
            ->connectFacing(
                left: <<<'EOF'
                '   |
                    |
                .   |
                EOF,
                right: <<<'EOF'
                |   '
                |    
                |   .
                EOF,
                top: <<<'EOF'
                .   .
                     
                -----
                EOF,
                bottom: <<<'EOF'
                -----
                     
                .   .
                EOF,
                all: <<<'EOF'
                '   '
                     
                .   .
                EOF,
            )
            ->connections([
                Connection::horizontal()
                    ->begin(0, -9, Facing::RIGHT)
                    ->end(-5, -9, Facing::LEFT),
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
        $this->progressArea->dirty();
    }
}
